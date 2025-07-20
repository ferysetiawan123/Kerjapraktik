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


        // Akun Kepala Gudang
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], 
            [
                'name' => 'Kepalagudang',
                'password' => Hash::make('123'),  
                'role' => 'administrator', 
                'email_verified_at' => now(), 
            ]
        );

        // Akun Manajer
        User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name' => 'Staffgudang',
                'password' => Hash::make('123'),
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
                'role' => 'kasir', // Role Kasir
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'kasir2@gmail.com'],
            [
                'name' => 'Kasir 2',
                'password' => Hash::make('123'),
                'role' => 'kasir', // Role Kasir
                'email_verified_at' => now(),
            ]
        );

        
    }
}
