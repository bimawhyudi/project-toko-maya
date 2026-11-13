<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Impor produk dari database/seeders/data/produk.json (hasil konversi
     * konversi_produk.py dari produk.xlsx). Hanya barang dengan harga eceran
     * valid yang diimpor; stok awal adalah data contoh yang wajar.
     */
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

        // Foto asli hasil desain Figma (database/seeders/data/gambar-produk/) —
        // dipasangkan ke produk yang sesuai bila filenya tersedia.
        $fileGambar = __DIR__.'/data/gambar-produk.json';
        if (is_file($fileGambar)) {
            foreach (json_decode(file_get_contents($fileGambar), true) as $file => $namaList) {
                if (! is_file(__DIR__.'/data/gambar-produk/'.$file)) {
                    continue;
                }
                foreach ((array) $namaList as $nama) {
                    Produk::where('nama_produk', $nama)
                        ->update(['gambar' => 'produk/'.$file]);
                }
            }
        }
    }
}
