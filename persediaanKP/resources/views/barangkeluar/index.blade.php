@extends('layouts.master')

@section('title')
    Riwayat Barang Keluar
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Riwayat Barang Keluar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                {{-- Tombol Tambah Barang Keluar hanya untuk Administrator, Manajer, dan Kasir --}}
                @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager') || auth()->user()->hasRole('kasir')))
                <button onclick="addForm('{{ route('barangkeluar.store') }}')" class="btn btn-success btn-s btn-flat"><i class="fa fa-plus-circle"></i> Tambah Barang Keluar</button>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Nama Produk</th>
                        <th>Tanggal Keluar</th> {{-- PERBAIKAN: Pastikan ini 'Tanggal Keluar' --}}
                        <th>Jumlah Keluar</th>
                        <th>Penerima Barang</th>
                        <th>Keterangan Barang</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('barangkeluar.form')
@endsection

@push('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table;

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('barangkeluar.data') }}',
                error: function (xhr, error, thrown) {
                    console.error("DataTables AJAX Error:", xhr.responseText, error, thrown);
                    let errorMessage = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data!',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'tanggal_keluar'}, // <-- PERBAIKAN: Gunakan 'tanggal_keluar'
                {data: 'jumlah_keluar'},
                {data: 'penerima_barang'},
                {data: 'keterangan_barang'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (!e.isDefaultPrevented()) {
                e.preventDefault();

                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data barang keluar berhasil disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    })
                    .fail((jqXHR, textStatus, errorThrown) => {
                        let errorMessage = 'Terjadi kesalahan tidak terduga saat menyimpan data. Silakan coba lagi atau hubungi administrator.';

                        if (jqXHR.status === 422) {
                            let errors = jqXHR.responseJSON.errors;
                            errorMessage = 'Validasi gagal:\n';
                            for (let field in errors) {
                                let readableField = field.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
                                errorMessage += `- ${readableField}: ${errors[field].join(', ')}\n`;
                            }
                        } else if (jqXHR.status === 419) {
                            errorMessage = 'Sesi Anda telah berakhir atau token keamanan tidak valid. Silakan refresh halaman dan coba lagi.';
                        } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                        console.error("Server Error:", jqXHR.responseJSON || errorThrown);
                    });
            }
        });

        @if (auth()->check() && auth()->user()->hasRole('kasir'))
            table.column(-1).visible(false);
        @endif
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Barang Keluar');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=id_produk]').val('').trigger('change');
        $('#modal-form [name=jumlah_keluar]').val(1);
        $('#modal-form [name=tanggal_keluar]').val('{{ date('Y-m-d') }}'); // <-- PERBAIKAN: Gunakan tanggal_keluar
        $('#modal-form [name=penerima_barang]').val('{{ auth()->user()->name ?? '' }}');
        $('#modal-form [name=keterangan_barang]').val('');

        $('#modal-form [name=id_produk]').focus();
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Barang Keluar');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');

        $.get(url)
            .done((response) => {
                $('#modal-form [name=id_produk]').val(response.id_produk).trigger('change');
                $('#modal-form [name=tanggal_keluar]').val(response.tanggal_keluar); // <-- PERBAIKAN: Gunakan tanggal_keluar
                $('#modal-form [name=jumlah_keluar]').val(response.jumlah_keluar);
                $('#modal-form [name=penerima_barang]').val(response.penerima_barang);
                $('#modal-form [name=keterangan_barang]').val(response.keterangan_barang);
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                let serverError = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Tidak dapat menampilkan data. Silakan coba lagi.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: serverError,
                    confirmButtonText: 'OK'
                });
                console.error("Error fetching data:", jqXHR.responseJSON);
            });
    }

    function deleteData(url) {
        Swal.fire({
            title: 'Yakin ingin menghapus data ini?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        '_method': 'delete'
                    })
                    .done((response) => {
                        table.ajax.reload();
                        Swal.fire(
                            'Dihapus!',
                            response.message || 'Data berhasil dihapus!',
                            'success'
                        );
                    })
                    .fail((jqXHR, textStatus, errorThrown) => {
                        let serverError = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Tidak dapat menghapus data. Silakan coba lagi atau hubungi administrator.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: serverError,
                            confirmButtonText: 'OK'
                        });
                        console.error("Error deleting data:", jqXHR.responseJSON);
                    });
            }
        });
    }
</script>
@endpush
