@extends('admin.layout_admin')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header p-3">
                                <div class="d-flex align-content-center justify-content-between">
                                    <h3 class="font-weight-bold text-lg">Arsip Pengajuan Hold</h3>
                                    <a href="{{ route('pengajuan-hold.index') }}" class="btn btn-secondary">Kembali</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped data-table w-100">
                                    <thead>
                                        <tr>
                                            <th width="30px">No</th>
                                            <th>Customer</th>
                                            <th>Marketing</th>
                                            <th>Lokasi</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $(document).on('submit', '.data-table form', function(e) {
                e.preventDefault();
                const form = $(this);
                Swal.fire({
                    title: 'Hapus arsip ini?',
                    text: 'Data pengajuan hold akan dihapus secara permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize()
                    }).catch(() => {
                        Swal.showValidationMessage('Gagal menghapus arsip. Silakan coba lagi.');
                    })
                }).then((result) => {
                    if (result.isConfirmed) {
                        table.ajax.reload(null, false);
                        toastr.success('Arsip telah dihapus.');
                    }
                });
            });
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: false,
                responsive: true,
                ordering: false,
                ajax: "{{ route('pengajuan-hold.arsip') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama_lengkap',
                        name: 'nama_lengkap',
                    },
                    {
                        data: 'nama_marketing',
                        name: 'nama_marketing',
                    },
                    {
                        data: 'kode_kavling',
                        name: 'kode_kavling',
                    },

                    {
                        data: 'stt_reg',
                        name: 'stt_reg',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
        });
    </script>
@endpush

