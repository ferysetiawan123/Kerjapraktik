@extends('layouts.master')

@section('title')
    Laporan Inventory Masuk
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
{{-- Tambahkan link CSS untuk Select2 jika belum ada di layout utama --}}
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/select2/dist/css/select2.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-sm btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <a href="{{ route('laporan.export_pdf', ['awal' => $tanggalAwal, 'akhir' => $tanggalAkhir, 'id_produk' => $idProduk]) }}" class="btn btn-danger btn-sm btn-flat" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
            </div>
            <div class="box-header with-border">
                <b>Dari Tanggal   :   {{ tanggal_indonesia($tanggalAwal, false) }}</b>
            </div>
            <div class="box-header with-border">
                <b>Sampai Tanggal   :   {{ tanggal_indonesia($tanggalAkhir, false) }}</b>
            </div>
            <div class="box-body table-responsive">
                {{-- Berikan ID unik pada tabel agar DataTables lebih spesifik --}}
                <table class="table table-stiped table-bordered" id="laporan-barang-masuk-table">
                    <thead>
                        <th width="5%">No</th>
                        <th>Nama Barang</th>
                        <th>Tanggal Masuk</th>
                        <th>Jumlah Masuk</th>
                        <th>Nama Supplier</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan.form') {{-- Modal filter --}}
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
{{-- Tambahkan script JS untuk Select2 jika belum ada di layout utama --}}
<script src="{{ asset('/AdminLTE-2/bower_components/select2/dist/js/select2.full.min.js') }}"></script>

<script>
    let table;

    $(function () {
        table = $('#laporan-barang-masuk-table').DataTable({ 
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan.data', ['awal' => $tanggalAwal, 'akhir' => $tanggalAkhir, 'id_produk' => $idProduk]) }}',
                error: function (xhr, error, thrown) {
                if (xhr.status === 200 && xhr.responseText === '{"data":[]}') {
                    console.warn('Data kosong, tapi bukan error.');
                } else if (xhr.status !== 200) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: 'Terjadi kesalahan saat memuat data laporan.<br>Detail: ' +
                            (xhr.responseJSON ? xhr.responseJSON.message : thrown) +
                            '<br>Silakan periksa konsol browser untuk detail lebih lanjut.',
                        confirmButtonText: 'OK'
                    });
                }
            }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_produk'},
                {data: 'tanggal'},
                {data: 'jumlahMasuk'},
                {data: 'nama_supplier'}, 
            ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
        });

        // Event handler saat form modal di-submit
        $('#modal-form form').submit(function (e) {
            e.preventDefault(); 

            // Dapatkan nilai dari form modal
            let tanggalAwalBaru = $('[name=tanggal_awal]').val();
            let tanggalAkhirBaru = $('[name=tanggal_akhir]').val();
            let idProdukBaru = $('[name=id_produk]').val(); 

            // Bangun URL AJAX baru dengan parameter filter produk
            let newUrl = '{{ route('laporan.data', ['awal' => '_awal_', 'akhir' => '_akhir_', 'id_produk' => '_id_produk_']) }}';
            newUrl = newUrl.replace('_awal_', tanggalAwalBaru);
            newUrl = newUrl.replace('_akhir_', tanggalAkhirBaru);
            newUrl = newUrl.replace('_id_produk_', idProdukBaru);

            // Perbarui URL DataTables dan muat ulang data
            table.ajax.url(newUrl).load();

            // Perbarui link export PDF dengan parameter filter produk
            let newPdfUrl = '{{ route('laporan.export_pdf', ['awal' => '_awal_', 'akhir' => '_akhir_', 'id_produk' => '_id_produk_']) }}';
            newPdfUrl = newPdfUrl.replace('_awal_', tanggalAwalBaru);
            newPdfUrl = newPdfUrl.replace('_akhir_', tanggalAkhirBaru);
            newPdfUrl = newPdfUrl.replace('_id_produk_', idProdukBaru);
            $('.btn-danger').attr('href', newPdfUrl); 

            // Perbarui teks tanggal di header
            $.get(`{{ url('/tanggal_indonesia') }}/${tanggalAwalBaru}/false`, function(data) {
                $('.box-header:nth-of-type(2) b').html(`Dari Tanggal   :   ${data}`);
            });
            $.get(`{{ url('/tanggal_indonesia') }}/${tanggalAkhirBaru}/false`, function(data) {
                $('.box-header:nth-of-type(3) b').html(`Sampai Tanggal   :   ${data}`);
            });

            $('#modal-form').modal('hide'); 
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush
