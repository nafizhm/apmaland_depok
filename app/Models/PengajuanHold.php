<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanHold extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_hold';

    protected $fillable = [
        'no_registrasi',
        'tgl_booking',
        'nama_lengkap',
        'nik',
        'no_telp',
        'email',
        'alamat_ktp',
        'alamat_domisili',
        'jenis_kelamin',
        'tempat_lahir',
        'tgl_lahir',
        'npwp',
        'pekerjaan',
        'status_pernikahan',
        'nama_p',
        'nik_p',
        'no_bpjs_kes',
        'nama_saudara',
        'no_telp_saudara',
        'foto_ktp',
        'foto_npwp',
        'foto_kk',
        'foto_bpjs',
        'foto_pemohon',
        'foto_ktp_p',
        'booking_fee',
        'id_bank',
        'id_metode_bayar',
        'file_bukti',
        'file_sppr',
        'id_marketing',
        'id_lokasi',
        'id_kavling',
        'total_harga',
        'harga_snapshot',
        'jenis_perumahan',
        'jenis_pembelian',
        'pembayaran_cash',
        'an_surat_cash',
        'dp_cash_b',
        'termin_x_cash_b',
        'dp_kpr',
        'lama_cicilan_kpr',
        'cicilan_kpr',
        'tgl_tempo_cicilan_1',
        'an_surat_kpr',
        'stt_reg',
    ];

    protected $casts = ['harga_snapshot' => 'array'];

    public $timestamps = false;

    public function scopeVerificationQueue($query)
    {
        return $query->where('stt_reg', 1)
            ->orderByDesc('tgl_booking')->orderByDesc('id');
    }

    public function metodeBayar()
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar');
    }

    public function attachmentUrl(string $field): ?string
    {
        $path = $this->attachmentPath($field);
        return $path ? asset($path) : null;
    }

    public function attachmentPath(string $field): ?string
    {
        if (!in_array($field, ['foto_ktp', 'foto_npwp', 'foto_kk', 'foto_bpjs', 'foto_ktp_p', 'foto_pemohon', 'file_bukti', 'file_sppr'], true)) return null;
        $filename = $this->getAttribute($field);
        if (!$filename) return null;
        $filename = basename($filename);
        $directories = ['assets/booking'];
        if ((int) $this->stt_reg === 2) {
            $directories[] = 'assets/customer';
            if ($field === 'file_bukti') $directories[] = 'assets/keuangan/pemasukan';
        }
        foreach ($directories as $directory) {
            if (is_file(public_path($directory . '/' . $filename))) return $directory . '/' . $filename;
        }
        return null;
    }

    public function getRincianBiayaAttribute()
    {
        return $this->harga_snapshot['rincian_biaya'] ?? $this->kavling->rincian_biaya ?? [];
    }

    public function marketing()
    {
        return $this->belongsTo(MarketingOffline::class, 'id_marketing');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function kavling()
    {
        return $this->belongsTo(KavlingPeta::class, 'id_kavling');
    }
}
