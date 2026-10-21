<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tanda Terima {{ $booking->no_registrasi }}</title>
<style>
    @page { margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; color: #111; }
    .receipt { border: 2px solid #111; padding: 14px 18px; }
    table { border-collapse: collapse; width: 100%; }
    td { padding: 5px 0; vertical-align: top; overflow-wrap: break-word; }
    .header td { vertical-align: middle; }
    .header img { max-width: 180px; max-height: 64px; }
    h1 { text-align: center; font-size: 28px; margin: 0; }
    .reference { text-align: right; font-size: 10px; }
    .label { width: 24%; }
    .value { border-bottom: 1px dotted #777; }
    .spacer td { padding-top: 12px; }
    .check { display: inline-block; border: 1px solid #111; width: 13px; height: 13px; text-align: center; font-size: 10px; line-height: 13px; margin-right: 6px; }
    .choices td { padding: 2px 0; }
    .signatures td { width: 33.33%; padding-top: 18px; padding-right: 25px; }
    .signature-line { border-bottom: 1px solid #111; height: 52px; margin-bottom: 5px; }
    .notes { font-size: 9px; line-height: 1.55; margin-top: 8px; }
</style>
</head>
<body>
@php
    $method = strtolower($booking->metodeBayar?->jenis_bayar ?? '');
    $transfer = str_contains($method, 'transfer');
    $debit = str_contains($method, 'debit');
    $credit = str_contains($method, 'credit') || str_contains($method, 'kredit');
@endphp
<div class="receipt">
    <table class="header"><tr>
        <td style="width: 26%;">@if ($logoPath)<img src="data:{{ mime_content_type($logoPath) }};base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo Perumahan">@else<strong>{{ $booking->lokasi?->nama_kavling }}</strong>@endif</td>
        <td style="width: 48%;"><h1>TANDA TERIMA</h1></td>
        <td style="width: 26%;" class="reference">No. {{ $booking->no_registrasi ?: $booking->id }}<br>{{ $booking->lokasi?->nama_kavling }}</td>
    </tr></table>
    <table>
        <tr><td class="label">TANGGAL</td><td>: {{ $booking->tgl_booking ? \Carbon\Carbon::parse($booking->tgl_booking)->locale('id')->translatedFormat('d F Y') : '........................' }}</td>
            <td>Tipe Unit: {{ $booking->kavling?->luas_bangunan ?? '...' }} / {{ $booking->kavling?->nama_cluster ?: '...' }}</td><td>No Unit: {{ $booking->kavling?->kode_kavling ?: '................' }}</td></tr>
        <tr><td class="label">NAMA PEMBELI</td><td colspan="3" class="value">: {{ $booking->nama_lengkap }}</td></tr>
        <tr><td>NO TELEPON/HP</td><td colspan="3" class="value">: {{ $booking->no_telp }}</td></tr>
        <tr><td>KTP</td><td colspan="3" class="value">: {{ $booking->nik }}</td></tr>
        <tr class="spacer"><td colspan="2">NAMA SALES IN/SALES AGENT: {{ $booking->marketing?->nama_marketing ?: '........................' }}</td>
            <td colspan="2">NAMA AGEN PROPERTY: ....................................</td></tr>
        <tr class="spacer"><td>KETERANGAN</td><td colspan="3">
            <table class="choices"><tr><td style="width: 23%;">: Untuk Pembayaran</td><td style="width: 43%;"><span class="check"></span> Reserve/Lock Unit<br><span class="check"></span> NUP<br><span class="check">X</span> Booking Fee (Non Refundable)</td><td><span class="check"></span> DP<br><span class="check"></span> Installment/Angsuran<br><span class="check"></span> Lainnya ................</td></tr></table>
        </td></tr>
        <tr class="spacer"><td>JUMLAH</td><td>: Rp {{ number_format((int) $booking->booking_fee, 0, ',', '.') }}</td><td colspan="2">Terbilang: {{ $terbilang }}</td></tr>
        <tr class="spacer"><td>PEMBAYARAN</td><td colspan="3">
            <table class="choices">
                <tr><td style="width: 24%;"><span class="check">{{ $debit ? 'X' : '' }}</span> Debit Card</td><td style="width: 36%;">No. ............................</td><td>Atas Nama: ............................</td></tr>
                <tr><td><span class="check">{{ $transfer ? 'X' : '' }}</span> Transfer</td><td>No. ............................</td><td>Atas Nama: ............................</td></tr>
                <tr><td><span class="check">{{ $credit ? 'X' : '' }}</span> Credit Card</td><td>No. ............................</td><td>Atas Nama: ............................</td></tr>
                <tr><td colspan="2">No Rekening Pengirim ....................................</td><td>Atas Nama: ............................</td></tr>
                @if ($method && !$transfer && !$debit && !$credit)<tr><td colspan="3">Metode pembayaran: {{ $booking->metodeBayar->jenis_bayar }}</td></tr>@endif
            </table>
        </td></tr>
    </table>
    <table class="signatures"><tr>
        <td>Pembeli<div class="signature-line"></div>{{ $booking->nama_lengkap }}</td>
        <td>Sales In-House/Sales Agent<div class="signature-line"></div>{{ $booking->marketing?->nama_marketing }}</td>
        <td>Keuangan<div class="signature-line"></div></td>
    </tr></table>
    <div class="notes">
        <strong>- Kami tidak bertanggung jawab jika transaksi di luar REKENING MANDIRI 124 00 164 9312 a.n PT APMA SUKSES GEMILANG</strong><br>
        - Maksimum 2 (dua) hari setelah pelunasan booking fee, konsumen wajib menentukan cara pembayaran.<br>
        - Refund hanya bisa dilakukan ke nomor rekening pengirim, dengan batas klaim 14 hari dari tanggal Reserve / Lock Unit.
    </div>
</div>
</body>
</html>
