@extends('layouts.master')

@section('title')
    Daftar Pengguna
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Pengguna</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm()" class="btn btn-success btn-flat"><i class="fa fa-plus-circle"></i> Tambah</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('user.form')
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('.table').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('user.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'name'},
                {data: 'email'},
                {data: 'role'},
                {data: 'action', searchable: false, sortable: false},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done(response => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail(errors => {
                        alert('Tidak dapat menyimpan data');
                        
                        if (errors.status == 422) {
                            let messages = '';
                            $.each(errors.responseJSON.errors, function(key, value){
                                messages += value[0] + '\n';
                            });
                            alert(messages);
                        } else {
                            alert('Terjadi kesalahan server.');
                        }
                        return;
                    });
            }
        });
    });

    function addForm() {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Pengguna');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', '{{ route('user.store') }}');
        $('#modal-form [name=_method]').val('post');
        $('#password, #password_confirmation').attr('required', true);
        $('.help-block.with-errors').html(''); 
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Pengguna');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#password, #password_confirmation').attr('required', false);
        $('.help-block.with-errors').html(''); 


        $.get(url)
            .done(response => {
                $('#modal-form [name=name]').val(response.name);
                $('#modal-form [name=email]').val(response.email);
                $('#modal-form [name=role]').val(response.role); 
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                  
                    if (errors.status == 403) { 
                        alert(errors.responseJSON);
                    } else {
                        alert('Terjadi kesalahan saat menghapus data.');
                    }
                    return;
                });
        }
    }
</script>
@endpush