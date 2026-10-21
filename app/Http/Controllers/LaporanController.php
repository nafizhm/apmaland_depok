<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use App\Models\Menu;
use App\Services\MonthlyTransactionReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    private function filters(Request $request): array
    {
        $menuId = Menu::where('route_name', 'laporan.index')->value('id');
        abort_unless($menuId && HakAkses::where('id_user', auth()->id())->where('id_menu', $menuId)->where('lihat', 1)->exists(), 403);
        $validated = $request->validate([
            'tahun' => 'sometimes|required|integer|min:1900|max:2100',
            'jenis_transaksi' => ['sometimes', 'required', Rule::in(array_keys(MonthlyTransactionReport::TYPES))],
        ]);
        return [(int) ($validated['tahun'] ?? now('Asia/Jakarta')->year), $validated['jenis_transaksi'] ?? 'hold'];
    }

    public function index(Request $request, MonthlyTransactionReport $report)
    {
        [$tahun, $jenisTransaksi] = $this->filters($request);
        $jenisList = MonthlyTransactionReport::TYPES;
        $rows = $report->counts($tahun, $jenisTransaksi);
        $total = array_sum(array_column($rows, 'jumlah'));
        return view('admin.laporan.index', compact('tahun', 'jenisTransaksi', 'jenisList', 'rows', 'total'));
    }

    public function detail(Request $request, MonthlyTransactionReport $report)
    {
        [$tahun, $jenis] = $this->filters($request);
        $validated = $request->validate(['bulan' => 'required|integer|min:1|max:12', 'page' => 'sometimes|integer|min:1']);
        return response()->json($report->details($tahun, $jenis, (int) $validated['bulan'])->paginate(25));
    }

    public function excel(Request $request, MonthlyTransactionReport $report)
    {
        [$tahun, $jenis] = $this->filters($request);
        if ($jenis === 'semua') {
            $book = app(\App\Services\CustomerProcessExport::class)->workbook();
            return response()->streamDownload(function () use ($book) {
                (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
                $book->disconnectWorksheets();
            }, "laporan-semua-{$tahun}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $summary = $book->getActiveSheet()->setTitle('Rekap Bulanan');
        $summary->fromArray([['Laporan ' . MonthlyTransactionReport::TYPES[$jenis], $tahun], ['Bulan', 'Jumlah Data']], null, 'A1');
        $rows = $report->counts($tahun, $jenis);
        foreach ($rows as $index => $row) $summary->fromArray([$row['bulan'], $row['jumlah']], null, 'A' . ($index + 3));
        $summary->fromArray(['Total', array_sum(array_column($rows, 'jumlah'))], null, 'A15');
        $types = $jenis === 'semua' ? array_diff(array_keys(MonthlyTransactionReport::TYPES), ['semua']) : [$jenis];
        $colors = ['hold' => '2563EB', 'booking_fee' => '15803D', 'proses_bank' => 'B45309', 'sp3k' => '7E22CE', 'akad' => 'BE123C'];
        $headerStyle = fn ($color) => [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ];
        if ($jenis === 'semua') {
            $summary->setCellValue('B1', $tahun);
            $summary->fromArray(array_merge(['Bulan'], array_map(fn ($type) => MonthlyTransactionReport::TYPES[$type], array_values($types)), ['Total']), null, 'A2');
            $counts = [];
            foreach ($types as $type) $counts[$type] = $report->counts($tahun, $type);
            foreach ($rows as $index => $row) {
                $values = array_map(fn ($type) => $counts[$type][$index]['jumlah'], array_values($types));
                $summary->fromArray(array_merge([$row['bulan']], $values, [array_sum($values)]), null, 'A'.($index + 3), true);
            }
            $totals = array_map(fn ($type) => array_sum(array_column($counts[$type], 'jumlah')), array_values($types));
            $summary->fromArray(array_merge(['Total'], $totals, [array_sum($totals)]), null, 'A15', true);
            $summary->getStyle('A2:G2')->applyFromArray($headerStyle('334155'));
            foreach (array_values($types) as $index => $type) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 2);
                $summary->getStyle($column.'2')->applyFromArray($headerStyle($colors[$type]));
            }
            $summary->getStyle('A15:G15')->getFont()->setBold(true);
            $summary->setAutoFilter('A2:G14');
            $summary->getRowDimension(2)->setRowHeight(30);
        } else {
            $summary->getStyle('A2:B2')->applyFromArray($headerStyle($colors[$jenis]));
        }
        foreach ($types as $type) {
        $sheet = $book->createSheet()->setTitle($jenis === 'semua' ? MonthlyTransactionReport::TYPES[$type] : 'Detail Transaksi');
        $query = $report->exportDetails($tahun, $type);
        $keys = ['tanggal', 'jenis', 'kode', 'nama', 'telepon', 'lokasi', 'kavling'];
        foreach ($query->columns as $selection) {
            if (is_string($selection) && str_contains($selection, '__')) $keys[] = substr($selection, strpos($selection, ' as ') + 4);
        }
        $labels = ['tanggal' => 'Tanggal', 'jenis' => 'Jenis Transaksi', 'kode' => 'Kode', 'nama' => 'Nama Customer', 'telepon' => 'Telepon', 'lokasi' => 'Lokasi', 'kavling' => 'Kavling'];
        $sheet->fromArray(array_merge(['No'], array_map(fn ($key) => $labels[$key] ?? ucwords(str_replace(['__', '_'], [' / ', ' '], $key)), $keys)), null, 'A1');
        $line = 2;
        foreach ($query->cursor() as $row) {
            $sheet->setCellValue('A' . $line, $line - 1);
            $values = array_map(fn ($key) => $key === 'tanggal' && $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : $row->$key, $keys);
            foreach ($values as $index => $value) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 2);
                $sheet->setCellValueExplicit($column . $line, (string) ($value ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $line++;
        }
        $sheet->setAutoFilter('A1:'.$sheet->getHighestColumn().max($line - 1, 1));
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->applyFromArray($headerStyle($colors[$type]));
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getTabColor()->setRGB($colors[$type]);
        }
        foreach ($book->getAllSheets() as $tab) {
            $tab->freezePane($tab === $summary ? 'B3' : 'A2');
            for ($column = 1; $column <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($tab->getHighestColumn()); $column++) {
                $tab->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
            }
            $tab->getStyle('A1:' . $tab->getHighestColumn() . '1')->getFont()->setBold(true);
        }
        return response()->streamDownload(function () use ($book) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, "laporan-{$jenis}-{$tahun}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
