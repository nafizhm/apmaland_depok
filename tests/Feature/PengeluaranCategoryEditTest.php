<?php

namespace Tests\Feature;

use App\Http\Controllers\Keuangan\PengeluaranController;
use App\Models\Hutang;
use App\Models\Pengeluaran;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PengeluaranCategoryEditTest extends TestCase
{
    public function test_edit_saves_category_without_receipt_number_and_reconciles_debt(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        foreach (['pengeluaran' => new Pengeluaran, 'hutang' => new Hutang] as $name => $model) {
            Schema::create($name, function (Blueprint $table) use ($model) {
                $table->id();
                foreach ($model->getFillable() as $field) $table->string($field)->nullable();
            });
        }
        Schema::create('kategori_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');
            $table->string('jenis_kategori');
        });
        DB::table('kategori_transaksi')->insert([
            ['id' => 1, 'kategori' => 'Operasional', 'jenis_kategori' => 'PENGELUARAN'],
            ['id' => 2, 'kategori' => 'Pembayaran Hutang', 'jenis_kategori' => 'PENGELUARAN'],
            ['id' => 3, 'kategori' => 'Pendapatan', 'jenis_kategori' => 'PEMASUKAN'],
        ]);
        $debt = Hutang::create(['nominal' => 5000, 'terbayar' => 1000, 'sisa_bayar' => 4000, 'status' => 1]);
        $expense = Pengeluaran::create(['id_kategori_transaksi' => 2, 'id_hutang' => $debt->id, 'nominal' => 1000, 'lampiran' => 'existing.pdf', 'no_tanda_terima' => 'OLD-001']);
        Route::put('/_test/pengeluaran/{id}', [PengeluaranController::class, 'update']);
        $payload = ['tanggal' => '2026-10-16', 'nominal' => '1.000', 'id_bank' => 1, 'id_metode_bayar' => 1, 'diterima_dari' => 'Pemberi', 'nama_penerima' => 'Penerima', 'id_kategori_transaksi' => 1];
        $url = '/_test/pengeluaran/'.$expense->id;
        $this->putJson($url, $payload)->assertOk();
        $this->assertDatabaseHas('pengeluaran', ['id' => $expense->id, 'id_kategori_transaksi' => 1, 'id_hutang' => 0, 'no_tanda_terima' => 'OLD-001']);
        $this->assertDatabaseHas('hutang', ['id' => $debt->id, 'terbayar' => 0, 'sisa_bayar' => 5000]);
        foreach ([3, 999, ''] as $category) {
            $this->putJson($url, array_replace($payload, ['id_kategori_transaksi' => $category]))
                ->assertUnprocessable()->assertJsonValidationErrors('id_kategori_transaksi');
        }
        $payload['id_kategori_transaksi'] = 2;
        $this->putJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('id_hutang');
        $payload['id_hutang'] = $debt->id;
        $this->putJson($url, $payload)->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => $debt->id, 'terbayar' => 1000, 'sisa_bayar' => 4000]);

        $this->putJson($url, $payload)->assertOk();
        $this->assertDatabaseHas('hutang', ['id' => $debt->id, 'terbayar' => 1000, 'sisa_bayar' => 4000]);

        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kategori_transaksi');
        });
        DB::table('piutang')->insert(['id' => 1, 'id_kategori_transaksi' => 3]);
        $linked = Pengeluaran::create([
            'id_piutang' => 1, 'id_kategori_transaksi' => 3,
            'nominal' => 1000, 'lampiran' => 'existing.pdf',
        ]);
        $payload['id_kategori_transaksi'] = 3;
        unset($payload['id_hutang']);
        $linkedUrl = '/_test/pengeluaran/'.$linked->id;
        $this->putJson($linkedUrl, $payload)->assertOk();
        $this->assertDatabaseHas('pengeluaran', ['id' => $linked->id, 'id_kategori_transaksi' => 3]);
        $payload['id_kategori_transaksi'] = 1;
        $this->putJson($linkedUrl, $payload)->assertOk();
        $this->assertDatabaseHas('piutang', ['id' => 1, 'id_kategori_transaksi' => 1]);

    }
}
