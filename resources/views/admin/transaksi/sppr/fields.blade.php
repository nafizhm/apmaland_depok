<hr>
<h6 class="font-weight-bold">Data Tambahan SPR</h6>
<div class="form-group row">
    <label for="jenis_pembelian" class="col-sm-3 col-form-label">Cara Pembelian</label>
    <div class="col-sm-8"><select name="spr_data[jenis_pembelian]" id="jenis_pembelian" class="form-control"><option value="KPR">KPR</option><option value="TUNAI">Tunai</option></select></div>
</div>
@foreach (\App\Services\SprDocument::TEXT_FIELDS as $field => $label)
    <div class="form-group row">
        <label for="{{ $field }}" class="col-sm-3 col-form-label">{{ $label }}</label>
        <div class="col-sm-8"><input type="{{ $field === 'email' ? 'email' : 'text' }}" name="spr_data[{{ $field }}]" id="{{ $field }}" class="form-control spr-extra" maxlength="{{ $field === 'email' ? 255 : 500 }}"></div>
    </div>
@endforeach
<div class="form-group row">
    <label for="kelebihan_tanah" class="col-sm-3 col-form-label">Kelebihan Tanah (m²)</label>
    <div class="col-sm-8"><input type="number" name="spr_data[kelebihan_tanah]" id="kelebihan_tanah" min="0" step="0.01" class="form-control spr-extra"></div>
</div>
@foreach (\App\Services\SprDocument::MONEY_FIELDS as $field => $label)
    <div class="form-group row">
        <label for="{{ $field }}" class="col-sm-3 col-form-label">{{ $label }}</label>
        <div class="col-sm-8"><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="text" name="spr_data[{{ $field }}]" id="{{ $field }}" class="form-control rupiah spr-extra" inputmode="numeric"></div></div>
    </div>
@endforeach
@foreach (['tanggal_booking' => 'Tanggal Tanda Jadi', 'tanggal_angsuran_pertama' => 'Tanggal Angsuran Pertama'] as $field => $label)
    <div class="form-group row">
        <label for="{{ $field }}" class="col-sm-3 col-form-label">{{ $label }}</label>
        <div class="col-sm-8"><input type="date" name="spr_data[{{ $field }}]" id="{{ $field }}" class="form-control spr-extra"></div>
    </div>
@endforeach
<div class="alert alert-light border" id="spr-totals" aria-live="polite"></div>
<h6 class="font-weight-bold">Jadwal Angsuran Selanjutnya</h6>
<p class="text-muted">Isi sesuai kesepakatan, maksimal 20 angsuran. Baris kosong tidak disimpan.</p>
<div class="table-responsive"><table class="table table-sm table-bordered">
    <thead><tr><th>No.</th><th>Tanggal</th><th>Nominal (Rp)</th></tr></thead>
    <tbody>
        @for ($i = 0; $i < 20; $i++)
            <tr class="spr-installment">
                <td>{{ $i + 1 }}</td>
                <td><input type="date" name="spr_data[angsuran][{{ $i }}][tanggal]" id="angsuran_{{ $i }}_tanggal" class="form-control spr-extra" aria-label="Tanggal angsuran {{ $i + 1 }}"></td>
                <td><input type="text" name="spr_data[angsuran][{{ $i }}][nominal]" id="angsuran_{{ $i }}_nominal" class="form-control rupiah spr-extra" inputmode="numeric" aria-label="Nominal angsuran {{ $i + 1 }}"></td>
            </tr>
        @endfor
    </tbody>
</table></div>
