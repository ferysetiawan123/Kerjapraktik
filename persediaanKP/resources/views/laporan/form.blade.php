<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('laporan.index') }}" method="get" data-toggle="validator" class="form-horizontal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Periode Laporan</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tanggal_awal" class="col-lg-2 col-lg-offset-1 control-label">Mulai Tanggal</label>
                        <div class="col-lg-6">
                            <input type="text" name="tanggal_awal" id="tanggal_awal" class="form-control datepicker" required autofocus
                                value="{{ request('tanggal_awal', date('Y-m-01')) }}" 
                                style="border-radius: 0 !important;">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tanggal_akhir" class="col-lg-2 col-lg-offset-1 control-label">Sampai Tanggal</label>
                        <div class="col-lg-6">
                            <input type="text" name="tanggal_akhir" id="tanggal_akhir" class="form-control datepicker" required
                                value="{{ request('tanggal_akhir', date('Y-m-d')) }}" 
                                style="border-radius: 0 !important;">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    
                    {{-- --- Tambahkan Filter Produk --- --}}
                    {{-- Variabel $produks berasal dari LaporanController::index() --}}
                    @isset($produks) 
                    <div class="form-group row">
                        <label for="id_produk" class="col-lg-2 col-lg-offset-1 control-label">Produk</label>
                        <div class="col-lg-6">
                            <select name="id_produk" id="id_produk" class="form-control select2">
                                <option value="">Semua Produk</option>
                                @foreach ($produks as $item)
                                    <option value="{{ $item->id_produk }}" 
                                        {{ (request('id_produk') == $item->id_produk || (isset($idProduk) && $idProduk == $item->id_produk)) ? 'selected' : '' }}>
                                        {{ $item->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    @endisset
                    {{-- --- Akhir Filter Produk --- --}}

                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i>Tampilkan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi datepicker untuk form modal laporan
        $('#modal-form .datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            endDate: '0d' 
        });

        // Inisialisasi Select2 untuk dropdown produk
        // Pastikan Anda sudah memuat library Select2 di layout utama atau di halaman ini
        if ($.fn.select2) { // Cek apakah Select2 sudah dimuat
            $('#modal-form .select2').select2({
                placeholder: 'Pilih Produk',
                allowClear: true,
                dropdownParent: $('#modal-form') // Penting untuk Select2 di dalam modal
            });
        }

        // Event listener saat modal ditunjukkan
        $('#modal-form').on('shown.bs.modal', function () {
            $('#modal-form .datepicker').datepicker('destroy').datepicker({ 
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                endDate: '0d'
            });
            $('#modal-form [name=tanggal_awal]').focus();

            // Re-initialize Select2 saat modal ditampilkan untuk memastikan posisi dropdown yang benar
            if ($.fn.select2) {
                $('#modal-form .select2').select2('destroy'); 
                $('#modal-form .select2').select2({
                    placeholder: 'Pilih Produk',
                    allowClear: true,
                    dropdownParent: $('#modal-form') 
                });
            }
        });
    });
</script>