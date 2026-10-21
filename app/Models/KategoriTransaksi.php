<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';

    protected $fillable = ['kode', 'kategori', 'jenis_kategori', 'stt_fix'];

    public $timestamps = false;

    public static function pembayaranHutang(): self
    {
        return static::firstOrCreate(
            ['kategori' => 'Pembayaran Hutang', 'jenis_kategori' => 'PENGELUARAN'],
            ['kode' => 'HUTANG', 'stt_fix' => 1]
        );
    }
}
