<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make(env('AKUN_DEMO_PASSWORD', 'password123'));

        $akun = [
            ['nama' => 'Pemilik Toko Maya', 'email' => 'pemilik@tokomaya.test', 'role' => User::ROLE_PEMILIK],
            ['nama' => 'Dewi Lestari', 'email' => 'kasir@tokomaya.test', 'role' => User::ROLE_KASIR],
            ['nama' => 'Rahmat Alfarizi', 'email' => 'gudang@tokomaya.test', 'role' => User::ROLE_ADMIN_GUDANG],
            ['nama' => 'Budi Santoso', 'email' => 'pelanggan@tokomaya.test', 'role' => User::ROLE_PELANGGAN],
        ];

        foreach ($akun as $a) {
            User::updateOrCreate(
                ['email' => $a['email']],
                $a + ['password' => $password, 'no_hp' => '08120000000'.random_int(1, 9)]
            );
        }
    }
}