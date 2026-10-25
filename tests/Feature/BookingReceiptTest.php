<?php

namespace Tests\Feature;

use App\Http\Controllers\PengajuanHoldController;
use App\Models\PengajuanHold;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingReceiptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('pengajuan_hold', function (Blueprint $table) {
            $table->id();
            foreach ((new PengajuanHold)->getFillable() as $field) $table->string($field)->nullable();
        });
        foreach (['marketing_offline', 'lokasi_kavling', 'kavling_peta', 'metode_bayar', 'konfigurasi_media', 'menu'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                foreach (match ($name) {
                    'marketing_offline' => ['nama_marketing'], 'lokasi_kavling' => ['nama_kavling'],
                    'kavling_peta' => ['kode_kavling', 'tipe_bangunan'], 'metode_bayar' => ['jenis_bayar'],
                    'konfigurasi_media' => ['jenis_data', 'stt_aktif', 'nama_file'], 'menu' => ['route_name'],
                } as $field) $table->string($field)->nullable();
            });
        }
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Pramita Residence']);
        DB::table('kavling_peta')->insert(['id' => 1, 'kode_kavling' => 'A-07', 'tipe_bangunan' => 'Osaka']);
        DB::table('metode_bayar')->insert(['id' => 1, 'jenis_bayar' => 'Transfer']);
        PengajuanHold::create(['id' => 1, 'stt_reg' => 2, 'tgl_booking' => '2026-10-20', 'nama_lengkap' => 'Pembeli & Keluarga', 'no_registrasi' => 'REG-001', 'booking_fee' => 2000000, 'id_lokasi' => 1, 'id_kavling' => 1, 'id_metode_bayar' => 1]);
        PengajuanHold::create(['id' => 2, 'stt_reg' => 1, 'tgl_booking' => '2026-10-19']);
        PengajuanHold::create(['id' => 3, 'stt_reg' => 3, 'tgl_booking' => '2026-10-22']);
        Route::get('/_test/bookings', [PengajuanHoldController::class, 'index'])->name('_test.bookings');
        Route::get('/_test/bookings-archive', [PengajuanHoldController::class, 'viewArsip']);
        Route::get('/_test/bookings/{id}/receipt', [PengajuanHoldController::class, 'cetakTandaTerima']);
    }

    public function test_queue_only_contains_pending_bookings(): void
    {
        $this->assertSame([2], PengajuanHold::verificationQueue()->pluck('id')->all());
        $response = $this->getJson('/_test/bookings', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame([2], array_column($response->json('data'), 'id'));
        $this->assertStringNotContainsString('Cetak Tanda Terima', $response->json('data.0.action'));
        $this->assertSame('booking-pending', $response->json('data.0.DT_RowClass'));
    }

    public function test_archive_contains_completed_verifications_and_approved_receipt_action(): void
    {
        $response = $this->getJson('/_test/bookings-archive', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame([3, 1], array_column($response->json('data'), 'id'));
        $this->assertStringNotContainsString('Cetak Tanda Terima', $response->json('data.0.action'));
        $this->assertStringContainsString('Cetak Tanda Terima', $response->json('data.1.action'));
    }

    public function test_pending_and_rejected_cannot_print_receipts(): void
    {
        foreach ([2, 3] as $id) $this->get('/_test/bookings/' . $id . '/receipt')->assertStatus(422);
    }

    public function test_approved_receipt_is_one_landscape_pdf_page(): void
    {
        $response = $this->get('/_test/bookings/1/receipt')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $parser = new \setasign\Fpdi\Tcpdf\Fpdi();
        $this->assertSame(1, $parser->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($response->getContent())));
        $size = $parser->getTemplateSize($parser->importPage(1));
        $this->assertGreaterThan($size['height'], $size['width']);
    }
}
