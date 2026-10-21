<?php
namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Bank;
use App\Models\KategoriTransaksi;
use App\Models\Pemasukan;
use App\Models\Piutang;
use App\Traits\LogAktivitasTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PemasukanController extends Controller
{
    use LogAktivitasTrait;
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Pemasukan::with(['kategori', 'piutang'])->orderByDesc('tanggal');

            if ($request->filled('filter_tanggal')) {
                $data->whereDate('tanggal', $request->filter_tanggal);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->translatedFormat('j F Y');
                })
                ->editColumn('nominal', function ($row) {
                    return '
                    <div class="d-flex justify-content-between harga-format w-100">
                        <span>Rp.</span>
                        <span>' . number_format($row->nominal, 0, ',', '.') . '</span>
                    </div>';
                })
                ->addColumn('rekening', function ($row) {
                    $bank = Bank::find($row->id_bank);
                    return $bank ? $bank->nama : 'Bank Tidak Diketahui';
                })
                ->addColumn('id_kategori_transaksi', function ($row) {
                    $nama = e($row->kategori->kategori ?? 'Tidak Diketahui');
                    $deskripsi = $row->piutang->deskripsi ?? '';

                    return $deskripsi !== ''
                        ? $nama . '<br><small class="text-muted">' . e($deskripsi) . '</small>'
                        : $nama;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->input('search.value') != '') {
                        $search = $request->input('search.value');

                        $bankIds     = Bank::where('nama', 'like', "%$search%")->pluck('id');
                        $kategoriIds = KategoriTransaksi::where('kategori', 'like', "%$search%")->pluck('id');

                        $query->where(function ($q) use ($bankIds, $kategoriIds, $search) {
                            $q->whereIn('id_bank', $bankIds)
                                ->orWhereIn('id_kategori_transaksi', $kategoriIds)
                                ->orWhereHas('piutang', function ($piutang) use ($search) {
                                    $piutang->where('deskripsi', 'like', "%$search%");
                                })
                                ->orWhere('keterangan', 'like', "%$search%");
                        });
                    }
                })
                ->filterColumn('tanggal', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->WhereDate('tanggal', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('pemasukan.edit', $row->id);
                    $deleteUrl = route('pemasukan.destroy', $row->id);

                    $isDetailOnly = $row->id_hutang != 0 || $row->id_customer != 0 || $row->id_mutasi != 0;

                    $btn = '<div class="d-flex justify-content-center">';

                    if ($permissions['edit']) {
                        if ($row->plafon_sp3k !== null && $row->id_customer) {
                            $btn .= '<a class="btn btn-primary btn-sm mx-1" href="' . e(route('pembayaran.detail', $row->id_customer)) . '">Edit</a>';
                        } elseif ($row->id_mutasi) {
                            $btn .= '<a class="btn btn-primary btn-sm mx-1" href="' . e(route('mutasi-saldo.index')) . '">Edit</a>';
                        } else {
                            $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                data-id="' . e($row->id) . '"
                data-url="' . e($editUrl) . '">
                Edit
            </button>';
                        }
                    }

                    if ($permissions['hapus']) {
                        if ($isDetailOnly) {
                            $btn .= '<button class="btn btn-danger btn-sm mx-1" disabled>
                Hapus
            </button>';
                        } else {
                            $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                ' . csrf_field() . method_field('DELETE') . '
                <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                    Hapus
                </button>
            </form>';
                        }
                    }

                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['action', 'tanggal', 'nominal', 'id_kategori_transaksi'])
                ->make(true);
        }

        $kategoriTransaksi = KategoriTransaksi::where('jenis_kategori', 'PEMASUKAN')
            ->orderBy('id')
            ->get();

        $kategoriTransaksiDetail = $kategoriTransaksi;

        $PiutangList = Piutang::where(function ($query) {
            $query->whereNull('id_customer')->orWhere('id_customer', 0);
        })->get();

        $bankList = Bank::all();

        return view('admin.keuangan.pemasukan.index', compact('permissions', 'kategoriTransaksi', 'kategoriTransaksiDetail', 'PiutangList', 'bankList'));
    }
    public function edit($id)
    {
        $list = Pemasukan::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function show($id)
    {
        $list = Pemasukan::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->filled('id_piutang')) {
            $request->validate(['id_piutang' => 'integer|min:1|exists:piutang,id']);
            $request->merge(['id_kategori_transaksi' => $this->kategoriPiutang($request->id_piutang)]);
        }
        abort_if(KategoriTransaksi::where('kategori', 'Pencairan KPR')->whereKey($request->id_kategori_transaksi)->exists(),
            422, 'Input pencairan KPR melalui detail pembayaran agar akad dan batas plafon diperiksa.');
        $request->validate([
            'tanggal'               => 'required|date',
            'nominal'               => 'required',
            'id_bank'               => 'required',
            'lampiran'              => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'id_kategori_transaksi' => 'required',
            'jenis_pemasukan'       => 'nullable|in:lainnya,piutang',
            'id_piutang'            => 'required_if:jenis_pemasukan,piutang|prohibited_if:jenis_pemasukan,lainnya|nullable|integer|min:1|exists:piutang,id',
        ], [
            'tanggal.required'               => 'Tanggal wajib diisi.',
            'tanggal.date'                   => 'Tanggal harus berupa tanggal.',
            'nominal.required'               => 'Nominal wajib diisi.',
            'id_bank.required'               => 'Rekening wajib dipilih.',
            'lampiran.required'              => 'Lampiran wajib diisi.',
            'lampiran.file'                  => 'Lampiran harus berupa file.',
            'lampiran.mimes'                 => 'Format lampiran harus jpg, jpeg, png, atau pdf.',
            'lampiran.max'                   => 'Ukuran file maksimal 2MB.',
            'id_kategori_transaksi.required' => 'Kategori transaksi wajib dipilih.',
            'id_piutang.required_if'         => 'Piutang wajib dipilih',
            'id_piutang.prohibited_if'       => 'Gunakan tombol Pelunasan Piutang untuk pembayaran piutang.',
        ]);

        if (! empty($request->id_piutang)) {
            $piutang = Piutang::find($request->id_piutang);

            if ($piutang) {
                $nominalInput = (int) str_replace('.', '', $request->nominal);

                if ($nominalInput > $piutang->sisa_bayar) {
                    return response()->json([
                        'errors' => ['nominal' => ['Nominal tidak boleh lebih besar dari sisa piutang.']],
                    ], 422);
                }
            }
        }

        $db = [
            'id_piutang'            => $request->id_piutang ?? 0,
            'id_hutang'             => 0,
            'id_invoice'            => 0,
            'id_mutasi'             => 0,
            'id_bank'               => $request->id_bank,
            'tanggal'               => $request->tanggal,
            'nominal'               => str_replace('.', '', $request->nominal),
            'id_kategori_transaksi' => $request->id_kategori_transaksi,
            'keterangan'            => $request->keterangan ?? '',
        ];

        if ($request->hasFile('lampiran')) {

            $file = $request->file('lampiran');
            $ext  = $file->getClientOriginalExtension();

            $filename = Str::random(25) . '.' . $ext;
            $file->move(public_path('assets/keuangan/pemasukan/'), $filename);

            $db['lampiran'] = $filename;
        }

        $pk = Pemasukan::create($db);
        $this->logCreate('Pemasukan', $pk->id);

        return response()->json(['status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $data = Pemasukan::findOrFail($id);
        if ($data->id_piutang) {
            $request->merge(['id_kategori_transaksi' => $this->kategoriPiutang($data->id_piutang)]);
        }
        abort_if($data->plafon_sp3k !== null, 422, 'Pencairan KPR dikelola melalui detail pembayaran.');

        $rules = [
            'tanggal' => 'required|date',
            'nominal' => 'required',
            'id_bank' => 'required',
        ];

        $rules['id_kategori_transaksi'] = [
            'required',
            $data->id_piutang
                ? \Illuminate\Validation\Rule::exists('kategori_transaksi', 'id')
                : \Illuminate\Validation\Rule::exists('kategori_transaksi', 'id')->where('jenis_kategori', 'PEMASUKAN'),
        ];

        if (empty($data->lampiran)) {
            $rules['lampiran'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
        } elseif ($request->hasFile('lampiran')) {
            $rules['lampiran'] = 'file|mimes:jpg,jpeg,png,pdf|max:2048';
        }

        $request->validate($rules, [
            'id_kategori_transaksi.required' => 'Kategori transaksi wajib dipilih.',
            'id_kategori_transaksi.exists' => 'Pilih kategori transaksi berjenis pemasukan.',
            'tanggal.required'  => 'Tanggal wajib diisi.',
            'tanggal.date'      => 'Tanggal harus berupa tanggal.',
            'nominal.required'  => 'Nominal wajib diisi.',
            'id_bank.required'  => 'Rekening wajib dipilih.',
            'lampiran.required' => 'Lampiran wajib diisi.',
            'lampiran.file'     => 'Lampiran harus berupa file.',
            'lampiran.mimes'    => 'Format lampiran harus jpg, jpeg, png, atau pdf.',
            'lampiran.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        if (KategoriTransaksi::whereKey($request->id_kategori_transaksi)->where('kategori', 'Pencairan KPR')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_kategori_transaksi' => 'Pencairan KPR dikelola melalui detail pembayaran.',
            ]);
        }

        $db = [
            'tanggal'    => $request->tanggal,
            'id_kategori_transaksi' => $request->id_kategori_transaksi,
            'id_bank'    => $request->id_bank,
            'keterangan' => $request->keterangan ?? '',
            'nominal'    => str_replace('.', '', $request->nominal),
        ];

        if ($request->hasFile('lampiran')) {
            if (! empty($data->lampiran) && file_exists(public_path('assets/keuangan/pemasukan/' . $data->lampiran))) {
                unlink(public_path('assets/keuangan/pemasukan/' . $data->lampiran));
            }

            $file = $request->file('lampiran');
            $ext  = $file->getClientOriginalExtension();

            $filename = Str::random(25) . '.' . $ext;
            $file->move(public_path('assets/keuangan/pemasukan/'), $filename);

            $db['lampiran'] = $filename;
        }

        if ($request->has('id_piutang')) {
            $request->validate(['id_piutang' => 'nullable|integer|min:1|exists:piutang,id']);
            $db['id_piutang'] = $request->id_piutang ?? 0;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $db) {
            if ($data->id_hutang) {
                $hutang = \App\Models\Hutang::whereKey($data->id_hutang)->lockForUpdate()->firstOrFail();
                validator(['nominal' => $db['nominal']], [
                    'nominal' => 'required|integer|min:'.$hutang->terbayar,
                ], ['nominal.min' => 'Nominal hutang tidak boleh kurang dari jumlah yang sudah dibayar.'])->validate();
                $remaining = (int) $db['nominal'] - (int) $hutang->terbayar;
                $hutang->update([
                    'nominal' => $db['nominal'], 'sisa_bayar' => $remaining,
                    'status' => $remaining > 0 ? 1 : 2,
                    'tgl_pelunasan' => $remaining > 0 ? null : $hutang->tgl_pelunasan,
                    'tanggal_hutang' => $db['tanggal'], 'id_bank' => $db['id_bank'],
                    'lampiran' => $db['lampiran'] ?? $data->lampiran,
                ]);
            }
            $data->update($db);
        });
        $this->logEdit('Pemasukan', $data->id);

        return response()->json(['status' => 'success']);
    }

    private function kategoriPiutang($id)
    {
        $piutang = Piutang::find($id);
        if (! $piutang || ! $piutang->id_kategori_transaksi
            || ! KategoriTransaksi::whereKey($piutang->id_kategori_transaksi)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_piutang' => 'Kategori piutang belum tersedia. Lengkapi kategori pada data piutang terlebih dahulu.',
            ]);
        }

        return $piutang->id_kategori_transaksi;
    }

    public function destroy($id)
    {
        $data = Pemasukan::findOrFail($id);
        abort_if($data->plafon_sp3k !== null, 422, 'Pencairan KPR dikelola melalui detail pembayaran.');
        if (! empty($data->lampiran) && file_exists(public_path('assets/keuangan/pemasukan/' . $data->lampiran))) {
            unlink(public_path('assets/keuangan/pemasukan/' . $data->lampiran));
        }

        $this->logDelete('Pemasukan', $data->id);
        $data->delete();

        return response()->json(['status' => 'success']);
    }
}
