<?php

namespace App\Services;

use App\Models\KavlingPeta;
use Illuminate\Validation\ValidationException;

class KavlingPricing
{
    public const FIELDS = [
        'harga_cash' => 'Harga Cash Keras',
        'dp_cash_bertahap' => 'DP Cash Bertahap (50%)',
        'harga_bertahap_3' => 'Harga Bertahap 3x (DP 50%)',
        'cicilan_3' => 'Cicilan 3x (DP 50%)',
        'harga_bertahap_6' => 'Harga Bertahap 6x (DP 50%)',
        'cicilan_6' => 'Cicilan 6x (DP 50%)',
        'harga_bertahap_12' => 'Harga Bertahap 12x (DP 50%)',
        'cicilan_12' => 'Cicilan 12x (DP 50%)',
    ];

    public const DP30_FIELDS = [
        'dp_cash_bertahap_30' => 'DP Cash Bertahap (30%)',
        'harga_bertahap_3_dp30' => 'Harga Bertahap 3x (DP 30%)',
        'cicilan_3_dp30' => 'Cicilan 3x (DP 30%)',
        'harga_bertahap_6_dp30' => 'Harga Bertahap 6x (DP 30%)',
        'cicilan_6_dp30' => 'Cicilan 6x (DP 30%)',
        'harga_bertahap_12_dp30' => 'Harga Bertahap 12x (DP 30%)',
        'cicilan_12_dp30' => 'Cicilan 12x (DP 30%)',
    ];

    public static function fields(): array
    {
        return array_merge(self::FIELDS, self::DP30_FIELDS);
    }

    public static function dp30Prices(array $prices): array
    {
        $dp = (int) round((int) $prices['harga_cash'] * 30 / 100);
        $result = ['dp_cash_bertahap_30' => $dp];
        foreach ([3, 6, 12] as $tenor) {
            $total = (int) $prices['harga_bertahap_' . $tenor];
            $result['harga_bertahap_' . $tenor . '_dp30'] = $total;
            $result['cicilan_' . $tenor . '_dp30'] = (int) round(($total - $dp) / $tenor);
        }
        return $result;
    }

    public static function quote(KavlingPeta $unit, ?string $method, $tenor = null, $dpPercent = 50): array
    {
        $total = $unit->total_harga;
        $rincian = $unit->rincian_biaya ?? [];
        if ($method === 'KPR') {
            $total = (int) $unit->plafon_kpr;
        }
        $dp = $installment = 0;
        $priced = $unit->harga_cash !== null && in_array($method, ['Pembelian Cash', 'Cash Bertahap'], true);
        if ($priced) {
            if ($method === 'Cash Bertahap') {
                if (!in_array((string) $dpPercent, ['30', '50'], true)) {
                    throw ValidationException::withMessages(['dp_persen' => 'Pilih DP 30% atau 50%.']);
                }
                if (!in_array((string) $tenor, ['3', '6', '12'], true)) {
                    throw ValidationException::withMessages(['termin_x_cash_b' => 'Pilih cicilan 3x, 6x, atau 12x.']);
                }
                $suffix = (int) $dpPercent === 30 ? '_dp30' : '';
                $total = (int) $unit->{'harga_bertahap_' . $tenor . $suffix};
                $dp = (int) $unit->{(int) $dpPercent === 30 ? 'dp_cash_bertahap_30' : 'dp_cash_bertahap'};
                $installment = (int) $unit->{'cicilan_' . $tenor . $suffix};
            } else {
                $total = (int) $unit->harga_cash;
            }
            $rincian = [['nama' => 'Harga Rumah', 'nilai' => $total]];
        }
        if ($total <= 0 || ($priced && $method === 'Cash Bertahap' && ($dp <= 0 || $installment <= 0))) {
            throw ValidationException::withMessages(['id_kavling' => 'Harga kavling untuk pilihan ini belum ditentukan.']);
        }

        return ['id_kavling' => $unit->id, 'jenis_pembelian' => $method,
            'tenor' => $method === 'Cash Bertahap' ? (int) $tenor : null,
            'dp_persen' => $method === 'Cash Bertahap' ? (int) $dpPercent : null,
            'total_harga' => $total, 'dp' => $dp, 'cicilan' => $installment,
            'rincian_biaya' => $rincian];
    }
}
