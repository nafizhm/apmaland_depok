<?php

namespace Tests\Feature;

use App\Http\Controllers\GenerateNumberController;
use App\Http\Controllers\Transaksi\PindahUnitController;
use App\Models\Pemasukan;
use App\Models\PindahUnit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PindahUnitTest extends TestCase
{
    public function test_empty_utj_is_saved_as_zero_and_units_are_transferred(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        foreach ([new PindahUnit, new Pemasukan] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->id();
                foreach ($model->getFillable() as $field) {
                    if ($field === 'id') continue;
                    if ($field === 'nominal_utj') {
                        $table->integer($field);
                    } else {
                        $table->string($field)->nullable();
                    }
                }
            });
        }
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->integer('id_lokasi');
            $table->integer('id_kavling');
            $table->integer('stt_arsip')->default(0);
        });
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kavling');
        });
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->id();
            $table->integer('id_lokasi');
            $table->integer('id_customer')->nullable();
            $table->integer('tipe_bangunan')->default(36);
            $table->string('kode_kavling');
            $table->timestamps();
        });
        Schema::create('pengajuan_hold', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kavling');
            $table->integer('stt_reg');
        });
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->integer('id_customer');
            $table->integer('id_kategori_transaksi');
        });
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Test']);
        DB::table('customer')->insert(['id' => 1, 'id_lokasi' => 1, 'id_kavling' => 1]);
        DB::table('kavling_peta')->insert([
            ['id' => 1, 'id_lokasi' => 1, 'id_customer' => 1, 'kode_kavling' => 'A1'],
            ['id' => 2, 'id_lokasi' => 1, 'id_customer' => null, 'kode_kavling' => 'A2'],
        ]);
        $this->mock(GenerateNumberController::class, function ($mock) {
            $mock->shouldReceive('generateNomorDokumen')->once()->andReturn('TEST-001');
        });
        Route::post('/_test/pindah-unit', [PindahUnitController::class, 'store']);

        $this->postJson('/_test/pindah-unit', [
            'tgl_pindah' => '2026-10-20', 'id_customer' => 1,
            'id_kavling_baru' => 2, 'nominal_utj' => '',
            'biaya_admin' => '1.500.000', 'keterangan_pindah' => 'Test',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(0, DB::table('pindah_unit')->value('nominal_utj'));
        $this->assertDatabaseHas('customer', ['id' => 1, 'id_kavling' => 2]);
        $this->assertDatabaseHas('kavling_peta', ['id' => 1, 'id_customer' => null]);
        $this->assertDatabaseHas('kavling_peta', ['id' => 2, 'id_customer' => 1]);
        $this->assertDatabaseHas('pemasukan', ['nominal' => 1500000]);
    }
}
