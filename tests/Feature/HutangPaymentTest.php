<?php

namespace Tests\Feature;

use App\Http\Controllers\Keuangan\HutangController;
use App\Models\Hutang;
use App\Models\Pengeluaran;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HutangPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('hutang', function (Blueprint $table) {
            $table->id();
            foreach ((new Hutang)->getFillable() as $field) {
                if (in_array($field, ['nominal', 'terbayar', 'sisa_bayar'])) $table->bigInteger($field)->default(0);
                else $table->string($field)->nullable();
            }
        });
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            foreach ((new Pengeluaran)->getFillable() as $field) $table->string($field)->nullable();
        });
        foreach (['bank', 'metode_bayar'] as $name) {
            Schema::create($name, fn (Blueprint $table) => $table->id());
            DB::table($name)->insert(['id' => 1]);
        }
        Schema::create('kategori_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('kategori');
            $table->string('jenis_kategori');
            $table->integer('stt_fix')->nullable();
        });
        DB::table('kategori_transaksi')->insert(['id' => 3, 'kode' => 'LAIN', 'kategori' => 'Lainnya', 'jenis_kategori' => 'PENGELUARAN']);
        Hutang::create(['nominal' => 10000000000, 'sisa_bayar' => 10000000000, 'status' => 1]);
        Route::post('/_test/hutang/{id}/bayar', [HutangController::class, 'bayar']);
        Route::put('/_test/hutang/{id}', [HutangController::class, 'update']);
    }

    protected function tearDown(): void
    {
        foreach (DB::table('pengeluaran')->pluck('lampiran') as $file) {
            $path = public_path('assets/keuangan/pengeluaran/'.$file);
            if ($file && is_file($path)) unlink($path);
        }
        parent::tearDown();
    }

    private function payment(string $amount, string $receipt): array
    {
        return [
            'tanggal' => '2026-10-13', 'nominal' => $amount,
            'id_bank' => 1, 'id_metode_bayar' => 1,
            'no_tanda_terima' => $receipt, 'diterima_dari' => 'Pemberi', 'nama_penerima' => 'Penerima',
            'lampiran' => UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf'),
        ];
    }

    public function test_partial_payment_then_settlement_records_expenses_and_updates_debt(): void
    {
        $this->postJson('/_test/hutang/1/bayar', $this->payment('3.000.000.000', 'PAY-1'))->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 3000000000, 'sisa_bayar' => 7000000000, 'status' => 1]);
        $kategori = \App\Models\KategoriTransaksi::where('kategori', 'Pembayaran Hutang')->firstOrFail();
        $this->assertSame('PENGELUARAN', $kategori->jenis_kategori);
        $this->assertDatabaseHas('pengeluaran', ['id_hutang' => 1, 'id_kategori_transaksi' => $kategori->id, 'nominal' => 3000000000]);
        $this->postJson('/_test/hutang/1/bayar', $this->payment('7.000.000.000', 'PAY-2'))->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 10000000000, 'sisa_bayar' => 0, 'status' => 2, 'tgl_pelunasan' => '2026-10-13']);
        $this->assertDatabaseCount('pengeluaran', 2);
        $this->assertSame(2, Pengeluaran::where('id_kategori_transaksi', $kategori->id)->count());
        $this->postJson('/_test/hutang/1/bayar', $this->payment('1', 'PAY-3'))->assertUnprocessable();
        $this->assertDatabaseCount('pengeluaran', 2);
    }

    public function test_invalid_payment_does_not_change_balance(): void
    {
        foreach (['0', '-1', '10.000.000.001', 'abc'] as $amount) {
            $this->postJson('/_test/hutang/1/bayar', $this->payment($amount, 'INVALID'))->assertUnprocessable()->assertJsonValidationErrors('nominal');
        }
        $this->assertDatabaseCount('pengeluaran', 0);
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 0, 'sisa_bayar' => 10000000000]);
    }

    public function test_migration_repairs_missing_categories_without_changing_valid_expenses(): void
    {
        Pengeluaran::create(['id_hutang' => 1, 'id_kategori_transaksi' => 99]);
        Pengeluaran::create(['id_hutang' => 1, 'id_kategori_transaksi' => null]);
        Pengeluaran::create(['id_hutang' => 1, 'id_kategori_transaksi' => 3]);
        Pengeluaran::create(['id_hutang' => 0, 'id_kategori_transaksi' => 99]);
        $migration = require database_path('migrations/2026_10_13_010000_fix_hutang_payment_category.php');
        $migration->up();
        $migration->up();
        $kategori = \App\Models\KategoriTransaksi::where('kategori', 'Pembayaran Hutang')->firstOrFail();
        $this->assertSame(2, Pengeluaran::where('id_kategori_transaksi', $kategori->id)->count());
        $this->assertDatabaseHas('pengeluaran', ['id' => 3, 'id_kategori_transaksi' => 3]);
        $this->assertDatabaseHas('pengeluaran', ['id' => 4, 'id_kategori_transaksi' => 99]);
        $this->assertSame(1, \App\Models\KategoriTransaksi::where('kategori', 'Pembayaran Hutang')->count());
    }

    public function test_editing_debt_preserves_payments_and_recalculates_remaining_balance(): void
    {
        Schema::create('pemasukan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_hutang');
            $table->string('tanggal')->nullable();
            $table->bigInteger('nominal');
            $table->string('lampiran')->nullable();
        });
        DB::table('pemasukan')->insert(['id_hutang' => 1, 'nominal' => 10000000000]);
        Hutang::findOrFail(1)->update(['lampiran' => 'existing.pdf']);
        $this->postJson('/_test/hutang/1/bayar', $this->payment('3.000.000.000', 'EDIT-PAY'))->assertOk();
        $fields = ['tanggal_hutang' => '2026-10-13', 'deskripsi' => 'Hutang diperbarui', 'id_bank' => 1, 'nominal' => '10.000.000.000'];
        $this->putJson('/_test/hutang/1', $fields)->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 3000000000, 'sisa_bayar' => 7000000000, 'status' => 1]);
        $fields['nominal'] = '2.000.000.000';
        $this->putJson('/_test/hutang/1', $fields)->assertUnprocessable()->assertJsonValidationErrors('nominal');
        $fields['nominal'] = '3.000.000.000';
        $this->putJson('/_test/hutang/1', $fields)->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 3000000000, 'sisa_bayar' => 0, 'status' => 2]);
        $this->putJson('/_test/hutang/1', $fields)->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => 1, 'terbayar' => 3000000000, 'sisa_bayar' => 0, 'status' => 2]);
    }

    public function test_debt_list_displays_paid_and_remaining_amounts_after_payment(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
        });
        Route::get('/_test/hutang', [HutangController::class, 'index'])->name('test.hutang.index');
        $this->postJson('/_test/hutang/1/bayar', $this->payment('3.000.000.000', 'LIST-PAY'))->assertOk();
        $response = $this->getJson('/_test/hutang', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $response->assertJsonPath('data.0.terbayar', 'Rp. 3.000.000.000');
        $response->assertJsonPath('data.0.sisa_bayar', 'Rp. 7.000.000.000');
    }
}
