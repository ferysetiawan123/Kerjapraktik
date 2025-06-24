<?php

// Fungsi untuk memformat tanggal ke format Indonesia
if (! function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($tgl, $tampil_hari = true)
    {
        $nama_hari  = array(
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'
        );
        $nama_bulan = array(1 =>
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember' // Dapatkan November yang hilang
        );

        $tahun   = substr($tgl, 0, 4);
        $bulan   = (int) substr($tgl, 5, 2); // Ambil angka bulan
        $tanggal = substr($tgl, 8, 2);
        $text    = '';

        // Pastikan bulan valid sebelum diakses dari array $nama_bulan
        if ($bulan < 1 || $bulan > 12) {
            return $tgl; // Kembalikan tanggal asli jika bulan tidak valid
        }

        $namaBulanFormatted = $nama_bulan[$bulan]; // Gunakan angka bulan untuk mendapatkan nama bulan

        if ($tampil_hari) {
            $urutan_hari = date('w', mktime(0,0,0, substr($tgl, 5, 2), $tanggal, $tahun));
            $hari        = $nama_hari[$urutan_hari];
            $text       .= "$hari, $tanggal $namaBulanFormatted $tahun"; // Gunakan namaBulanFormatted
        } else {
            $text       .= "$tanggal $namaBulanFormatted $tahun"; // Gunakan namaBulanFormatted
        }
        
        return $text; 
    }
}

// Fungsi untuk memformat angka menjadi format mata uang (misal: Rp. 1.000.000)
if (! function_exists('format_uang')) {
    function format_uang($angka)
    {
        // Menggunakan number_format untuk format ribuan dengan titik dan tanpa desimal
        return 'Rp. ' . number_format($angka, 0, ',', '.');
    }
}

/*
// Fungsi untuk menambahkan nol di depan angka
// Ini Dihapus karena sudah diganti dengan str_pad bawaan PHP di controller
// Jika Anda benar-benar membutuhkannya di tempat lain, Anda bisa kembalikan
if (! function_exists('tambah_nol_didepan')) {
    function tambah_nol_didepan($value, $threshold = null)
    {
        // Pastikan $threshold adalah integer positif
        if (!is_numeric($threshold) || $threshold <= 0) {
            $threshold = 1; // Default ke 1 jika threshold tidak valid
        }
        return sprintf("%0". $threshold . "s", $value);
    }
}
*/