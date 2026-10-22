@if ($data->attachmentUrl($field))
    <div class="d-flex flex-wrap mt-2 mb-2" style="gap:6px;">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('pengajuan-hold.attachment', ['id' => $data->id, 'field' => $field, 'action' => 'download']) }}"><i class="fas fa-download mr-1" aria-hidden="true"></i>Download</a>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('pengajuan-hold.attachment', ['id' => $data->id, 'field' => $field, 'action' => 'print']) }}" target="_blank" rel="noopener"><i class="fas fa-print mr-1" aria-hidden="true"></i>Cetak</a>
    </div>
@endif
