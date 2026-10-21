<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Customer;
use App\Models\SPPR;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\SprDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SPPRController extends Controller
{
    use LogAktivitasTrait;

    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = SPPR::with(['customer.kavling', 'customer.marketing'])->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_nama', function ($row) {
                    return $row->nama;
                })
                ->addColumn('lokasi_unit', function ($row) {
                    return $row->customer?->kavling?->kode_kavling ?? '-';
                })
                ->addColumn('nama_marketing', function ($row) {
                    return $row->customer?->marketing?->nama_marketing ?? '-';
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $cetakUrl = route('sppr.cetak', $row->id);
                    $editUrl = route('sppr.edit', $row->id);
                    $deleteUrl = route('sppr.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-info btn-sm mx-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit</button>';
                    }
                    $btn .= '<a href="' . e($cetakUrl) . '" target="_blank" class="btn btn-dark btn-sm mx-1">Cetak</a>';
                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">Hapus</button>
                        </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $customerList = Customer::orderBy('nama_lengkap')->get();

        return view('admin.transaksi.sppr.index', compact('permissions', 'customerList'));
    }

    public function getCustomerDetail($id)
    {
        $customer = Customer::with(['lokasi', 'kavling'])->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => array_merge([
            'nama' => $customer->nama_lengkap,
            'alamat' => $customer->alamat_ktp ?? $customer->alamat_domisili ?? '',
            'nik' => $customer->nik,
            'no_telp' => $customer->no_telp,
            'lokasi_unit' => $customer->kavling?->kode_kavling ?? '',
            'harga_jual' => $customer->hrg_jual ?? 0,
            'alamat_surat' => $customer->alamat_domisili ?? $customer->alamat_ktp ?? '',
            'email' => $customer->email ?? '',
            'nama_perumahan' => $customer->lokasi?->nama_kavling ?? '',
            'alamat_perumahan' => $customer->lokasi?->alamat ?? '',
            'tipe_rumah' => $customer->kavling?->tipe_bangunan ?? '',
            'id_rumah' => $customer->kavling?->id_rumah_sikumbang ?? '',
            'asumsi_plafon_kpr' => $customer->estimasi_plafon ?? 0,
        ], $this->kavlingData($customer))]);
    }

    private function kavlingData(Customer $customer): array
    {
        $kavling = $customer->kavling;
        if ($customer->lokasi?->is_cluster) {
            $blok = $kavling?->cluster ?? '';
            $no = $kavling?->no ?? '';
        } else {
            $parts = explode('-', $kavling?->kode_kavling ?? '', 2);
            $blok = $parts[0];
            $no = $parts[1] ?? '';
        }

        return [
            'blok' => $blok,
            'no' => $no,
            'luas_bangunan' => $kavling?->luas_bangunan ?? 0,
            'luas_tanah' => $kavling?->luas_tanah ?? 0,
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'id_customer' => 'required|integer|exists:customer,id',
            'no_sppr' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'nik' => 'nullable|string|max:20',
            'no_telp' => 'required|string|max:20',
            'harga_jual' => 'required|integer|min:0',
            'nominal_dp' => 'required|integer|min:0',
            'asumsi_plafon_kpr' => 'required|integer|min:0',
            'penandatangan' => 'nullable|string|max:255',
        ] + SprDocument::rules());
        if (($data['spr_data']['diskon'] ?? 0) > $data['harga_jual']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['spr_data.diskon' => 'Diskon tidak boleh melebihi harga rumah.']);
        }
        foreach (['booking', 'angsuran_pertama'] as $payment) {
            if (($data['spr_data']['nominal_' . $payment] ?? 0) > 0 && empty($data['spr_data']['tanggal_' . $payment])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['spr_data.tanggal_' . $payment => 'Tanggal pembayaran wajib diisi jika nominal diisi.']);
            }
        }
        $customer = Customer::with(['lokasi', 'kavling'])->findOrFail($data['id_customer']);
        if (!$customer->kavling) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_customer' => 'Customer belum memiliki data kavling. Lengkapi kavling terlebih dahulu.',
            ]);
        }

        // Ukuran dan unit selalu berasal dari menu kavling, bukan input browser.
        return array_merge($data, ['nik' => $data['nik'] ?? $customer->nik ?? ''], $this->kavlingData($customer));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $sppr = SPPR::create($data);
        $this->logCreate('SPR', $sppr->id);

        return response()->json(['status' => 'success']);
    }

    public function edit($id)
    {
        $sppr = SPPR::with(['customer.lokasi', 'customer.kavling'])->findOrFail($id);
        $data = $sppr->toArray();
        $data['lokasi_unit'] = $sppr->customer?->kavling?->kode_kavling ?? '';
        if ($sppr->customer) {
            $data = array_merge($data, $this->kavlingData($sppr->customer));
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $sppr = SPPR::findOrFail($id);
        $sppr->update($this->validatedData($request));
        $this->logEdit('SPR', $sppr->id);

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $sppr = SPPR::findOrFail($id);

        $this->logDelete('SPR', $sppr->id);
        $sppr->delete();

        return response()->json(['status' => 'success']);
    }


    public function cetak($id)
    {
        $spr = SPPR::with(['customer.lokasi', 'customer.kavling'])->findOrFail($id);
        abort_unless($spr->customer?->kavling, 422, 'Data kavling customer belum tersedia.');
        $unit = $this->kavlingData($spr->customer);
        $data = $spr->spr_data ?? [];
        $totals = SprDocument::totals($spr);
        $logo = \App\Models\PengaturanMedia::where('jenis_data', 'Logo Aplikasi')->where('stt_aktif', 1)->first();
        $logoPath = $logo?->nama_file ? public_path('config_media/' . basename($logo->nama_file)) : null;
        if (!$logoPath || !is_file($logoPath)) $logoPath = null;
        $pdf = Pdf::loadView('admin.transaksi.sppr.print', compact('spr', 'unit', 'data', 'totals', 'logoPath'))
            ->setPaper('a4')->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);

        return $pdf->stream('SPR_' . Str::slug($spr->nama) . '.pdf');
    }
}