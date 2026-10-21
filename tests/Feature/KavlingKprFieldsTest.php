<?php

namespace Tests\Feature;

use App\Http\Controllers\Master\KavlingController;
use App\Models\KavlingPeta;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KavlingKprFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->id();
            foreach (array_unique(array_diff((new KavlingPeta)->getFillable(), ['nama_cluster', 'diskon_dp', 'plafon_kpr'])) as $field) $table->text($field)->nullable();
        });
        (require database_path('migrations/2026_10_22_140000_add_kavling_kpr_fields.php'))->up();
        Schema::create('komponen_biaya', function (Blueprint $table) {
            $table->id(); $table->string('nama'); $table->string('kode_unik'); $table->integer('urutan');
        });
        Schema::create('customer', function (Blueprint $table) { $table->id(); $table->integer('id_kavling'); });
        Schema::create('lokasi_kavling', fn (Blueprint $table) => $table->id());
        DB::table('komponen_biaya')->insert(['nama' => 'Harga Rumah', 'kode_unik' => 'harga_jual', 'urutan' => 1]);
        KavlingPeta::create(['id' => 1, 'id_lokasi' => 1, 'kode_kavling' => 'A-07']);
        Route::put('/_test/kavling/{id}', [KavlingController::class, 'update']);
        Route::get('/_test/kavling/{id}', [KavlingController::class, 'edit']);
    }

    public function test_cluster_discount_and_ceiling_are_saved_separately_from_house_costs(): void
    {
        $this->putJson('/_test/kavling/1', [
            'nama_cluster' => 'Osaka', 'diskon_dp' => 62900000, 'plafon_kpr' => 629000000,
            'panjang' => 12, 'lebar' => 6, 'luas_tanah' => 72, 'luas_bangunan' => 40,
            'rincian_biaya' => [['nama' => 'Harga Rumah', 'nilai' => 691900000]],
        ])->assertOk()->assertJsonPath('status', 'success');
        $unit = KavlingPeta::findOrFail(1);
        $this->assertSame('Osaka', $unit->nama_cluster);
        $this->assertSame(62900000, $unit->diskon_dp);
        $this->assertSame(629000000, $unit->plafon_kpr);
        $this->assertSame(691900000, $unit->total_harga);
        $this->getJson('/_test/kavling/1')->assertOk()->assertJsonPath('data.plafon_kpr', 629000000);
    }

    public function test_negative_discount_or_ceiling_is_rejected(): void
    {
        $this->putJson('/_test/kavling/1', [
            'panjang' => 12, 'lebar' => 6, 'luas_tanah' => 72, 'luas_bangunan' => 40,
            'diskon_dp' => -1, 'plafon_kpr' => -1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['diskon_dp', 'plafon_kpr']);
    }
}
