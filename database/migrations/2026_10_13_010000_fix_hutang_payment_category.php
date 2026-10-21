<?php

use App\Models\KategoriTransaksi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $kategori = KategoriTransaksi::pembayaranHutang();
            DB::table('pengeluaran')->where('id_hutang', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('id_kategori_transaksi')
                        ->orWhereNotIn('id_kategori_transaksi', DB::table('kategori_transaksi')->select('id'));
                })
                ->update(['id_kategori_transaksi' => $kategori->id]);
        });
    }

    public function down(): void
    {
        // Keep the category referenced by recorded payments.
    }
};
