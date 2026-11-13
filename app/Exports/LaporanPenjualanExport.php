<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Baris laporan penjualan per pesanan (sudah lunas, tanpa keranjang).
 */
class LaporanPenjualanExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(private array $baris, private array $rekap) {}

    public function array(): array
    {
        return array_merge([$this->rekap, []], $this->baris);
    }

    public function headings(): array
    {
        return ['Kode Pesanan', 'Tanggal', 'Pelanggan', 'Kanal', 'Metode Pembayaran', 'Ongkir (Rp)', 'Total (Rp)', 'Status'];
    }
}
