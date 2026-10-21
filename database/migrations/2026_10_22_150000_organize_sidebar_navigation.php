<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    private string $backup = 'backups/sidebar-before-20261022.json';

    public function up(): void
    {
        if (!Storage::disk('local')->exists($this->backup)) {
            Storage::disk('local')->put($this->backup, DB::table('menu')->get(['id', 'title', 'icon', 'urutan'])->toJson());
        }
        DB::transaction(function () {
            $groups = [
                'Beranda' => ['Dashboard', 'fa-home'],
                'Siteplan' => ['Siteplan', 'fa-map-marked-alt'],
                'Transaksi' => ['Penjualan', 'fa-handshake'],
                'Customer' => ['Pelanggan', 'fa-users'],
                'Mutasi Transaksi' => ['Perubahan Transaksi', 'fa-exchange-alt'],
                'Cetak Berkas' => ['Dokumen Penjualan', 'fa-file-signature'],
                'Legal' => ['Legalitas & Utilitas', 'fa-balance-scale'],
                'Keuangan' => ['Keuangan', 'fa-wallet'],
                'Pembelian' => ['Pembelian', 'fa-shopping-cart'],
                'Laporan' => ['Laporan', 'fa-chart-bar'],
                'Master Data' => ['Data Master', 'fa-database'],
                'Pengaturan' => ['Pengaturan', 'fa-cog'],
            ];
            foreach (array_keys($groups) as $index => $oldTitle) {
                [$title, $icon] = $groups[$oldTitle];
                DB::table('menu')->where('id_parent', 0)->where('title', $oldTitle)
                    ->update(['title' => $title, 'icon' => $icon, 'urutan' => $index + 1]);
            }

            $children = [
                'siteplan-penjualan.index' => ['Penjualan', 1],
                'st-bphtb-ssp.index' => ['BPHTB & SSP', 2],
                'st-balik-nama.index' => ['Balik Nama', 3],
                'siteplan-listrik.index' => ['Listrik', 4],
                'siteplan-air.index' => ['Air', 5],
                'pengajuan-hold.index' => ['Booking & Reservasi', 1],
                'wawancara.index' => ['Pengajuan KPR', 2],
                'acc-bank.index' => ['Persetujuan KPR (SP3K)', 3],
                'akad.index' => ['Akad Penjualan', 4],
                'customer.index' => ['Data Pelanggan', 1],
                'upload-file.index' => ['Dokumen Pelanggan', 2],
                'arsip-customer.index' => ['Arsip Pelanggan', 3],
                'pindah-unit.index' => ['Perpindahan Unit', 1],
                'ganti-nama.index' => ['Perubahan Nama Pembeli', 2],
                'pembelian-cancel.index' => ['Pembatalan Pembelian', 3],
                'sppr.index' => ['SPR', 1],
                'bast.index' => ['Serah Terima (BAST)', 2],
                'pengajuan-berkas.index' => ['Pengajuan Dokumen', 1],
                'bphtb-ssp.index' => ['BPHTB & SSP', 2],
                'balik-nama.index' => ['Balik Nama Sertifikat', 3],
                'listrik-air.index' => ['Pemasangan Listrik & Air', 4],
                'pembayaran.index' => ['Pembayaran Pelanggan', 1],
                'piutang.index' => ['Piutang Pelanggan', 2],
                'pemasukan.index' => ['Penerimaan Dana', 3],
                'pengeluaran.index' => ['Pengeluaran Dana', 4],
                'hutang.index' => ['Utang & Pembayaran', 5],
                'keuangan-retensi.index' => ['Pembayaran Retensi', 6],
                'mutasi-saldo.index' => ['Transfer Antar Rekening', 7],
                'laporan-arus-kas.index' => ['Laporan Arus Kas', 8],
                'kategori-transaksi.index' => ['Kategori Keuangan', 9],
                'perusahaan.index' => ['Perusahaan', 1],
                'lokasi-kavling.index' => ['Proyek Perumahan', 2],
                'kavling.index' => ['Unit & Kavling', 3],
                'marketing-offline.index' => ['Tim Marketing', 4],
                'bank-transaksi.index' => ['Rekening Transaksi', 5],
                'bank-kpr.index' => ['Bank KPR', 6],
                'notaris.index' => ['Notaris', 7],
                'jenis-berkas.index' => ['Jenis Dokumen', 8],
                'retensi.index' => ['Data Retensi', 9],
                'pengaturan-profil.index' => ['Profil Aplikasi', 1],
                'pengaturan-media.index' => ['Logo & Media', 2],
                'list-penjualan.index' => ['Status Penjualan', 3],
                'pengaturan-pengguna.index' => ['Pengguna', 4],
                'role-user.index' => ['Peran Pengguna', 5],
                'hak-akses.index' => ['Hak Akses', 6],
                'log-aktivitas.index' => ['Riwayat Aktivitas', 7],
            ];
            foreach ($children as $route => [$title, $order]) {
                DB::table('menu')->where('route_name', $route)->where('id_parent', '!=', 0)
                    ->update(['title' => $title, 'urutan' => $order]);
            }
        });
    }

    public function down(): void
    {
        if (!Storage::disk('local')->exists($this->backup)) return;
        DB::transaction(function () {
            foreach (json_decode(Storage::disk('local')->get($this->backup), true) as $menu) {
                $id = $menu['id'];
                unset($menu['id']);
                DB::table('menu')->where('id', $id)->update($menu);
            }
        });
    }
};
