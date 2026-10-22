<?php

namespace App\Console\Commands;

use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Services\KavlingPricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportParamitaPrices extends Command
{
    protected $signature = 'prices:import-paramita {--apply : Simpan harga setelah semua unit cocok} {--dp30-only : Hanya isi skema DP 30% berdasarkan harga cash dan bertahap tersimpan}';
    protected $description = 'Import harga rev.pdf Paramita 22 Oktober 2026 (default: pratinjau)';

    public function handle(): int
    {
        $locations = LokasiKavling::whereRaw('LOWER(TRIM(nama_kavling)) IN (?, ?)', ['pramita residence', 'paramita residence'])->get();
        if ($locations->count() !== 1) {
            $this->error('Lokasi paramita residence harus cocok tepat satu.');
            return self::FAILURE;
        }
        $rows = json_decode(file_get_contents(database_path('data/paramita-harga-rev-2026-10-22.json')), true, 512, JSON_THROW_ON_ERROR);
        $normalize = fn ($code) => preg_replace_callback('/^([A-D])-?0*(\d+)$/', fn ($m) => $m[1] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT), strtoupper(trim($code)));
        $units = KavlingPeta::where('id_lokasi', $locations->first()->id)->get()->groupBy(fn ($unit) => $normalize($unit->kode_kavling));
        $updates = [];
        foreach ($rows as $row) {
            foreach ($row['units'] as $code) {
                $matches = $units->get($code, collect());
                if ($matches->count() !== 1) {
                    $this->error("Unit {$code} tidak cocok tepat satu.");
                    return self::FAILURE;
                }
                $unit = $matches->first();
                if ((float) $unit->luas_tanah !== (float) $row['luas_tanah'] || (float) $unit->luas_bangunan !== (float) $row['luas_bangunan']) {
                    $this->error("Luas {$code} berbeda dengan PDF. Import dibatalkan.");
                    return self::FAILURE;
                }
                $prices = array_intersect_key($row, KavlingPricing::FIELDS);
                if ($this->option('dp30-only')) {
                    foreach (['harga_cash', 'harga_bertahap_3', 'harga_bertahap_6', 'harga_bertahap_12'] as $field) {
                        if ((int) $unit->$field <= 0) {
                            $this->error("Harga dasar {$code} belum lengkap. Import dibatalkan.");
                            return self::FAILURE;
                        }
                        $prices[$field] = (int) $unit->$field;
                    }
                    $updates[$unit->id] = KavlingPricing::dp30Prices($prices);
                } else {
                    $updates[$unit->id] = array_merge($prices, KavlingPricing::dp30Prices($prices));
                }
            }
        }
        $this->info(count($updates) . ' unit cocok di ' . $locations->first()->nama_kavling . '.');
        if (!$this->option('apply')) return self::SUCCESS;

        $backup = 'backups/paramita-prices-' . now()->format('Ymd-His') . '-' . uniqid() . '.json';
        Storage::disk('local')->put($backup, $units->flatten()->whereIn('id', array_keys($updates))->toJson(JSON_PRETTY_PRINT));
        DB::transaction(function () use ($updates) {
            foreach ($updates as $id => $values) KavlingPeta::whereKey($id)->update($values);
        });
        $this->info('Harga tersimpan. Backup: ' . Storage::disk('local')->path($backup));
        return self::SUCCESS;
    }
}
