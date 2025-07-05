@extends('layouts.master')

@section('title')
    Riwayat Barang Masuk
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Riwayat Barang Masuk</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                {{-- Tombol Tambah Barang Masuk hanya untuk Administrator, Manajer, dan Kasir --}}
                @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager') || auth()->user()->hasRole('kasir')))
                <button onclick="addForm('{{ route('barangmasuk.store') }}')" class="btn btn-success btn-s btn-flat"><i class="fa fa-plus-circle"></i> Tambah Barang Masuk</button>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode Produk</th>
                        <th>Tanggal Masuk</th>
                        <th>Jumlah Masuk</th>
                        <th>Supplier</th>
                        <th>Penerima</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Memastikan form modal disertakan --}}
@includeIf('barangmasuk.form')
@endsection

@push('scripts')
<script>
    // Mengatur CSRF token untuk semua permintaan AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table;

    $(function () {
        // Inisialisasi DataTables
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('barangmasuk.data') }}',
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
                {data: 'tanggal_masuk'}, // Menggunakan 'tanggal_masuk' sesuai dengan nama kolom di DataTables
                {data: 'jumlah_masuk'},
                {data: 'nama'},
                {data: 'penerima_barang'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        // Inisialisasi Datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
        });

        // Handler untuk submit form tambah/edit
        $('#modal-form').validator().on('submit', function (e) {
            if (!e.isDefaultPrevented()) {
                e.preventDefault();

                // Pastikan Anda mendapatkan data dari form dengan benar
                // Misalnya, jika form menggunakan input tanggal dengan name="tanggal_masuk",
                // maka data yang diserialisasi akan otomatis menyertakannya.
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data barang masuk berhasil disimpan!',
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
                                // Memformat nama field agar lebih mudah dibaca (e.g., id_produk -> Id Produk)
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

        // Menyembunyikan kolom aksi untuk user dengan role 'kasir'
        @if (auth()->check() && auth()->user()->hasRole('kasir'))
            table.column(-1).visible(false);
        @endif
    });

    // Fungsi untuk menampilkan form tambah barang masuk
    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Barang Masuk');

        $('#modal-form form')[0].reset(); // Reset semua input form
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');

        // Reset dan set nilai default untuk field tertentu
        $('#modal-form [name=id_produk]').val('').trigger('change');
        $('#modal-form [name=id_supplier]').val('').trigger('change');
        $('#modal-form [name=jumlah_masuk]').val(1); // Set default jumlah_masuk menjadi 1
        // PASTIKAN INPUT NAME DI FORM MODAL ADALAH 'tanggal_masuk'
        $('#modal-form [name=tanggal_masuk]').val('{{ date('Y-m-d') }}'); // Set tanggal hari ini
        $('#modal-form [name=penerima_barang]').val('{{ auth()->user()->name ?? '' }}'); // Auto-fill penerima dengan nama user

        // Reset validasi
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');

        $('#modal-form [name=id_produk]').focus(); // Fokus ke input id_produk
    }

    // Fungsi untuk menampilkan form edit barang masuk
    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Barang Masuk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');

        $.get(url)
            .done((response) => {
                // Mengisi form dengan data dari respons server (kolom snake_case)
                $('#modal-form [name=id_produk]').val(response.id_produk).trigger('change');
                // PASTIKAN INPUT NAME DI FORM MODAL ADALAH 'tanggal_masuk'
                $('#modal-form [name=tanggal_masuk]').val(response.tanggal_masuk);
                $('#modal-form [name=jumlah_masuk]').val(response.jumlah_masuk);
                $('#modal-form [name=id_supplier]').val(response.id_supplier).trigger('change');
                $('#modal-form [name=penerima_barang]').val(response.penerima_barang);
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

    // Fungsi untuk menghapus data barang masuk
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
