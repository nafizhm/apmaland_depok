<?php

namespace Tests\Feature;

use App\Http\Controllers\Transaksi\SPPRController;
use App\Models\SPPR;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpprTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', config('database.default'));
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kavling')->nullable();
            $table->integer('id_lokasi');
        });
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_cluster')->default(false);
        });
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kavling');
            $table->integer('luas_bangunan');
            $table->integer('luas_tanah');
        });
        (require database_path('migrations/2026_06_06_083533_create_sppr_table.php'))->up();
        (require database_path('migrations/2026_07_14_100000_add_fields_to_sppr_table.php'))->up();
        Schema::table('sppr', function (Blueprint $table) {
            $table->string('no_sppr')->nullable();
            $table->string('penandatangan')->nullable();
            $table->integer('id_marketing')->nullable();
            $table->text('keterangan')->nullable();
        });
        (require database_path('migrations/2026_10_03_100000_remove_unused_columns_from_sppr_table.php'))->up();
        Schema::create('menu', function (Blueprint $table) {
            $table->id(); $table->string('route_name'); $table->string('title');
        });
        DB::table('menu')->insert(['route_name' => 'sppr.index', 'title' => 'SPPR']);
        Schema::create('konfigurasi_media', function (Blueprint $table) {
            $table->id(); $table->string('jenis_data'); $table->boolean('stt_aktif'); $table->string('nama_file');
        });
        (require database_path('migrations/2026_10_22_120000_add_spr_template_data.php'))->up();
        DB::table('lokasi_kavling')->insert(['id' => 1]);
        DB::table('kavling_peta')->insert(['id' => 1, 'kode_kavling' => 'B-7', 'luas_bangunan' => 80, 'luas_tanah' => 84]);
        DB::table('customer')->insert(['id' => 1, 'id_kavling' => 1, 'id_lokasi' => 1]);
        Route::post('/_test/sppr', [SPPRController::class, 'store']);
        Route::put('/_test/sppr/{id}', [SPPRController::class, 'update']);
        Route::get('/_test/sppr/{id}/edit', [SPPRController::class, 'edit']);
    }

    private function payload(): array
    {
        return [
            'id_customer' => 1, 'no_sppr' => '007', 'nama' => 'Pembeli & Keluarga',
            'alamat' => 'Jalan Sukamanah RT 06 RW 12 Kelurahan Sukamanah Kecamatan Cipedes Tasikmalaya',
            'nik' => '3278021103060003', 'no_telp' => '081234567890',
            'harga_jual' => 655000000, 'nominal_dp' => 65500000,
            'asumsi_plafon_kpr' => 589500000,
            'luas_bangunan' => 999, 'luas_tanah' => 999,
            'biaya_surat_surat' => 999999,
        ];
    }

    public function test_add_and_edit_only_need_template_fields_and_use_current_kavling(): void
    {
        $this->postJson('/_test/sppr', $this->payload())->assertOk();
        $this->assertFalse(Schema::hasColumn('sppr', 'biaya_surat_surat'));
        $this->assertFalse(Schema::hasColumn('sppr', 'total_yang_harus_dibayar'));
        $this->assertDatabaseHas('sppr', ['id' => 1, 'luas_bangunan' => 80, 'luas_tanah' => 84]);
        DB::table('kavling_peta')->where('id', 1)->update(['luas_tanah' => 90]);
        $this->getJson('/_test/sppr/1/edit')->assertOk()->assertJsonPath('data.luas_tanah', 90);
        $this->putJson('/_test/sppr/1', $this->payload())->assertOk();
        $this->assertDatabaseHas('sppr', ['id' => 1, 'luas_tanah' => 90]);
    }

    public function test_customer_without_kavling_cannot_save(): void
    {
        DB::table('customer')->where('id', 1)->update(['id_kavling' => null]);
        $this->postJson('/_test/sppr', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('id_customer');
    }

    public function test_spr_fields_save_update_and_totals_follow_the_template(): void
    {
        $payload = $this->payload();
        $payload['harga_jual'] = 679000000;
        $payload['nominal_dp'] = 61800000;
        $payload['spr_data'] = [
            'jenis_pembelian' => 'KPR', 'diskon' => 40000000,
            'nama_perumahan' => 'Pramita Residence', 'alamat_surat' => 'Alamat Surat',
            'email' => 'test@example.com', 'kelebihan_tanah' => 4.5,
            'harga_kelebihan_tanah' => 10000000, 'biaya_sertifikat' => 2000000, 'biaya_bphtb' => 3000000,
            'tanggal_booking' => '2026-06-10', 'nominal_booking' => 2000000,
            'tanggal_angsuran_pertama' => '2026-06-17', 'nominal_angsuran_pertama' => 10000000,
            'angsuran' => [['tanggal' => '2026-08-15', 'nominal' => 20000000]],
            'promo_termasuk_1' => 'FREE Surat & PPN',
        ];
        unset($payload['nik']);
        $this->postJson('/_test/sppr', $payload)->assertOk();
        $spr = SPPR::findOrFail(1);
        $this->assertSame('Alamat Surat', $spr->spr_data['alamat_surat']);
        $this->assertSame(4.5, $spr->spr_data['kelebihan_tanah']);
        $totals = \App\Services\SprDocument::totals($spr);
        $this->assertSame(639000000, $totals['harga_setelah_diskon']);
        $this->assertSame(649000000, $totals['total_harga']);
        $this->assertSame(76800000, $totals['kewajiban_kpr']);
        $this->assertSame(654000000, $totals['kewajiban_tunai']);
        $this->assertSame(32000000, $totals['jumlah_jadwal']);
        $this->getJson('/_test/sppr/1/edit')->assertOk()->assertJsonPath('data.spr_data.angsuran.0.nominal', 20000000);
        $payload['spr_data']['jenis_pembelian'] = 'TUNAI';
        $this->putJson('/_test/sppr/1', $payload)->assertOk();
        $this->assertSame('TUNAI', $spr->fresh()->spr_data['jenis_pembelian']);
        $this->assertDatabaseHas('menu', ['route_name' => 'sppr.index', 'title' => 'SPR']);
    }

    public function test_spr_rejects_excessive_discount_incomplete_schedule_and_missing_payment_date(): void
    {
        $payload = $this->payload();
        $payload['spr_data'] = ['diskon' => 999999999];
        $this->postJson('/_test/sppr', $payload)->assertUnprocessable()->assertJsonValidationErrors('spr_data.diskon');
        $payload['spr_data'] = ['angsuran' => [['tanggal' => '2026-10-22']]];
        $this->postJson('/_test/sppr', $payload)->assertUnprocessable()->assertJsonValidationErrors('spr_data.angsuran.0.nominal');
        $payload['spr_data'] = ['nominal_booking' => 2000000];
        $this->postJson('/_test/sppr', $payload)->assertUnprocessable()->assertJsonValidationErrors('spr_data.tanggal_booking');
    }

    public function test_print_produces_a_single_page_pdf_with_all_template_sections(): void
    {
        $this->postJson('/_test/sppr', $this->payload())->assertOk();
        $spr = SPPR::findOrFail(1);
        $spr->update(['spr_data' => [
            'nama_perumahan' => 'Pramita Residence', 'promo_termasuk_1' => 'Surat & PPN',
            'angsuran' => array_fill(0, 20, ['tanggal' => '2026-10-22', 'nominal' => 20000000]),
        ]]);
        $unit = ['blok' => 'B', 'no' => '7', 'luas_tanah' => 84, 'luas_bangunan' => 80];
        $html = view('admin.transaksi.sppr.print', [
            'spr' => $spr->load('customer.lokasi', 'customer.kavling'), 'unit' => $unit,
            'data' => $spr->spr_data, 'totals' => \App\Services\SprDocument::totals($spr), 'logoPath' => null,
        ])->render();
        foreach (['A. IDENTITAS KONSUMEN', 'B. IDENTITAS RUMAH', 'C. PERHITUNGAN KEWAJIBAN KONSUMEN', 'D. PEMBELIAN SECARA KPR', 'E. PEMBELIAN SECARA TUNAI', 'F. JADWAL PEMBAYARAN', 'G. PAKET HARGA PROMOSI'] as $section) {
            $this->assertStringContainsString($section, $html);
        }
        $this->assertStringContainsString('Pembeli &amp; Keluarga', $html);
        $this->assertStringNotContainsString('RIKI KRIESNA', $html);
        $response = app(SPPRController::class)->cetak(1);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('SPR_', $response->headers->get('Content-Disposition'));
        $parser = new \setasign\Fpdi\Tcpdf\Fpdi();
        $this->assertSame(1, $parser->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($response->getContent())));
    }
}
