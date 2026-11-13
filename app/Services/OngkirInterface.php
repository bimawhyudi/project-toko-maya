<?php

namespace App\Services;

/**
 * Kontrak layanan ongkir. Driver baru (mis. RajaOngkir/Komerce) cukup
 * mengimplementasikan interface ini lalu diganti di AppServiceProvider.
 */
interface OngkirInterface
{
    /**
     * Hitung pilihan ongkir untuk satu alamat tujuan.
     *
     * @param  array{provinsi:?string, kota:?string, id_kota_api:?string, kecamatan:?string}  $alamat
     * @return array<int, array{kode:string, kurir:string, layanan:string, biaya:float, estimasi:string}>
     */
    public function hitung(array $alamat): array;
}
