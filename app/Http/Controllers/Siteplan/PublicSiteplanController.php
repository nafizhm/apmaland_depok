<?php

namespace App\Http\Controllers\Siteplan;

use App\Http\Controllers\Controller;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Models\ProgresListPenjualan;
use Illuminate\Http\Request;
use App\Models\PengaturanMedia;
use App\Models\Piutang;
use App\Models\Pemasukan;

class PublicSiteplanController extends Controller
{
    /**
     * Display the public siteplan.
     */
     public function index()
    {
        $selection = \App\Services\SiteplanSelection::load([
            'masterSvg',
            'kavlingPeta' => fn ($query) => $query->withBookingState(),
            'kavlingPeta.customer.progres',
            'kavlingPeta.progres',
        ]);

        $legend = ProgresListPenjualan::forColorSettings()->whereNotNull('warna')
            ->where('warna', '!=', '')
            ->where('stt_tampil', 1)
            ->orderBy('urutan', 'asc')
            ->get();

            $bg        = PengaturanMedia::where('jenis_data', 'Background booking')->first();

        $holdColor = ProgresListPenjualan::whereRaw('LOWER(TRIM(status_progres)) = ?', ['hold'])->value('warna') ?: '#ffc107';

        return view('public_siteplan.index', $selection + compact('legend', 'bg', 'holdColor'));
    }

    /**
     * Fetch kavling details for the public popup.
     */
     public function show($id)
    {
        $data = KavlingPeta::withBookingState()->with(['lokasi', 'customer', 'listrikAir'])->findOrFail($id);

        $tagihanList   = Piutang::where('id_customer', $data->id_customer)->orderBy('id')->get();
        $pemasukanList = Pemasukan::with('kategori')->where('id_customer', $data->id_customer)->get();

        return response()->json([
            'success'         => true,
            'data'            => $data,
            'tagihan'         => $tagihanList,
            'listrik_air' => $data->listrikAir,
            'pemasukan'       => $pemasukanList,
            'total_tagihan'   => $tagihanList->sum('nominal'),
            'total_pemasukan' => $pemasukanList->sum('nominal'),
        ]);
    }
}
