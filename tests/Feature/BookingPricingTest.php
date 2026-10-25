<?php

namespace Tests\Feature;

use App\Http\Controllers\PengajuanHoldController;
use App\Models\KavlingPeta;
use App\Models\PengajuanHold;
use App\Services\KavlingPricing;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingPricingTest extends TestCase
{
    private string $testPublicPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->testPublicPath = storage_path('framework/testing/prices-' . uniqid());
        $this->app->usePublicPath($this->testPublicPath);
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->id();
            foreach (array_unique((new KavlingPeta)->getFillable()) as $field) $table->text($field)->nullable();
        });
        Schema::create('pengajuan_hold', function (Blueprint $table) {
            $table->id();
            foreach ((new PengajuanHold)->getFillable() as $field) $table->text($field)->nullable();
        });
        Schema::create('lokasi_kavling', function (Blueprint $table) { $table->id(); $table->string('nama_kavling'); });
        Schema::create('customer', function (Blueprint $table) { $table->id(); $table->integer('id_kavling'); $table->integer('stt_arsip')->nullable(); });
        DB::table('lokasi_kavling')->insert(['id' => 6, 'nama_kavling' => 'paramita residence']);
        $row = json_decode(file_get_contents(database_path('data/paramita-harga-rev-2026-10-22.json')), true)[0];
        KavlingPeta::create(array_merge(array_intersect_key($row, KavlingPricing::FIELDS), [
            'id_lokasi' => 6, 'kode_kavling' => 'B-11', 'plafon_kpr' => 350000000, 'rincian_biaya' => [['nama' => 'Harga Rumah', 'nilai' => 471900000]],
        ]));
        Route::get('/_test/price/{id}', [PengajuanHoldController::class, 'getHargaKavling']);
        Route::post('/_test/book', [PengajuanHoldController::class, 'bookingStore']);
        Route::post('/_test/verify/{id}', [PengajuanHoldController::class, 'simpanVerifikasi']);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testPublicPath)) File::deleteDirectory($this->testPublicPath);
        parent::tearDown();
    }

    public function test_api_uses_exact_pdf_prices_for_cash_and_every_tenor(): void
    {
        $this->getJson('/_test/price/1?jenis_pembelian=Pembelian%20Cash')->assertOk()->assertJsonPath('total_harga', 395000000);
        foreach ([3 => [406850000, 69783333], 6 => [418700000, 36866667], 12 => [430550000, 19420833]] as $tenor => [$total, $monthly]) {
            $this->getJson('/_test/price/1?jenis_pembelian=Cash%20Bertahap&termin_x_cash_b=' . $tenor)
                ->assertOk()->assertJsonPath('total_harga', $total)->assertJsonPath('quote.dp', 197500000)->assertJsonPath('quote.cicilan', $monthly);
        }
        $this->getJson('/_test/price/1?jenis_pembelian=KPR')->assertOk()
            ->assertJsonPath('total_harga', 350000000)
            ->assertJsonPath('quote.total_harga', 350000000)
            ->assertJsonPath('rincian_biaya.0.nilai', 471900000);
        foreach (['', '0', '4', '60'] as $tenor) {
            $this->getJson('/_test/price/1?jenis_pembelian=Cash%20Bertahap&termin_x_cash_b=' . $tenor)
                ->assertUnprocessable()->assertJsonValidationErrors('termin_x_cash_b');
        }
    }

    public function test_kpr_booking_stores_only_the_plafon_as_total(): void
    {
        $payload = array_merge($this->payload(), ['jenis_pembelian' => 'KPR']);
        $this->post('/_test/book', $payload, ['Accept' => 'application/json'])->assertOk();
        $booking = PengajuanHold::firstOrFail();
        $this->assertEquals(350000000, $booking->total_harga);
        $this->assertSame(350000000, $booking->harga_snapshot['total_harga']);
    }

    public function test_kpr_without_plafon_cannot_use_the_house_price_as_total(): void
    {
        KavlingPeta::whereKey(1)->update(['plafon_kpr' => null]);
        $this->getJson('/_test/price/1?jenis_pembelian=KPR')
            ->assertUnprocessable()->assertJsonValidationErrors('id_kavling');
    }

    private function payload(): array
    {
        return ['nama_lengkap' => 'Pembeli', 'nik' => '1234567890123456', 'tempat_lahir' => 'Depok',
            'tgl_lahir' => '1990-01-01', 'jenis_kelamin' => 'Laki-laki', 'no_telp' => '081234567890',
            'alamat_ktp' => 'Depok', 'alamat_domisili' => 'Depok', 'id_lokasi' => 6, 'id_kavling' => 1,
            'total_harga' => 1, 'id_marketing' => 0, 'status_pernikahan' => 'Belum Menikah',
            'booking_fee' => '2.000.000', 'jenis_perumahan' => 'Komersil', 'jenis_pembelian' => 'Cash Bertahap',
            'termin_x_cash_b' => 12, 'foto_ktp' => UploadedFile::fake()->image('ktp.jpg')];
    }

    public function test_booking_recalculates_tampered_total_and_keeps_price_snapshot_during_verification(): void
    {
        $this->post('/_test/book', $this->payload(), ['Accept' => 'application/json'])->assertOk();
        $booking = PengajuanHold::firstOrFail();
        $this->assertEquals(430550000, $booking->total_harga);
        $this->assertSame(19420833, $booking->harga_snapshot['cicilan']);
        $this->assertEquals(12, $booking->termin_x_cash_b);
        KavlingPeta::whereKey(1)->update(['harga_bertahap_12' => 500000000]);
        $payload = $this->payload();
        unset($payload['foto_ktp']);
        $this->postJson('/_test/verify/' . $booking->id, array_merge($payload, ['tgl_booking' => '2026-10-22', 'stt_reg' => 1]))->assertOk();
        $this->assertEquals(430550000, $booking->fresh()->total_harga);
        $this->assertSame([['nama' => 'Harga Rumah', 'nilai' => 430550000]], $booking->fresh()->rincian_biaya);
    }

    public function test_import_matches_all_units_and_leaves_other_location_alone(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        KavlingPeta::query()->delete();
        $rows = json_decode(file_get_contents(database_path('data/paramita-harga-rev-2026-10-22.json')), true);
        foreach ($rows as $row) foreach ($row['units'] as $code) {
            KavlingPeta::create(['id_lokasi' => 6, 'kode_kavling' => $code, 'luas_tanah' => $row['luas_tanah'], 'luas_bangunan' => $row['luas_bangunan']]);
        }
        $other = KavlingPeta::create(['id_lokasi' => 5, 'kode_kavling' => 'B-11', 'harga_cash' => 123]);
        $this->artisan('prices:import-paramita')->assertSuccessful();
        $this->assertNull(KavlingPeta::where('id_lokasi', 6)->first()->harga_cash);
        $this->artisan('prices:import-paramita', ['--apply' => true])->assertSuccessful();
        $this->assertSame(96, KavlingPeta::where('id_lokasi', 6)->whereNotNull('harga_cash')->count());
        $this->assertSame(123, $other->fresh()->harga_cash);
        $this->assertSame(835742000, KavlingPeta::where('id_lokasi', 6)->where('kode_kavling', 'A-06')->first()->harga_bertahap_3);
        foreach (KavlingPeta::where('id_lokasi', 6)->get() as $unit) {
            $this->assertSame((int) round($unit->harga_cash * .3), $unit->dp_cash_bertahap_30);
            foreach ([3, 6, 12] as $tenor) {
                $quote = KavlingPricing::quote($unit, 'Cash Bertahap', $tenor, 30);
                $this->assertSame($unit->{'harga_bertahap_' . $tenor}, $quote['total_harga']);
                $this->assertSame((int) round(($quote['total_harga'] - $quote['dp']) / $tenor), $quote['cicilan']);
                $last = $quote['total_harga'] - $quote['dp'] - $quote['cicilan'] * ($tenor - 1);
                $this->assertSame($quote['total_harga'], $quote['dp'] + $quote['cicilan'] * ($tenor - 1) + $last);
            }
        }
    }

    public function test_cash_booking_uses_cash_price_and_clears_installment_fields(): void
    {
        $payload = array_merge($this->payload(), ['jenis_pembelian' => 'Pembelian Cash']);
        $this->post('/_test/book', $payload, ['Accept' => 'application/json'])->assertOk();
        $booking = PengajuanHold::firstOrFail();
        $this->assertEquals(395000000, $booking->total_harga);
        $this->assertEquals(0, $booking->termin_x_cash_b);
        $this->assertSame(0, $booking->harga_snapshot['dp']);
        $this->assertNull($booking->harga_snapshot['tenor']);
    }

    public function test_incomplete_installment_price_cannot_fall_back_to_another_price(): void
    {
        KavlingPeta::whereKey(1)->update(['harga_bertahap_12' => null]);
        $this->post('/_test/book', $this->payload(), ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('id_kavling');
        $this->assertDatabaseCount('pengajuan_hold', 0);
    }

    public function test_dp30_uses_its_own_price_and_is_kept_during_verification(): void
    {
        KavlingPeta::whereKey(1)->update([
            'dp_cash_bertahap_30' => 118500000,
            'harga_bertahap_12_dp30' => 430550000,
            'cicilan_12_dp30' => 26004167,
        ]);
        $this->getJson('/_test/price/1?jenis_pembelian=Cash%20Bertahap&termin_x_cash_b=12&dp_persen=30')
            ->assertOk()->assertJsonPath('quote.dp_persen', 30)
            ->assertJsonPath('quote.dp', 118500000)->assertJsonPath('quote.cicilan', 26004167);
        $payload = array_merge($this->payload(), ['dp_persen' => 30]);
        $this->post('/_test/book', $payload, ['Accept' => 'application/json'])->assertOk();
        $booking = PengajuanHold::firstOrFail();
        $this->assertSame(30, $booking->harga_snapshot['dp_persen']);
        unset($payload['foto_ktp']);
        $this->postJson('/_test/verify/' . $booking->id, array_merge($payload, ['tgl_booking' => '2026-10-22', 'stt_reg' => 1]))->assertOk();
        $this->assertEquals(118500000, $booking->fresh()->dp_cash_b);
        $this->assertSame(30, $booking->fresh()->harga_snapshot['dp_persen']);
        $payload['dp_persen'] = 50;
        $this->postJson('/_test/verify/' . $booking->id, array_merge($payload, ['tgl_booking' => '2026-10-22', 'stt_reg' => 1]))->assertOk();
        $this->assertSame(50, $booking->fresh()->harga_snapshot['dp_persen']);
        $this->assertEquals(197500000, $booking->fresh()->dp_cash_b);
    }

    public function test_dp30_with_missing_prices_and_invalid_dp_percentage_are_rejected(): void
    {
        $this->getJson('/_test/price/1?jenis_pembelian=Cash%20Bertahap&termin_x_cash_b=3&dp_persen=30')
            ->assertUnprocessable()->assertJsonValidationErrors('id_kavling');
        $this->post('/_test/book', array_merge($this->payload(), ['dp_persen' => 40]), ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('dp_persen');
        $this->assertDatabaseCount('pengajuan_hold', 0);
    }
}
