<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerProcessExport
{
    public function workbook(): Spreadsheet
    {
        $fields = Schema::getColumnListing('customer');
        $preferred = ['kode_customer', 'nama_lengkap', 'nik', 'no_telp', 'email', 'alamat_ktp', 'alamat_domisili'];
        $fields = array_values(array_unique(array_merge(array_intersect($preferred, $fields), $fields)));
        $query = DB::table('customer')->leftJoin('lokasi_kavling', 'lokasi_kavling.id', '=', 'customer.id_lokasi')
            ->leftJoin('kavling_peta', 'kavling_peta.id', '=', 'customer.id_kavling')
            ->select('customer.*', 'lokasi_kavling.nama_kavling as unit_lokasi', 'kavling_peta.kode_kavling as unit_kavling');
        $hold = DB::table('pengajuan_hold')->selectRaw('MAX(tgl_booking)')
            ->whereColumn('pengajuan_hold.id_lokasi', 'customer.id_lokasi')
            ->whereColumn('pengajuan_hold.id_kavling', 'customer.id_kavling')
            ->where('pengajuan_hold.stt_reg', 2);
        if (Schema::hasColumn('customer', 'nik') && Schema::hasColumn('pengajuan_hold', 'nik')) {
            $hold->whereColumn('pengajuan_hold.nik', 'customer.nik')->whereNotNull('customer.nik')->where('customer.nik', '!=', '');
        } else {
            $hold->whereColumn('pengajuan_hold.nama_lengkap', 'customer.nama_lengkap');
        }
        $query->selectSub($hold, 'stage_hold')
            ->addSelect('customer.tanggal_verif as stage_booking_fee')
            ->selectSub(DB::table('wawancara')->selectRaw('MAX(tgl_wawancara)')->whereColumn('wawancara.id_customer', 'customer.id'), 'stage_proses_bank')
            ->selectSub(DB::table('wawancara_sp3k')->join('wawancara', 'wawancara.id', '=', 'wawancara_sp3k.id_wawancara')
                ->selectRaw('MAX(tgl_terbit_sp3k)')->whereColumn('wawancara.id_customer', 'customer.id'), 'stage_sp3k')
            ->selectSub(DB::table('detail_akad')->join('akad', 'akad.id', '=', 'detail_akad.id_akad')
                ->selectRaw('MAX(akad.tgl_akad)')->where('detail_akad.status', 2)->whereColumn('detail_akad.id_customer', 'customer.id'), 'stage_akad')
            ->orderBy('unit_lokasi')->orderBy('unit_kavling')->orderBy('customer.id');

        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet()->setTitle('Customer dan Proses');
        $labels = ['kode_customer' => 'Kode Customer', 'nama_lengkap' => 'Nama Customer', 'nik' => 'NIK', 'no_telp' => 'Telepon'];
        $stages = ['hold' => ['Hold', '2563EB'], 'booking_fee' => ['Booking Fee', '15803D'], 'proses_bank' => ['Proses Bank', 'B45309'], 'sp3k' => ['SP3K', '7E22CE'], 'akad' => ['Akad', 'BE123C']];
        $headers = array_merge(['No', 'Lokasi', 'Kavling'], array_map(fn ($field) => $labels[$field] ?? ucwords(str_replace('_', ' ', $field)), $fields), array_column($stages, 0));
        $sheet->fromArray($headers, null, 'A1');
        $last = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:'.$last.'1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['wrapText' => true, 'vertical' => 'center'],
        ]);
        foreach (array_values($stages) as $index => $stage) {
            $column = Coordinate::stringFromColumnIndex(4 + count($fields) + $index);
            $sheet->getStyle($column.'1')->getFill()->getStartColor()->setRGB($stage[1]);
        }
        $line = 2;
        foreach ($query->cursor() as $customer) {
            $values = [$line - 1, $customer->unit_lokasi, $customer->unit_kavling];
            foreach ($fields as $field) $values[] = $customer->$field;
            foreach ($stages as $key => $stage) {
                $date = $customer->{'stage_'.$key};
                $values[] = $date ? Carbon::parse($date)->format('d-m-Y') : '-';
            }
            foreach ($values as $index => $value) {
                $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($index + 1).$line, (string) ($value === null || $value === '' ? '-' : $value), DataType::TYPE_STRING);
            }
            $line++;
        }
        $sheet->freezePane('D2');
        $sheet->setAutoFilter('A1:'.$last.max(1, $line - 1));
        $sheet->getRowDimension(1)->setRowHeight(32);
        for ($index = 1; $index <= count($headers); $index++) $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);

        return $book;
    }
}
