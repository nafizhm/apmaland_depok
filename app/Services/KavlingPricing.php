<?php

namespace App\Services;

use App\Models\KavlingPeta;
use Illuminate\Validation\ValidationException;

class KavlingPricing
{
    public const FIELDS = [
        'harga_cash' => 'Harga Cash Keras',
        'dp_cash_bertahap' => 'DP Cash Bertahap (50%)',
        'harga_bertahap_3' => 'Harga Bertahap 3x',
        'cicilan_3' => 'Cicilan 3x',
        'harga_bertahap_6' => 'Harga Bertahap 6x',
        'cicilan_6' => 'Cicilan 6x',
        'harga_bertahap_12' => 'Harga Bertahap 12x',
        'cicilan_12' => 'Cicilan 12x',
    ];

    public static function quote(KavlingPeta $unit, ?string $method, $tenor = null): array
    {
        $total = $unit->total_harga;
        $rincian = $unit->rincian_biaya ?? [];
        $dp = $installment = 0;
        $priced = $unit->harga_cash !== null && in_array($method, ['Pembelian Cash', 'Cash Bertahap'], true);
        if ($priced) {
            if ($method === 'Cash Bertahap') {
                if (!in_array((string) $tenor, ['3', '6', '12'], true)) {
                    throw ValidationException::withMessages(['termin_x_cash_b' => 'Pilih cicilan 3x, 6x, atau 12x.']);
                }
                $total = (int) $unit->{'harga_bertahap_' . $tenor};
                $dp = (int) $unit->dp_cash_bertahap;
                $installment = (int) $unit->{'cicilan_' . $tenor};
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
            'total_harga' => $total, 'dp' => $dp, 'cicilan' => $installment,
            'rincian_biaya' => $rincian];
    }
}
