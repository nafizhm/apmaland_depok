<?php

namespace App\Services;

use App\Models\Piutang;
use Illuminate\Support\Collection;

class PiutangUnitService
{
    public function groups(): Collection
    {
        return Piutang::with(['customer.lokasi', 'customer.kavling'])
            ->orderByDesc('tanggal_piutang')->orderBy('id')->get()
            ->groupBy(function ($bill) {
                $customer = $bill->customer;
                if ($customer?->id_lokasi && $customer?->id_kavling) {
                    return 'unit:'.$customer->id_lokasi.':'.$customer->id_kavling;
                }

                return $bill->id_customer ? 'customer:'.$bill->id_customer : 'bill:'.$bill->id;
            });
    }

    public function summary(Collection $bills): Piutang
    {
        $row = clone $bills->first();
        if (!$row->id_customer) {
            return $row->unsetRelations();
        }

        $customer = $row->customer;
        $identity = array_filter([
            $customer?->lokasi?->nama_kavling,
            $customer?->kavling?->kode_kavling,
            $bills->pluck('customer.nama_lengkap')->filter()->unique()->implode(', '),
        ]);
        $remaining = $bills->sum('sisa_bayar');
        $row->forceFill([
            'nominal' => $bills->sum('nominal'),
            'terbayar' => $bills->sum('terbayar'),
            'sisa_bayar' => $remaining,
            'status' => $remaining > 0 ? 1 : 2,
            'tgl_pelunasan' => $remaining > 0 ? null : $bills->max('tgl_pelunasan'),
            'deskripsi' => $identity ? implode(' / ', $identity) : $row->deskripsi,
        ])->syncOriginal();

        return $row->unsetRelations();
    }

    public function rows(?string $date = null): Collection
    {
        return $this->groups()->filter(function ($bills) use ($date) {
            return !$date || $bills->contains(fn ($bill) => substr((string) $bill->tanggal_piutang, 0, 10) === $date);
        })->map(fn ($bills) => $this->summary($bills))->values();
    }

    public function detail($id): array
    {
        Piutang::findOrFail($id);
        $bills = $this->groups()->first(fn ($group) => $group->contains('id', $id));
        $payments = [];
        foreach ($bills as $bill) {
            foreach (app(PiutangPaymentService::class)->syncPiutang($bill->id) as $payment) {
                $key = $payment['id'];
                if (isset($payments[$key])) {
                    $payments[$key]['nominal'] += $payment['nominal'];
                } else {
                    $payments[$key] = $payment;
                }
            }
            $bill->refresh();
        }
        $payments = collect($payments)->sortBy(fn ($payment) => [$payment['tanggal'], $payment['id']])->values()->all();

        $components = $bills->map(fn ($bill) => [
            'id' => $bill->id,
            'tanggal_piutang' => $bill->tanggal_piutang,
            'deskripsi' => $bill->deskripsi,
            'nominal' => (int) $bill->nominal,
            'terbayar' => (int) $bill->terbayar,
            'sisa_bayar' => (int) $bill->sisa_bayar,
            'status' => (int) $bill->status,
        ])->values()->all();

        return [$this->summary($bills), $payments, $components];
    }
}
