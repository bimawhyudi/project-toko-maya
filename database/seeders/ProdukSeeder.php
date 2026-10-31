<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(__DIR__.'/data/produk.json'),
            true
        );

        $kategori = Kategori::pluck('id', 'nama_kategori');

        foreach ($data as $baris) {
            Produk::updateOrCreate(
                ['nama_produk' => $baris['nama_produk']],
                [
                    'kategori_id' => $kategori[$baris['kategori']],
                    'deskripsi' => $baris['deskripsi'],
                    'harga' => $baris['harga'],
                    'stok' => $baris['stok'],
                    'gambar' => null,
                ]
            );
        }
    }
}