@extends('layouts.app')

@php
    $konfigurasi = \App\Models\PengaturanProfil::first();
    $logo = \App\Models\PengaturanMedia::where('jenis_data', 'Logo Aplikasi')->where('stt_aktif', 1)->first()
        ?? \App\Models\PengaturanMedia::where('jenis_data', 'Logo Login')->first();
    $company = $konfigurasi?->nama_perusahaan ?: 'Apmaland Property';
    $hasBooking = session()->has('nama');
@endphp

@section('title', 'Booking Berhasil | ' . $company)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { background: #f3f6ef !important; font-family: 'Inter', sans-serif; color: #173c35; }
    .booking-success { min-height: 100vh; padding: 34px 24px 22px; background: radial-gradient(ellipse at 4% 0%, #e3edcb 0%, transparent 45%), radial-gradient(ellipse at 100% 100%, #dcebe2 0%, transparent 45%); }
    .success-wrap { max-width: 1050px; margin: auto; }
    .success-brand { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 34px; }
    .success-brand img { max-width: 205px; max-height: 62px; object-fit: contain; }
    .success-brand-name { font-size: 23px; font-weight: 800; color: #0b3d3e; }
    .success-brand-note { font-size: 12px; color: #738078; display: flex; gap: 8px; align-items: center; }
    .success-brand-note span { width: 7px; height: 7px; border-radius: 50%; background: #6e963c; }
    .success-layout { display: grid; grid-template-columns: 1.35fr 1fr; background: white; border-radius: 26px; overflow: hidden; box-shadow: 0 20px 65px #153c3510; border: 1px solid #e1e8db; }
    .success-main { padding: 42px; }
    .success-status { display: inline-flex; gap: 8px; align-items: center; background: #eef5e6; color: #456b2b; border: 1px solid #dceacb; font-size: 11px; font-weight: 600; padding: 7px 12px; border-radius: 30px; margin-bottom: 25px; }
    .success-symbol { height: 64px; width: 64px; background: #eef5e6; border: 1px solid #dce9cf; border-radius: 22px; display: grid; place-items: center; margin-bottom: 22px; }
    .success-symbol svg { width: 32px; height: 32px; }
    .success-main h1 { font-size: clamp(27px, 3.4vw, 37px); line-height: 1.18; letter-spacing: -1.4px; font-weight: 800; margin: 0 0 14px; color: #0b3d3e; }
    .success-intro { font-size: 13px; color: #738078; line-height: 1.8; margin-bottom: 28px; max-width: 390px; }
    .success-summary { border: 1px solid #e5ebe1; border-radius: 15px; padding: 0 18px; margin: 0 0 25px; }
    .success-summary-row { display: flex; align-items: center; gap: 13px; padding: 16px 0; }
    .success-summary-row + .success-summary-row { border-top: 1px solid #edf0e9; }
    .success-detail-icon { flex: 0 0 35px; height: 35px; border-radius: 11px; display: grid; place-items: center; background: #f3f6ef; color: #63823f; }
    .success-summary dt { font-size: 10px; font-weight: 500; color: #849086; margin-bottom: 4px; }
    .success-summary dd { font-size: 13px; font-weight: 600; color: #24463c; margin: 0; overflow-wrap: anywhere; }
    .success-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .success-action { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 45px; padding: 12px; border-radius: 10px; font-size: 11px; font-weight: 600; text-decoration: none; transition: background .2s, transform .2s; }
    .success-action-primary { background: #0b3d3e; color: white; border: 1px solid #0b3d3e; }
    .success-action-primary:hover { background: #315f31; color: white; transform: translateY(-2px); }
    .success-action-secondary { background: white; color: #315f31; border: 1px solid #dce5d7; }
    .success-action-secondary:hover { background: #f1f6eb; color: #0b3d3e; }
    .success-action:focus-visible { outline: 3px solid #9bc400; outline-offset: 3px; }
    .success-side { padding: 40px 32px; background: #0b3d3e; color: white; position: relative; overflow: hidden; display: flex; flex-direction: column; }
    .success-side::before { content: ''; position: absolute; height: 280px; width: 280px; border: 1px solid #ffffff0d; border-radius: 50%; right: -150px; top: -120px; box-shadow: 0 0 0 35px #ffffff03, 0 0 0 70px #ffffff03; pointer-events: none; }
    .success-eyebrow { font-size: 9px; color: #b9d28e; letter-spacing: 2px; font-weight: 600; margin-bottom: 16px; }
    .success-side h2 { font-size: 22px; line-height: 1.45; font-weight: 600; letter-spacing: -.5px; margin-bottom: 25px; }
    .success-steps { list-style: none; padding: 0; margin: 0; }
    .success-step { display: flex; gap: 13px; position: relative; padding-bottom: 26px; }
    .success-step:not(:last-child)::after { content: ''; position: absolute; left: 14px; top: 33px; bottom: 4px; width: 1px; background: #ffffff20; }
    .success-step-number { width: 29px; height: 29px; flex: 0 0 29px; display: grid; place-items: center; border: 1px solid #ffffff30; border-radius: 50%; font-size: 10px; color: #d1e1d5; }
    .success-step:first-child .success-step-number { background: #c0d875; border-color: #c0d875; color: #173c35; }
    .success-step h3 { font-size: 12px; font-weight: 600; margin: 3px 0 7px; color: #fff; }
    .success-step p { font-size: 11px; color: #b4c9c1; line-height: 1.8; margin: 0; }
    .success-note { background: #ffffff08; border: 1px solid #ffffff14; border-radius: 12px; padding: 15px; margin-top: auto; display: flex; gap: 10px; align-items: flex-start; }
    .success-note i { color: #c0d875; margin-top: 3px; font-size: 13px; }
    .success-note p { font-size: 10px; color: #c2d3cb; line-height: 1.8; margin: 0; }
    .success-footer { text-align: center; font-size: 10px; color: #859083; margin-top: 25px; line-height: 1.8; }
    @media (max-width: 767px) {
        .booking-success { padding: 22px 16px; }
        .success-brand { margin-bottom: 23px; }
        .success-brand img { max-width: 170px; max-height: 50px; }
        .success-brand-note { display: none; }
        .success-layout { grid-template-columns: 1fr; border-radius: 20px; }
        .success-main { padding: 28px 24px; }
        .success-side { padding: 28px 24px; }
        .success-side h2 { font-size: 21px; }
        .success-intro { max-width: none; }
        .success-note { margin-top: 0; }
    }
    @media (max-width: 360px) { .success-actions { grid-template-columns: 1fr; } }
    @media print {
        .booking-success { background: white; padding: 0; }
        .success-layout { box-shadow: none; }
        .success-actions, .success-brand-note { display: none; }
    }
</style>
@endpush

@section('content')
<main class="booking-success">
    <div class="success-wrap">
        <header class="success-brand">
            @if ($logo?->nama_file)
                <img src="{{ asset('config_media/' . $logo->nama_file) }}" alt="{{ $company }}">
            @else
                <span class="success-brand-name">{{ $company }}</span>
            @endif
            <span class="success-brand-note"><span aria-hidden="true"></span> Langkah awal menuju rumah impian</span>
        </header>
        <div class="success-layout">
            <section class="success-main" aria-labelledby="success-title">
                <div class="success-status"><i class="fas {{ $hasBooking ? 'fa-check-circle' : 'fa-home' }}" aria-hidden="true"></i> {{ $hasBooking ? 'Permohonan terkirim' : 'Booking rumah' }}</div>
                <div class="success-symbol" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M7 16.5l6 6L25 10" stroke="#507b30" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h1 id="success-title">{{ $hasBooking ? 'Booking berhasil!' : 'Rumah impian dimulai di sini.' }}</h1>
                <p class="success-intro">{{ $hasBooking ? 'Terima kasih atas kepercayaan Anda. Permohonan booking Anda sudah kami terima dan akan ditindaklanjuti oleh tim kami.' : 'Pilih rumah yang Anda inginkan dan kirim permohonan melalui formulir booking. Ringkasan permohonan akan ditampilkan di halaman ini.' }}</p>
                @if ($hasBooking)
                <dl class="success-summary">
                    @foreach ([['icon' => 'fa-user', 'label' => 'Nama pemesan', 'value' => session('nama')], ['icon' => 'fa-map-marker-alt', 'label' => 'Lokasi perumahan', 'value' => session('lokasi')], ['icon' => 'fa-home', 'label' => 'Blok / unit rumah', 'value' => session('blok')]] as $detail)
                    <div class="success-summary-row">
                        <span class="success-detail-icon" aria-hidden="true"><i class="fas {{ $detail['icon'] }}"></i></span>
                        <div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] ?: 'Belum tersedia' }}</dd></div>
                    </div>
                    @endforeach
                </dl>
                @endif
                <div class="success-actions">
                    <a href="{{ route('public.siteplan.index') }}" class="success-action success-action-primary">Lihat siteplan <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <a href="{{ route('booking') }}" class="success-action success-action-secondary">{{ $hasBooking ? 'Kembali ke booking' : 'Isi formulir booking' }}</a>
                </div>
            </section>
            <aside class="success-side" aria-labelledby="next-title">
                <p class="success-eyebrow">LANGKAH SELANJUTNYA</p>
                <h2 id="next-title">Selangkah lebih dekat<br>dengan rumah Anda.</h2>
                <ol class="success-steps">
                    <li class="success-step"><span class="success-step-number">01</span><div><h3>Verifikasi permohonan</h3><p>Tim kami memeriksa data pemesan dan ketersediaan unit yang dipilih.</p></div></li>
                    <li class="success-step"><span class="success-step-number">02</span><div><h3>Konfirmasi dari tim kami</h3><p>Pastikan nomor telepon Anda aktif agar tim kami dapat menghubungi Anda.</p></div></li>
                    <li class="success-step"><span class="success-step-number">03</span><div><h3>Lanjutkan proses pemesanan</h3><p>Tim kami akan membantu menjelaskan dokumen dan tahapan pemesanan berikutnya.</p></div></li>
                </ol>
                <div class="success-note"><i class="fas fa-info-circle" aria-hidden="true"></i><p>Konfirmasi ketersediaan dan pemesanan unit akan disampaikan oleh tim kami setelah proses verifikasi.</p></div>
            </aside>
        </div>
        <footer class="success-footer">Terima kasih telah memilih {{ $company }}.<br>Temukan tempat terbaik untuk cerita baru Anda.</footer>
    </div>
</main>
@endsection

@push('scripts')
<script>sessionStorage.removeItem('success');</script>
@endpush
