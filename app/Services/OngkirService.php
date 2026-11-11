<?php

namespace App\Services;

/**
 * Driver MOCK layanan ongkir — dipakai selama belum ada API key
 * RajaOngkir/Komerce. Ongkir disimulasikan berdasarkan nama kota tujuan:
 * Padang & sekitarnya lebih murah, kota lain dihitung dari jumlah karakter
 * (hanya untuk contoh, bukan tarif nyata).
 */
class OngkirService implements OngkirInterface
{
    public function hitung(array $alamat): array
    {
        $kota = strtolower($alamat['kota'] ?? '');
        $kotaPadang = str_contains($kota, 'padang');

        $dasar = $kotaPadang ? 8000 : 15000;
        $tambahan = $kotaPadang ? 0 : min(20000, (int) ceil(max(0, mb_strlen($kota) - 6)) * 1500);

        return [
            [
                'kode' => 'jne_reg',
                'kurir' => 'JNE',
                'layanan' => 'Reguler',
                'biaya' => $dasar + $tambahan,
                'estimasi' => $kotaPadang ? '1-2 hari' : '3-5 hari',
            ],
            [
                'kode' => 'jnt_reg',
                'kurir' => 'J&T Express',
                'layanan' => 'Ekonomi',
                'biaya' => $dasar - 2000 + $tambahan,
                'estimasi' => $kotaPadang ? '2-3 hari' : '4-6 hari',
            ],
            [
                'kode' => 'sicepat_reg',
                'kurir' => 'SiCepat',
                'layanan' => 'Reguler',
                'biaya' => $dasar + 2000 + $tambahan,
                'estimasi' => $kotaPadang ? '1-2 hari' : '3-4 hari',
            ],
        ];
    }
}
