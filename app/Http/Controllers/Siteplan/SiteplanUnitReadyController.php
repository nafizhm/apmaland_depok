<?php

namespace App\Http\Controllers\Siteplan;

use App\Http\Controllers\Controller;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Models\ProgresUnitReady;

class SiteplanUnitReadyController extends Controller
{
    public function index()
    {
        $selection = \App\Services\SiteplanSelection::load(['masterSvg', 'kavlingPeta']);

        $legend = ProgresUnitReady::orderBy('id', 'asc')
            ->get();

        return view('admin.siteplan.siteplan_unit_ready.index', $selection + compact('legend'));
    }

    public function show($id)
    {
        $data = KavlingPeta::with(['lokasi', 'customer'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    private function generateSVG($id_lokasi, $width = 500, $height = 200)
    {
        $lokasi = LokasiKavling::with(['masterSvg', 'kavlingPeta.unitReady'])
            ->findOrFail($id_lokasi);

        if (! $lokasi->masterSvg) {
            abort(404, "Data master_svg tidak ditemukan");
        }

        ob_start();

        echo $lokasi->masterSvg->header_xml;
        $header = str_replace(['[[lebar]]', '[[tinggi]]'], [$width, $height], $lokasi->masterSvg->header_svg);
        echo $header;

        foreach ($lokasi->kavlingPeta as $pt) {
            $warna = '#ffffff';
            if ($pt->status_ready && $pt->unitReady) {
                $warna = $pt->unitReady->warna;
            }

            $replacements = [$pt->map, $warna, $pt->matrik, $pt->kode_kavling];

            if ($pt->jenis_map === 'polygon') {
                echo str_replace(['[[1]]', '[[2]]', '[[3]]', '[[4]]'], $replacements, $lokasi->masterSvg->polygon_svg);
            } elseif ($pt->jenis_map === 'path') {
                echo str_replace(['[[1]]', '[[2]]', '[[3]]', '[[4]]'], $replacements, $lokasi->masterSvg->path_svg);
            }
        }

        echo $lokasi->masterSvg->footer_svg;

        return ob_get_clean();
    }

    public function cetakJPG($id_lokasi)
    {
        return app(\App\Services\SiteplanPrint::class)->download(
            $this->generateSVG($id_lokasi), (int) $id_lokasi, 'Unit Ready', 'jpg'
        );
    }

    public function cetakPDF($id_lokasi)
    {
        return app(\App\Services\SiteplanPrint::class)->download(
            $this->generateSVG($id_lokasi), (int) $id_lokasi, 'Unit Ready', 'pdf'
        );
    }
}
