<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Opsi: Hapus semua user yang ada sebelum seeding.
        // HANYA JIKA ANDA YAKIN! Jika tidak, komentar baris di bawah.
        // User::truncate();

        // Akun Administrator
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Kondisi untuk mencari user
            [
                'name' => 'Administrator',
                'password' => Hash::make('123'), // Gunakan Hash::make() untuk password
                'foto' => '/img/user.jpg', // Jika ini kolom 'foto' di tabel 'users'
                'role' => 'administrator', // Ganti 'level' menjadi 'role'
                'email_verified_at' => now(), // Opsional: Tambahkan ini jika menggunakan email verification
            ]
        );

        // Akun Manajer
        User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name' => 'Manajer',
                'password' => Hash::make('123'),
                'foto' => '/img/user.jpg',
                'role' => 'manager', // Role Manajer
                'email_verified_at' => now(),
            ]
        );

        // Akun Kasir 1
        User::updateOrCreate(
            ['email' => 'kasir1@gmail.com'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('123'),
                'foto' => '/img/user.jpg',
                'role' => 'kasir', // Role Kasir
                'email_verified_at' => now(),
            ]
        );

        // Tambahkan akun kasir lain jika diperlukan
        // User::updateOrCreate(
        //     ['email' => 'kasir2@gmail.com'],
        //     [
        //         'name' => 'Kasir 2',
        //         'password' => Hash::make('123'),
        //         'foto' => '/img/user.jpg',
        //         'role' => 'kasir',
        //         'email_verified_at' => now(),
        //     ]
        // );
    }
}