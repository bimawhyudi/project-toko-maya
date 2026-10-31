<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(__DIR__.'/data/produk.json'),
            true
        );

        $namaKategori = array_values(array_unique(
            array_column($data, 'kategori')
        ));

        foreach ($namaKategori as $nama) {
            Kategori::updateOrCreate(
                ['nama_kategori' => $nama],
                ['deskripsi' => 'Kategori '.$nama.' Toko Maya']
            );
        }
    }
}