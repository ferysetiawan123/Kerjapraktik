<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inventory Barang | @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{-- AdminLTE-2 CSS --}}
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/dist/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    
    {{-- TAMBAHAN UNTUK LAPORAN (DATE PICKER) --}}
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">

    {{-- SweetAlert2 CSS (via CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    {{-- Select2 CSS (via CDN atau Lokal) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    {{-- Optional: Select2 theme for Bootstrap 4 (if using Bootstrap 4 components, though AdminLTE 2 uses Bootstrap 3) --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5.0.15/bootstrap-4.min.css"> --}}


    {{-- Google Fonts --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    {{-- Custom CSS untuk halaman spesifik --}}
    @stack('css')
</head>
<body class="hold-transition skin-purple-light sidebar-mini">
    <div class="wrapper">

        @includeIf('layouts.header')

        @includeIf('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    @yield('title')
                </h1>
                <ol class="breadcrumb">
                    @section('breadcrumb')
                        <li><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    @show
                </ol>
            </section>

            <section class="content">

                @yield('content')

            </section>
        </div>
        @includeIf('layouts.footer')
    </div>

    {{-- AdminLTE-2 JS --}}
    <script src="{{ asset('AdminLTE-2/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    
    {{-- TAMBAHAN UNTUK DATE PICKER --}}
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    
    {{-- TAMBAHAN LAIN DARI ADMINLTE --}}
    <script src="{{ asset('AdminLTE-2/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/fastclick/lib/fastclick.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/dist/js/adminlte.min.js') }}"></script>

    {{-- VALIDATOR.MIN.JS --}}
    <script src="{{ asset('js/validator.min.js') }}"></script>

    {{-- SweetAlert2 JS (via CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    {{-- Select2 JS (via CDN atau Lokal) --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk semua elemen dengan class 'select2' yang bukan bagian dari modal-form
            // Karena yang di modal-form sudah dihandle di form.blade.php dengan dropdownParent
            $('.select2:not(#modal-form .select2)').select2({
                width: '100%' // Agar Select2 memenuhi lebar container
            });

            // Global Datepicker initialization (for datepickers outside modals, if any)
            // For datepickers inside modals, it's better to initialize them when the modal opens
            $('.datepicker:not(#modal-form .datepicker)').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                endDate: '0d' // Batasi tanggal hanya sampai hari ini
            });

            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Global SweetAlert2 configuration (optional, adjust as needed)
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            window.showSuccessToast = function(message) {
                Toast.fire({
                    icon: 'success',
                    title: message
                });
            };

            window.showErrorToast = function(message) {
                Toast.fire({
                    icon: 'error',
                    title: message
                });
            };

            // Global error handler for AJAX
            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                if (jqxhr.status === 422) { // Unprocessable Entity (Laravel Validation Error)
                    // This is handled by validator.min.js usually
                } else if (jqxhr.status === 401) { // Unauthorized
                    showErrorToast('Sesi Anda telah berakhir. Silakan login kembali.');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else if (jqxhr.status === 500) { // Server Error
                    showErrorToast('Terjadi kesalahan pada server. Mohon coba lagi nanti.');
                    console.error('Server Error:', jqxhr.responseText);
                } else if (thrownError) {
                    showErrorToast('Terjadi kesalahan: ' + thrownError);
                    console.error('AJAX Error:', thrownError);
                } else {
                    showErrorToast('Terjadi kesalahan yang tidak diketahui.');
                    console.error('Unknown AJAX Error:', jqxhr.responseText);
                }
            });
        });
    </script>

    {{-- Skrip kustom untuk halaman spesifik --}}
    @stack('scripts')
</body>
</html>