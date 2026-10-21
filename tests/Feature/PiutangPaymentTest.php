<?php

namespace Tests\Feature;

use App\Http\Controllers\Keuangan\PiutangController;
use App\Models\Pemasukan;
use App\Models\Piutang;
use App\Services\PiutangPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PiutangPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->integer('id_lokasi')->nullable();
            $table->integer('id_kavling')->nullable();
        });
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kavling');
        });
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kavling');
        });
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->integer('id_customer')->default(0);
            $table->integer('nominal');
            $table->integer('terbayar')->default(0);
            $table->integer('sisa_bayar')->default(0);
            $table->integer('status')->default(1);
            $table->date('tgl_pelunasan')->nullable();
        });
        Schema::create('pemasukan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_customer')->default(0);
            $table->integer('id_piutang')->default(0);
            $table->integer('id_kategori_transaksi')->default(4);
            $table->integer('nominal');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('no_kwitansi')->nullable();
        });
        DB::table('customer')->insert(['id' => 1, 'nama_lengkap' => 'Customer Test']);
    }

    private function bill(int $amount, int $customer = 1): Piutang
    {
        return Piutang::create(['id_customer' => $customer, 'nominal' => $amount]);
    }

    private function pay(int $amount, array $values = []): Pemasukan
    {
        return Pemasukan::create(array_merge([
            'id_customer' => 1, 'nominal' => $amount,
            'tanggal' => '2026-10-07', 'keterangan' => 'Pembayaran',
        ], $values));
    }

    public function test_booking_and_kpr_sync_and_deletion_reopens_receivables(): void
    {
        $first = $this->bill(100);
        $second = $this->bill(200);
        $booking = $this->pay(20);
        $kpr = $this->pay(250, ['id_kategori_transaksi' => 9]);
        $this->assertDatabaseHas('piutang', ['id' => $first->id, 'terbayar' => 100, 'sisa_bayar' => 0, 'status' => 2, 'tgl_pelunasan' => '2026-10-07']);
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 170, 'sisa_bayar' => 30, 'status' => 1]);
        $kpr->update(['nominal' => 100]);
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 20, 'sisa_bayar' => 180]);
        $booking->delete();
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 0, 'sisa_bayar' => 200]);
        $kpr->delete();
        $this->assertDatabaseHas('piutang', ['id' => $first->id, 'terbayar' => 0, 'sisa_bayar' => 100, 'status' => 1, 'tgl_pelunasan' => null]);
    }

    public function test_detail_combines_customer_bills_and_counts_each_payment_once(): void
    {
        $first = $this->bill(100);
        $second = $this->bill(200);
        $general = $this->pay(150);
        $linked = $this->pay(40, ['id_piutang' => $second->id]);
        $this->pay(999, ['id_customer' => 2]);
        $this->pay(999, ['keterangan' => 'Biaya ganti nama customer']);
        $response = app(PiutangController::class)->show($second->id)->getData(true);
        $this->assertSame('Customer Test', $response['customer']);
        $this->assertEquals(300, $response['data']['nominal']);
        $this->assertEquals(190, $response['data']['terbayar']);
        $this->assertEquals(110, $response['data']['sisa_bayar']);
        $this->assertCount(2, $response['components']);
        $components = array_column($response['components'], null, 'id');
        $this->assertSame(100, $components[$first->id]['nominal']);
        $this->assertSame(100, $components[$first->id]['terbayar']);
        $this->assertSame(200, $components[$second->id]['nominal']);
        $this->assertSame(90, $components[$second->id]['terbayar']);
        $this->assertSame(110, $components[$second->id]['sisa_bayar']);
        $this->assertSame((int) $response['data']['nominal'], array_sum(array_column($response['components'], 'nominal')));
        $this->assertCount(2, $response['payments']);
        $amounts = array_column($response['payments'], 'nominal', 'id');
        $this->assertSame(150, $amounts[$general->id]);
        $this->assertSame(40, $amounts[$linked->id]);
        $response = app(PiutangController::class)->show($first->id)->getData(true);
        $this->assertCount(2, $response['payments']);
        $this->assertSame(150, $response['payments'][0]['nominal']);
    }

    public function test_unit_rows_combine_components_but_keep_other_units_and_manual_bills_separate(): void
    {
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Lokasi A']);
        DB::table('kavling_peta')->insert(['id' => 1, 'kode_kavling' => 'A-01']);
        DB::table('customer')->where('id', 1)->update(['id_lokasi' => 1, 'id_kavling' => 1]);
        DB::table('customer')->insert([
            ['id' => 2, 'nama_lengkap' => 'Same unit', 'id_lokasi' => 1, 'id_kavling' => 1],
            ['id' => 3, 'nama_lengkap' => 'Other location', 'id_lokasi' => 2, 'id_kavling' => 1],
        ]);
        $this->bill(100);
        $this->bill(200);
        $this->bill(50, 2);
        $this->bill(70, 3);
        $this->bill(20, 0);
        $this->bill(30, 0);
        $this->pay(150);
        $rows = app(\App\Services\PiutangUnitService::class)->rows();
        $this->assertCount(4, $rows);
        $unit = $rows->firstWhere('id_customer', 1);
        $this->assertEquals(350, $unit->nominal);
        $this->assertEquals(150, $unit->terbayar);
        $this->assertEquals(200, $unit->sisa_bayar);
        $this->assertEquals(350, $unit->getRawOriginal('nominal'));
        $this->assertStringContainsString('Lokasi A / A-01', $unit->deskripsi);
        $this->assertArrayNotHasKey('customer', $unit->toArray());
    }

    public function test_linked_payment_move_and_manual_receivable_edit_recalculate(): void
    {
        $first = $this->bill(100, 0);
        $second = $this->bill(200, 0);
        $payment = $this->pay(100, ['id_customer' => 0, 'id_piutang' => $first->id]);
        $this->assertDatabaseHas('piutang', ['id' => $first->id, 'status' => 2, 'sisa_bayar' => 0]);
        $payment->update(['id_piutang' => $second->id, 'nominal' => 150]);
        $this->assertDatabaseHas('piutang', ['id' => $first->id, 'terbayar' => 0, 'status' => 1, 'tgl_pelunasan' => null]);
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 150, 'sisa_bayar' => 50]);
        $second->update(['nominal' => 250]);
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 150, 'sisa_bayar' => 100]);
        $payment->delete();
        $this->assertDatabaseHas('piutang', ['id' => $second->id, 'terbayar' => 0, 'sisa_bayar' => 250]);
    }

    public function test_existing_stale_balances_and_payments_before_bills_are_reconciled(): void
    {
        $this->pay(150);
        $bill = $this->bill(100);
        $this->assertDatabaseHas('piutang', ['id' => $bill->id, 'terbayar' => 100, 'sisa_bayar' => 0]);
        DB::table('piutang')->where('id', $bill->id)->update(['terbayar' => 999, 'sisa_bayar' => -899]);
        app(PiutangPaymentService::class)->syncAll();
        $this->assertDatabaseHas('piutang', ['id' => $bill->id, 'terbayar' => 100, 'sisa_bayar' => 0]);
        $bill->update(['nominal' => 200]);
        $this->assertDatabaseHas('piutang', ['id' => $bill->id, 'terbayar' => 150, 'sisa_bayar' => 50, 'status' => 1, 'tgl_pelunasan' => null]);
    }
}
