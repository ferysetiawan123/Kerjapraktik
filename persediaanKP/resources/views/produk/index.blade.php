@extends('layouts.master')

@section('title')
    Daftar Produk
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Produk</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="btn-group">
                    {{-- Tombol Tambah dan Hapus Terpilih hanya untuk Administrator dan Manajer --}}
                    @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager')))
                    <button onclick="addForm('{{ route('produk.store') }}')" class="btn btn-success btn-s btn-flat"><i class="fa fa-plus-circle"></i> Tambah Produk</button>
                    <button onclick="deleteSelected('{{ route('produk.delete_selected') }}')" class="btn btn-danger btn-s btn-flat"><i class="fa fa-trash"></i> Hapus Terpilih</button>
                    @endif
                    {{-- Tambahkan info untuk menambah stok melalui Barang Masuk --}}
                    <button type="button" class="btn btn-warning btn-s btn-flat" data-toggle="tooltip" data-placement="top" title="Untuk menambah stok produk yang sudah ada, gunakan menu 'Barang Masuk'."><i class="fa fa-info-circle"></i> Info Stok</button>
                </div>
            </div>
            <div class="box-body table-responsive">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-stiped table-bordered">
                        <thead>
                            <th width="5%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

@includeIf('produk.form')
@endsection

@push('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table;
    const produkDataUrl = '{{ route('produk.data') }}';
    // Base URL untuk produk, akan digunakan di JS
    const produkBaseUrl = '{{ url('/produk') }}';

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: produkDataUrl,
            },
            columns: [
                {
                    data: 'id_produk',
                    render: function (data, type, row) {
                        return `<input type="checkbox" name="id_produk[]" value="${data}">`;
                    },
                    searchable: false,
                    sortable: false
                },
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'nama_kategori'},
                {data: 'merk'},
                {data: 'harga_beli'},
                {data: 'harga_jual'},
                {data: 'stok'},
                {data: 'satuan'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (!e.isDefaultPrevented()) {
                e.preventDefault();


                const form = $(this).find('form');
                const formData = new FormData(form[0]);
                formData.append('_method', form.find('[name=_method]').val());

                $.ajax({
                    url: form.attr('action'),
                    type: form.find('[name=_method]').val() === 'put' ? 'POST' : 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Produk berhasil disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        let errorMessage = 'Terjadi kesalahan tidak terduga saat menyimpan data. Silakan coba lagi atau hubungi administrator.';

                        if (jqXHR.status === 422) {
                            let errors = jqXHR.responseJSON.errors;
                            errorMessage = 'Validasi gagal:\n';
                            for (let field in errors) {
                                let readableField = field.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
                                errorMessage += `- ${readableField}: ${errors[field].join(', ')}\n`;
                            }

                            if (typeof validator !== 'undefined') {
                                validator.invalidate(errors);
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
                    }
                });
            }
        });

        // Event listener untuk checkbox 'select_all'
        $('[name=select_all]').on('click', function () {
            $('input[name="id_produk[]"]').prop('checked', this.checked);
        });

        // Event listener untuk setiap checkbox produk individu
        $('.table tbody').on('change', 'input[type="checkbox"]', function () {
            if (!this.checked) {
                $('[name=select_all]').prop('checked', false);
            } else if ($('input[name="id_produk[]"]:checked').length === $('input[name="id_produk[]"]').length) {
                $('[name=select_all]').prop('checked', true);
            }
        });

        // Sembunyikan tombol "Tambah Produk" dan "Hapus Terpilih" untuk Kasir
        @if (auth()->check() && auth()->user()->hasRole('kasir'))
            $('.btn-group button').hide();
            table.column(-1).visible(false);
            table.column(0).visible(false);

        @endif
        // Inisialisasi tooltip
        $('[data-toggle="tooltip"]').tooltip();
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Produk');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();
        $('#modal-form [name=stok]').attr('readonly', false);
        $('#modal-form [name=stok]').attr('required', true);
        $('#modal-form [name=stok]').val(0);
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');
        $('#modal-form .select2').val('').trigger('change');
        return false;
    }

    function editForm(url) {
        console.log("Fetching data from:", url);
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_produk]').focus();
        $('#modal-form [name=stok]').attr('readonly', true);
        $('#modal-form [name=stok]').attr('required', false);
        $('.help-block.with-errors').empty();
        $('.form-group').removeClass('has-error');
        $('#modal-form .select2').val('').trigger('change');

        $.get(url)
            .done((response) => {
                // Perbaikan cara mengatur action URL

                $('#modal-form form').attr('action', produkBaseUrl + '/' + response.id_produk);

                $('#modal-form [name=nama_produk]').val(response.nama_produk);
                $('#modal-form [name=id_kategori]').val(response.id_kategori).trigger('change');
                $('#modal-form [name=merk]').val(response.merk);
                $('#modal-form [name=harga_beli]').val(response.harga_beli);
                $('#modal-form [name=harga_jual]').val(response.harga_jual);
                $('#modal-form [name=stok]').val(response.stok);
                $('#modal-form [name=satuan]').val(response.satuan);
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
                $('#modal-form').modal('hide');
            });
        return false;
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
        return false;
    }

    function deleteSelected(url) {
        const checkedIds = $('input[name="id_produk[]"]:checked').map(function() {
            return this.value;
        }).get();

        if (checkedIds.length > 0) {
            Swal.fire({
                title: 'Yakin ingin menghapus ' + checkedIds.length + ' data terpilih?',
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
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            ids: checkedIds
                        })
                        .done((response) => {
                            table.ajax.reload();
                            Swal.fire(
                                'Dihapus!',
                                response.message || 'Data terpilih berhasil dihapus!',
                                'success'
                            );
                        })
                        .fail((jqXHR, textStatus, errorThrown) => {
                            let serverError = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Tidak dapat menghapus data terpilih. Silakan coba lagi atau hubungi administrator.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: serverError,
                                confirmButtonText: 'OK'
                            });
                            console.error("Error deleting selected data:", jqXHR.responseJSON);
                        });
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Pilih minimal satu data yang akan dihapus!',
                confirmButtonText: 'OK'
            });
        }
    }
</script>
@endpush
