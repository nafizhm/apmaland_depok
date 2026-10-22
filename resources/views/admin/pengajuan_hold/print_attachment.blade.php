<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Lampiran - {{ $data->nama_lengkap }}</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f1f5f3; }
        .toolbar { padding:14px 20px; background:white; display:flex; flex-wrap:wrap; align-items:center; gap:12px; }
        button, a { padding:8px 12px; cursor:pointer; } button { background:#315f31; color:white; border:0; border-radius:6px; }
        .document { display:block; max-width:100%; height:auto; margin:20px auto; }
        iframe { width:100%; height:calc(100vh - 90px); border:0; }
        @page { size:A4; margin:10mm; }
        @media print { .toolbar { display:none; } body { background:white; } .document { margin:0 auto; max-width:100%; max-height:277mm; object-fit:contain; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong>Lampiran {{ $data->nama_lengkap }}</strong>
        <button type="button" onclick="printDocument()">Cetak</button>
        <a href="{{ route('pengajuan-hold.attachment', ['id' => $data->id, 'field' => $field, 'action' => 'download']) }}">Download</a>
        @if ($isPdf)<a href="{{ $url }}" target="_blank" rel="noopener">Buka PDF</a><small>Jika dialog cetak tidak muncul, buka PDF lalu pilih ikon cetak.</small>@endif
    </div>
    @if ($isPdf)
        <iframe id="documentPdf" src="{{ $url }}" title="Lampiran PDF"></iframe>
    @else
        <img id="documentImage" class="document" src="{{ $url }}" alt="Lampiran {{ $data->nama_lengkap }}">
    @endif
    <script>
        function printDocument() {
            const pdf = document.getElementById('documentPdf');
            if (pdf) {
                try { pdf.contentWindow.focus(); pdf.contentWindow.print(); }
                catch (error) { window.open(@json($url), '_blank', 'noopener'); }
            } else { window.print(); }
        }
        const image = document.getElementById('documentImage');
        if (image) {
            if (image.complete && image.naturalWidth) printDocument();
            else image.addEventListener('load', printDocument, {once:true});
        }
    </script>
</body>
</html>
