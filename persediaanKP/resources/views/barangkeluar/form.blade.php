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
                        <label for="tanggal_keluar" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Keluar</label>
                        <div class="col-lg-8">
                            <input type="date" name="tanggal_keluar" id="tanggal_keluar" class="form-control" required autofocus max="{{ date('Y-m-d') }}">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="jumlah_keluar" class="col-lg-2 col-lg-offset-1 control-label">Jumlah Keluar</label>
                        <div class="col-lg-8">
                            <input type="number" name="jumlah_keluar" id="jumlah_keluar" class="form-control" required autofocus min=1>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="penerima_barang" class="col-lg-2 col-lg-offset-1 control-label">Penerima Barang</label>
                        <div class="col-lg-8">
                            <input type="text" name="penerima_barang" id="penerima_barang" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="keterangan_barang" class="col-lg-2 col-lg-offset-1 control-label">Keterangan</label>
                        <div class="col-lg-8">
                            <input type="text" name="keterangan_barang" id="keterangan_barang" class="form-control" autofocus>
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
