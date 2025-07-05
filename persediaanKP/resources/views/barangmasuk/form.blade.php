<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="id_produk" class="col-lg-2 col-lg-offset-1 control-label">Kode Produk</label>
                        <div class="col-md-8">
                            <select name="id_produk" id="id_produk" class="form-control select2" required>
                                <option value="">Pilih Kode Produk</option>
                                @foreach ($produk as $key => $item)
                                    <option value="{{ $key }}">{{$item}}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tanggal_masuk" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Masuk</label>
                        <div class="col-lg-8">
                            {{-- PERUBAHAN UTAMA: name diubah dari "tanggal" menjadi "tanggal_masuk" --}}
                            {{-- PERUBAHAN: type diubah dari "date" menjadi "text" dan ditambahkan class "datepicker" --}}
                            <input type="text" name="tanggal_masuk" id="tanggal_masuk" class="form-control datepicker" required
                                data-date-format="yyyy-mm-dd" data-date-end-date="0d" value="{{ date('Y-m-d') }}">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="jumlah_masuk" class="col-lg-2 col-lg-offset-1 control-label">Jumlah Masuk</label>
                        <div class="col-lg-8">
                            <input type="number" name="jumlah_masuk" id="jumlah_masuk" class="form-control" required min="1">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_supplier" class="col-lg-2 col-lg-offset-1 control-label">Nama Supplier</label>
                        <div class="col-md-8">
                            <select name="id_supplier" id="id_supplier" class="form-control select2" required>
                                <option value="">Pilih Nama Supplier</option>
                                @foreach ($supplier as $key => $item)
                                    <option value="{{ $key }}">{{$item}}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="penerima_barang" class="col-lg-2 col-lg-offset-1 control-label">Penerima</label>
                        <div class="col-lg-8">
                            <input type="text" name="penerima_barang" id="penerima_barang" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Pastikan ini ada di resources/views/layouts/master.blade.php atau di bagian scripts global Anda --}}
{{-- Atau pastikan Anda memuat Bootstrap Datepicker JS dan CSS yang diperlukan --}}
{{-- <link rel="stylesheet" href="/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css"> --}}
{{-- <script src="/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> --}}

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 pada modal saat pertama kali dimuat
        // Penting: Inisialisasi Select2 perlu dilakukan setelah elemen tersedia di DOM
        // Jika Select2 tidak bekerja, coba inisialisasi di fungsi addForm/editForm atau saat modalShown event
        $('#modal-form .select2').select2({
            dropdownParent: $('#modal-form') // Penting untuk modal agar dropdown muncul di atas modal
        });

        // Inisialisasi Datepicker pada modal saat pertama kali dimuat
        // Datepicker juga perlu diinisialisasi setelah elemen tersedia
        $('#modal-form .datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            // opsi ini membatasi tanggal hanya sampai hari ini atau sebelumnya
            endDate: '0d'
        });

        // Event listener saat modal ditunjukkan untuk inisialisasi ulang datepicker dan select2 jika diperlukan
        $('#modal-form').on('shown.bs.modal', function () {
            $('#modal-form .select2').select2({
                dropdownParent: $('#modal-form')
            });
            $('#modal-form .datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                endDate: '0d'
            }).on('changeDate', function(e) {
                // Saat tanggal diubah, pastikan validator membersihkan error
                $(this).closest('.form-group').find('.help-block.with-errors').empty();
                $(this).closest('.form-group').removeClass('has-error');
            });
            // Pastikan fokus ke input produk saat modal terbuka
            $('#modal-form [name=id_produk]').focus();
        });
    });
</script>
