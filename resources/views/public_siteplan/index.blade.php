@extends('layouts.app')

@section('title', 'Siteplan Penjualan - Apmaland')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/apmaland-theme.css') }}?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background: url('{{ $bg ? asset("config_media/" . $bg->nama_file) : "" }}') no-repeat center center fixed !important;
            background-size: cover !important;
            background-color: #f4f7f2 !important;
            min-height: 100vh;
        }

        .siteplan-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 15px 50px;
        }

        .siteplan-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.97);
        }

        .siteplan-header {
            background: linear-gradient(135deg, #0b3d3e 0%, #315f31 50%, #638b26 100%);
            padding: 24px 30px;
            border-bottom: 3px solid #9bc400;
            text-align: center;
        }

        .siteplan-header h4 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .siteplan-header p {
            color: rgba(255, 255, 255, 0.8);
            margin: 8px 0 0;
            font-size: 0.85rem;
        }

        .siteplan-body {
            padding: 20px 30px 30px;
        }

        .siteplan-location-selector {
            max-width: 360px;
        }

        .siteplan-location-selector label {
            color: #0b3d3e;
            font-weight: 600;
        }


        /* SVG Container */
        .svg-card-wrapper {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .svg-view-container {
            width: 100%;
            height: 65vh;
            min-height: 500px;
            overflow: hidden;
            position: relative;
            background: #f8fafc;
        }

        .svg-view-container svg {
            width: 100%;
            height: 100%;
            cursor: grab;
            transition: transform 0.1s ease-out;
            touch-action: none;
            user-select: none;
            -webkit-user-drag: none;
        }

        /* Floating Components */
        .legend-box {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 210px;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
        }

        .legend-box.hidden {
            transform: translateX(250px);
        }

        .legend-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: #315f31;
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 999;
            border: none;
        }

        .legend-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: #111827;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .legend-label {
            font-size: 0.75rem;
            color: #4b5563;
            font-weight: 500;
        }

        /* Popup Box */
        #popupOverlay {
            position: fixed;
            z-index: 9999;
            display: none;
            pointer-events: none;
        }

        #popupBox {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 300px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            position: relative;
            pointer-events: all;
            border: 1px solid #e5e7eb;
        }

        #popupClose {
            position: absolute;
            top: 10px;
            right: 12px;
            background: #f3f4f6;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }

        .popup-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1a5c30;
            margin-bottom: 15px;
        }

        .popup-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .popup-label {
            color: #6b7280;
        }

        .popup-value {
            font-weight: 600;
            color: #111827;
            text-align: right;
        }

        .popup-price {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-price .value {
            font-size: 1rem;
            font-weight: 800;
            color: #16a34a;
        }

        .btn-reset {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
            background: rgba(255,255,255,0.9);
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }

        .btn-reset:hover {
            background: #fff;
            border-color: #315f31;
            color: #315f31;
        }

        .text-white-svg text {
            fill: #ffffff !important;
        }

        @media (max-width: 768px) {
            .siteplan-container {
                padding: 15px 10px;
            }
            .siteplan-header {
                padding: 20px;
            }
            .siteplan-body {
                padding: 15px;
            }
            .svg-view-container {
                height: 50vh;
            }
            .legend-box {
                bottom: 80px;
                right: 20px;
            }
            .legend-toggle {
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="siteplan-container">
        <div class="siteplan-card">
            {{-- Header (Matching Booking) --}}
            <div class="siteplan-header">
                <h4>Siteplan Penjualan</h4>
                <p>Silakan pilih lokasi perumahan dan klik pada kavling untuk melihat detail informasi.</p>
            </div>

            <div class="siteplan-body">
                <form method="GET" action="{{ route('public.siteplan.index') }}" class="siteplan-location-selector">
                    <label for="siteplan-location">Lokasi Siteplan</label>
                    <select id="siteplan-location" name="id_lokasi" class="form-control" aria-controls="siteplan-locations"
                        onchange="this.form.submit()" @disabled($lokasiOptions->isEmpty())>
                    @foreach ($lokasiOptions as $option)
                        <option value="{{ $option->id }}" @selected($selectedLocation?->id === $option->id)>{{ $option->nama_kavling }}</option>
                    @endforeach
                    </select>
                </form>
                @if ($lokasiKavling->isEmpty())
                    <p class="mt-3 text-muted">Belum ada lokasi siteplan tersedia.</p>
                @endif

                <div id="siteplan-locations">
                    @foreach ($lokasiKavling as $index => $kav)
                        <div class="siteplan-pane" id="pane-{{ $kav->id }}">

                            <div class="svg-card-wrapper">
                                <div class="svg-view-container svg-container" id="svg-container-{{ $kav->id }}">
                                    <button class="btn btn-reset reset-button">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset Siteplan
                                    </button>

                                    {{-- SVG Render --}}
                                    @if ($kav->masterSvg)
                                        {!! str_replace(['[[lebar]]', '[[tinggi]]'], ['100%', '100%'], $kav->masterSvg->header_svg) !!}

                                        @foreach ($kav->kavlingPeta as $pt)
                                            @php
                                                $warna = '#ffffff';
                                                if ($pt->customer) {
                                                    $warna = $pt->customer->progres->warna ?? '#ffffff';
                                                } elseif ($pt->is_booked) {
                                                    $warna = $holdColor;
                                                }
                                            @endphp

                                            <a href="javascript:void(0);" class="detail-button {{ $pt->siteplan_text_color === '#ffffff' ? 'text-white-svg' : '' }}"
                                                data-url="{{ route('public.siteplan.show', $pt->id) }}">
                                                {!! str_replace(
                                                    ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                                                    [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                                                    $pt->jenis_map == 'polygon' ? $kav->masterSvg->polygon_svg : $kav->masterSvg->path_svg,
                                                ) !!}
                                            </a>
                                        @endforeach

                                        {!! $kav->masterSvg->footer_svg !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <button class="legend-toggle" onclick="toggleLegend()">
        <i class="fas fa-info-circle"></i>
    </button>
    <div class="legend-box hidden" id="legendBox">
    <div class="legend-title">Keterangan Status</div>

        @foreach ($legend as $item)
            <div class="legend-item">
                <div class="legend-color" style="background-color: {{ $item->warna }}"></div>
                <div class="legend-label">{{ $item->status_progres }}</div>
            </div>
        @endforeach

        <button class="btn btn-xs btn-block mt-3 text-muted" onclick="toggleLegend()">Tutup</button>
    </div>

    {{-- Detail Popup --}}
    <div id="popupOverlay">
        <div id="popupBox">
            <button id="popupClose" onclick="closePopup()">&times;</button>
            <div class="popup-arrow"></div>

            <div class="popup-title">Detail Kavling</div>

            <div class="popup-row">
                <span class="popup-label">Perumahan</span>
                <span class="popup-value" id="p_nama_kavling">-</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Kode Kavling</span>
                <span class="popup-value" id="p_kode_kavling">-</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Tipe</span>
                <span class="popup-value"><span id="p_tipe_bangunan">-</span></span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Luas Tanah</span>
                <span class="popup-value"><span id="p_luas_tanah">-</span> m²</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Luas Bangunan</span>
                <span class="popup-value"><span id="p_luas_bangunan">-</span> m²</span>
            </div>

            <div class="popup-price">
                <span class="popup-label font-weight-bold">Total Harga</span>
                <span class="value"><span id="p_currency">Rp</span> <span id="p_hrg_jual">0</span></span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentTargetElement = null;
        let popupUpdateInterval = null;

        function toggleLegend() {
            $('#legendBox').toggleClass('hidden');
        }

        function updatePopupPosition() {
            if (!currentTargetElement || !$('#popupOverlay').is(':visible')) return;

            const targetRect = currentTargetElement.getBoundingClientRect();
            const popupBox = document.getElementById('popupBox');
            const popupWidth = popupBox.offsetWidth;
            const popupHeight = popupBox.offsetHeight;

            const targetCenterX = targetRect.left + (targetRect.width / 2);
            const targetCenterY = targetRect.top + (targetRect.height / 2);

            let left = targetCenterX - (popupWidth / 2);
            let top = targetCenterY - popupHeight - 25;

            const margin = 15;
            const winW = $(window).width();
            const winH = $(window).height();

            if (left < margin) left = margin;
            else if (left + popupWidth > winW - margin) left = winW - popupWidth - margin;

            if (top < margin) {
                top = targetCenterY + 25;
                $('.popup-arrow').css({
                    'bottom': 'auto', 'top': '-10px',
                    'border-top': 'none', 'border-bottom': '10px solid #fff'
                });
            } else {
                $('.popup-arrow').css({
                    'bottom': '-10px', 'top': 'auto',
                    'border-top': '10px solid #fff', 'border-bottom': 'none'
                });
            }

            $('#popupOverlay').css({ left: left + 'px', top: top + 'px' });
        }

        $(document).on('click', '.detail-button', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let url = $(this).data('url');
            currentTargetElement = this;

            $.get(url, function(res) {
                if (res.success) {
                    $('#p_nama_kavling').text(res.data.lokasi.nama_kavling);
                    $('#p_kode_kavling').text(res.data.kode_kavling);
                    $('#p_tipe_bangunan').text(res.data.nama_cluster || res.data.tipe_bangunan || '-');
                    $('#p_luas_tanah').text(res.data.luas_tanah);
                    $('#p_luas_bangunan').text(res.data.luas_bangunan);
                    if (res.data.total_harga > 0) {
                        $('#p_hrg_jual').text(parseFloat(res.data.total_harga).toLocaleString('id-ID'));
                        $('#p_currency').show();
                        $('.popup-price').show();
                    } else {
                        $('#p_hrg_jual').text('Harga belum tersedia');
                        $('#p_currency').hide();
                    }

                    $('#popupOverlay').fadeIn(200, function() { updatePopupPosition(); });

                    if (popupUpdateInterval) clearInterval(popupUpdateInterval);
                    popupUpdateInterval = setInterval(updatePopupPosition, 50);
                }
            });
        });

        function closePopup() {
            $('#popupOverlay').fadeOut(200);
            currentTargetElement = null;
            if (popupUpdateInterval) clearInterval(popupUpdateInterval);
        }

        $(document).on('click', function(e) {
            if ($(e.target).closest('#popupBox').length === 0 && !$(e.target).hasClass('detail-button')) {
                closePopup();
            }
        });

        $(window).on('resize scroll', updatePopupPosition);

        // Auto-update position on SVG zoom/pan
        if (window.MutationObserver) {
            const observer = new MutationObserver(() => {
                if ($('#popupOverlay').is(':visible')) updatePopupPosition();
            });
            $(document).ready(() => {
                $('svg').each(function() {
                    observer.observe(this, { attributes: true, attributeFilter: ['transform', 'style'] });
                });
            });
        }
    </script>
    <script src="{{ asset('assets/svg_1.js') }}"></script>
@endpush
