<?php

namespace Tests\Feature;

use App\Services\SiteplanSelection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteplanSelectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->id(); $table->string('nama_kavling'); $table->integer('urutan');
        });
        foreach (['master_svg', 'kavling_peta'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->integer('id_lokasi');
            });
            DB::table($name)->insert([['id_lokasi'=>1], ['id_lokasi'=>2]]);
        }
        DB::table('lokasi_kavling')->insert([
            ['id'=>1,'nama_kavling'=>'Atase townhouse','urutan'=>1],
            ['id'=>2,'nama_kavling'=>'paramita residence','urutan'=>2],
        ]);
    }

    public function test_initial_page_only_loads_the_siteplan_with_the_smallest_order(): void
    {
        DB::table('lokasi_kavling')->where('id', 2)->update(['urutan'=>-1]);
        $this->app->instance('request', Request::create('/siteplan'));
        $data = SiteplanSelection::load(['masterSvg', 'kavlingPeta']);
        $this->assertCount(2, $data['lokasiOptions']);
        $this->assertSame(2, $data['selectedLocation']->id);
        $this->assertCount(1, $data['lokasiKavling']);
        $this->assertSame(2, $data['selectedLocation']->masterSvg->id_lokasi);
        $this->assertSame([2], $data['selectedLocation']->kavlingPeta->pluck('id_lokasi')->all());
    }

    public function test_selection_only_loads_the_requested_locations_svg_and_units(): void
    {
        $this->app->instance('request', Request::create('/siteplan', 'GET', ['id_lokasi'=>2]));
        $data = SiteplanSelection::load(['masterSvg', 'kavlingPeta']);
        $this->assertCount(1, $data['lokasiKavling']);
        $this->assertSame(2, $data['selectedLocation']->id);
        $this->assertSame(2, $data['selectedLocation']->masterSvg->id_lokasi);
        $this->assertSame([2], $data['selectedLocation']->kavlingPeta->pluck('id_lokasi')->all());
    }

    public function test_unknown_location_returns_not_found(): void
    {
        $this->app->instance('request', Request::create('/siteplan', 'GET', ['id_lokasi'=>999]));
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        SiteplanSelection::load(['masterSvg', 'kavlingPeta']);
    }
}
