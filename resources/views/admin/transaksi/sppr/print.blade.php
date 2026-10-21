<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SPR {{ $spr->no_sppr }}</title>
    <style>
        @page { margin: 12mm 12mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #000; line-height: 1.16; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        td, th { border: .6px solid #000; padding: 1px 4px; vertical-align: middle; word-wrap: break-word; }
        .header td { border: 0; text-align: center; }
        .header h2 { font-size: 16px; margin: 0 0 4px; }
        .header img { max-width: 125px; max-height: 45px; }
        h1 { font-size: 20px; border: 1px solid black; text-align: center; margin: 6px 0 3px; padding: 3px; }
        .reference { text-align: right; margin: 0 0 6px; font-size: 9px; }
        .section { background: #e3e0e9; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .schedule td { height: 11px; }
        .small { font-size: 7px; }
    </style>
</head>
<body>
@php
    $money = fn ($amount) => 'Rp ' . number_format((int) $amount, 0, ',', '.');
    $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d-M-Y') : '';
    $kpr = ($data['jenis_pembelian'] ?? 'KPR') === 'KPR';
    $schedule = array_values($data['angsuran'] ?? []);
    $project = $data['nama_perumahan'] ?? $spr->customer?->lokasi?->nama_kavling ?? '';
    $projectLogo = public_path('assets/images/logo-paramita.jpg');
    $companyLogo = public_path('assets/images/logo-apmaland-panjang.jpg');
    $embedLogo = fn ($path) => 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
@endphp
<table class="header">
    <tr>
        <td style="width: 23%;">@if (is_file($projectLogo))<img src="{{ $embedLogo($projectLogo) }}" alt="Pramita Residence">@else{{ $project }}@endif</td>
        <td style="width: 54%;">
            <h2>{{ mb_strtoupper($project) }}</h2>
            Alamat: {{ $data['alamat_perumahan'] ?? $spr->customer?->lokasi?->alamat ?? '' }}<br>
            HP: {{ $data['telepon_perumahan'] ?? '' }}
        </td>
        <td style="width: 23%;">@if (is_file($companyLogo))<img src="{{ $embedLogo($companyLogo) }}" alt="Apmaland Property">@endif</td>
    </tr>
</table>
<h1>SURAT PEMESANAN RUMAH</h1>
<p class="reference">No. SPR: {{ $spr->no_sppr }}</p>
<table>
    <colgroup><col style="width: 29%;"><col style="width: 41%;"><col style="width: 30%;"></colgroup>
    <tr><td colspan="3" class="section">A. IDENTITAS KONSUMEN</td></tr>
    <tr><td>Nama</td><td colspan="2">{{ $spr->nama }}</td></tr>
    <tr><td>Alamat Rumah</td><td colspan="2">{{ $spr->alamat }}</td></tr>
    <tr><td>Alamat Surat Menyurat</td><td colspan="2">{{ $data['alamat_surat'] ?? '' }}</td></tr>
    <tr><td>Telepon: {{ $data['telepon_rumah'] ?? '' }}</td><td>HP: {{ $spr->no_telp }}</td><td>Email: {{ $data['email'] ?? '' }}</td></tr>
    <tr><td colspan="3" class="section">B. IDENTITAS RUMAH</td></tr>
    <tr><td>ID Rumah: {{ $data['id_rumah'] ?? $spr->customer?->kavling?->id_rumah_sikumbang ?? '' }}</td>
        <td colspan="2">Tipe: {{ $data['tipe_rumah'] ?? $spr->customer?->kavling?->tipe_bangunan ?? '' }} &nbsp; Blok: {{ $unit['blok'] }} &nbsp; No: {{ $unit['no'] }} &nbsp; LT: {{ $unit['luas_tanah'] }} m² &nbsp; LB: {{ $unit['luas_bangunan'] }} m²</td></tr>
    <tr><td>Kelebihan Tanah (KLT)</td><td colspan="2">{{ $data['kelebihan_tanah'] ?? 0 }} m²</td></tr>
    <tr><td>Harga Jual Rumah</td><td colspan="2">{{ $money($spr->harga_jual) }}</td></tr>
    <tr><td>Harga Kelebihan Tanah</td><td colspan="2">{{ $money($data['harga_kelebihan_tanah'] ?? 0) }}</td></tr>
    <tr><td colspan="3" class="section">C. PERHITUNGAN KEWAJIBAN KONSUMEN</td></tr>
    <tr><td colspan="2">Harga Rumah Standard</td><td class="right">{{ $money($spr->harga_jual) }}</td></tr>
    <tr><td colspan="2">Besaran Discount</td><td class="right">{{ $money($data['diskon'] ?? 0) }}</td></tr>
    <tr><td colspan="2">Harga Rumah Setelah Discount</td><td class="right">{{ $money($totals['harga_setelah_diskon']) }}</td></tr>
    <tr><td colspan="2">Kelebihan Tanah: {{ $data['kelebihan_tanah'] ?? 0 }} m²</td><td class="right">{{ $money($data['harga_kelebihan_tanah'] ?? 0) }}</td></tr>
    <tr class="bold"><td colspan="2" class="right">Total Harga Rumah dan Tanah</td><td class="right">{{ $money($totals['total_harga']) }}</td></tr>
    <tr><td colspan="3" class="section">D. PEMBELIAN SECARA KPR</td></tr>
    <tr><td colspan="2">Nilai KPR Maksimal</td><td class="right">{{ $kpr ? $money($spr->asumsi_plafon_kpr) : '' }}</td></tr>
    <tr><td colspan="2">Uang Muka / DP</td><td class="right">{{ $kpr ? $money($spr->nominal_dp) : '' }}</td></tr>
    <tr><td colspan="2">Kelebihan Tanah (KLT)</td><td class="right">{{ $kpr ? $money($data['harga_kelebihan_tanah'] ?? 0) : '' }}</td></tr>
    <tr><td rowspan="2">Biaya Surat dan Administrasi:</td><td>1 &nbsp; Biaya Sertifikat</td><td class="right">{{ $kpr ? $money($data['biaya_sertifikat'] ?? 0) : '' }}</td></tr>
    <tr><td>2 &nbsp; BPHTB</td><td class="right">{{ $kpr ? $money($data['biaya_bphtb'] ?? 0) : '' }}</td></tr>
    <tr class="bold"><td colspan="2" class="right">Total Biaya Administrasi</td><td class="right">{{ $kpr ? $money($totals['total_administrasi']) : '' }}</td></tr>
    <tr class="bold"><td colspan="2" class="right">Jumlah Kewajiban UM Konsumen Kepada Pihak Developer</td><td class="right">{{ $kpr ? $money($totals['kewajiban_kpr']) : '' }}</td></tr>
    <tr><td colspan="3" class="section">E. PEMBELIAN SECARA TUNAI</td></tr>
    <tr><td colspan="2">Total Harga Rumah</td><td class="right">{{ !$kpr ? $money($totals['harga_setelah_diskon']) : '' }}</td></tr>
    <tr><td colspan="2">Uang Muka / DP</td><td class="right">{{ !$kpr ? $money($spr->nominal_dp) : '' }}</td></tr>
    <tr><td colspan="2">Kelebihan Tanah (KLT)</td><td class="right">{{ !$kpr ? $money($data['harga_kelebihan_tanah'] ?? 0) : '' }}</td></tr>
    <tr><td rowspan="2">Biaya Surat dan Administrasi:</td><td>1 &nbsp; Biaya Sertifikat</td><td class="right">{{ !$kpr ? $money($data['biaya_sertifikat'] ?? 0) : '' }}</td></tr>
    <tr><td>2 &nbsp; BPHTB</td><td class="right">{{ !$kpr ? $money($data['biaya_bphtb'] ?? 0) : '' }}</td></tr>
    <tr class="bold"><td colspan="2" class="right">Total Biaya Administrasi</td><td class="right">{{ !$kpr ? $money($totals['total_administrasi']) : '' }}</td></tr>
    <tr class="bold"><td colspan="2" class="right">Jumlah Kewajiban Konsumen Kepada Developer</td><td class="right">{{ !$kpr ? $money($totals['kewajiban_tunai']) : '' }}</td></tr>
    <tr><td colspan="3" class="section">F. JADWAL PEMBAYARAN</td></tr>
    <tr><td>Tanggal Tanda Jadi</td><td class="center">{{ $date($data['tanggal_booking'] ?? null) }}</td><td class="right">{{ $money($data['nominal_booking'] ?? 0) }}</td></tr>
    <tr><td>Pembayaran Angsuran Pertama</td><td class="center">{{ $date($data['tanggal_angsuran_pertama'] ?? null) }}</td><td class="right">{{ $money($data['nominal_angsuran_pertama'] ?? 0) }}</td></tr>
    <tr><td colspan="2">Jumlah Angsuran Selanjutnya</td><td class="right">{{ count($schedule) }} kali</td></tr>
</table>
<table class="schedule">
    @for ($row = 0; $row < 10; $row++)
        <tr>
        @foreach ([$row, $row + 10] as $index)
            <td style="width: 4%;" class="center">{{ $index + 1 }}</td>
            <td style="width: 28%;" class="right">{{ isset($schedule[$index]) ? $money($schedule[$index]['nominal']) : '' }}</td>
            <td style="width: 18%;" class="center">{{ $date($schedule[$index]['tanggal'] ?? null) }}</td>
        @endforeach
        </tr>
    @endfor
    <tr class="bold"><td colspan="3" class="right">Jumlah</td><td colspan="3">{{ $money($totals['jumlah_jadwal']) }}</td></tr>
</table>
<table>
    <tr><td colspan="4" class="section">G. PAKET HARGA PROMOSI</td></tr>
    <tr><td style="width: 29%;">Harga Jual Pada Saat<br>Transaksi Sudah Termasuk</td>
        @foreach ([1, 2, 3] as $number)<td style="width: 23.66%;">{{ $number }} &nbsp; {{ $data['promo_termasuk_' . $number] ?? '' }}</td>@endforeach</tr>
    <tr><td>Harga Jual Pada Saat<br>Transaksi Belum Termasuk</td>
        @foreach ([1, 2, 3] as $number)<td>{{ $number }} &nbsp; {{ $data['promo_belum_' . $number] ?? '' }}</td>@endforeach</tr>
</table>
</body>
</html>
