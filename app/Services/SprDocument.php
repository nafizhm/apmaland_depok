<?php

namespace App\Services;

use App\Models\SPPR;

class SprDocument
{
    public const TEXT_FIELDS = [
        'nama_perumahan' => 'Nama Perumahan',
        'alamat_perumahan' => 'Alamat Perumahan',
        'telepon_perumahan' => 'Telepon Perumahan',
        'alamat_surat' => 'Alamat Surat Menyurat',
        'telepon_rumah' => 'Telepon Rumah',
        'email' => 'Email',
        'id_rumah' => 'ID Rumah',
        'tipe_rumah' => 'Tipe Rumah',
        'promo_termasuk_1' => 'Harga Termasuk (1)',
        'promo_termasuk_2' => 'Harga Termasuk (2)',
        'promo_termasuk_3' => 'Harga Termasuk (3)',
        'promo_belum_1' => 'Harga Belum Termasuk (1)',
        'promo_belum_2' => 'Harga Belum Termasuk (2)',
        'promo_belum_3' => 'Harga Belum Termasuk (3)',
    ];

    public const MONEY_FIELDS = [
        'diskon' => 'Besaran Diskon',
        'harga_kelebihan_tanah' => 'Harga Kelebihan Tanah (Total)',
        'biaya_sertifikat' => 'Biaya Sertifikat',
        'biaya_bphtb' => 'Biaya BPHTB',
        'nominal_booking' => 'Nominal Tanda Jadi',
        'nominal_angsuran_pertama' => 'Nominal Angsuran Pertama',
    ];

    public static function rules(): array
    {
        $rules = [
            'spr_data' => ['nullable', 'array:' . implode(',', array_merge(array_keys(self::TEXT_FIELDS), array_keys(self::MONEY_FIELDS), ['jenis_pembelian', 'kelebihan_tanah', 'tanggal_booking', 'tanggal_angsuran_pertama', 'angsuran']))],
            'spr_data.jenis_pembelian' => 'nullable|in:KPR,TUNAI',
            'spr_data.kelebihan_tanah' => 'nullable|numeric|min:0|max:1000000',
            'spr_data.tanggal_booking' => 'nullable|date_format:Y-m-d',
            'spr_data.tanggal_angsuran_pertama' => 'nullable|date_format:Y-m-d',
            'spr_data.angsuran' => 'nullable|array|max:20',
            'spr_data.angsuran.*' => 'array:tanggal,nominal',
            'spr_data.angsuran.*.tanggal' => 'required|date_format:Y-m-d',
            'spr_data.angsuran.*.nominal' => 'required|integer|min:1|max:999999999999',
        ];
        foreach (self::TEXT_FIELDS as $field => $label) {
            $rules['spr_data.' . $field] = $field === 'email' ? 'nullable|email|max:255' : 'nullable|string|max:500';
        }
        foreach (self::MONEY_FIELDS as $field => $label) {
            $rules['spr_data.' . $field] = 'nullable|integer|min:0|max:999999999999';
        }

        return $rules;
    }

    public static function totals(SPPR $spr): array
    {
        $data = $spr->spr_data ?? [];
        $afterDiscount = (int) $spr->harga_jual - (int) ($data['diskon'] ?? 0);
        $land = (int) ($data['harga_kelebihan_tanah'] ?? 0);
        $admin = (int) ($data['biaya_sertifikat'] ?? 0) + (int) ($data['biaya_bphtb'] ?? 0);

        return [
            'harga_setelah_diskon' => $afterDiscount,
            'total_harga' => $afterDiscount + $land,
            'total_administrasi' => $admin,
            'kewajiban_kpr' => (int) $spr->nominal_dp + $land + $admin,
            'kewajiban_tunai' => $afterDiscount + $land + $admin,
            'jumlah_jadwal' => (int) ($data['nominal_booking'] ?? 0) + (int) ($data['nominal_angsuran_pertama'] ?? 0)
                + collect($data['angsuran'] ?? [])->sum('nominal'),
        ];
    }
}
