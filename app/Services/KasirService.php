<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Alur C (kasir): transaksi fisik walk-in. Stok dipotong langsung saat
 * kasir mengonfirmasi transaksi; pesanan langsung berstatus "selesai"
 * dengan user_id null (pembeli fisik tanpa akun).
 */
class KasirService
{
    /**
     * @param  array<int, array{produk_id:int, jumlah:int}>  $items
     * @param  string  $metode  tunai|qris|debit
     */
    public function transaksiFisik(?int $kasirId, array $items, string $metode): Pesanan
    {
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'Nota masih kosong. Pilih barang terlebih dahulu.']);
        }

        if (! in_array($metode, ['tunai', 'qris', 'debit'], true)) {
            throw ValidationException::withMessages(['metode' => 'Metode pembayaran tidak dikenal.']);
        }

        return DB::transaction(function () use ($items, $metode) {
            $ids = collect($items)->pluck('produk_id')->unique();
            $produk = Produk::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $pesanan = Pesanan::create([
                'user_id' => null,
                'kode_pesanan' => $this->buatKode(),
                'tanggal_pesanan' => now(),
                'metode_pemenuhan' => Pesanan::PEMENUHAN_BELI_LANGSUNG,
                'metode_pembayaran' => $metode,
                'status_pesanan' => Pesanan::STATUS_SELESAI,
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $jumlah = max(1, (int) ($item['jumlah'] ?? 1));
                $p = $produk[$item['produk_id']] ?? null;

                if (! $p) {
                    throw ValidationException::withMessages(['items' => 'Ada produk yang tidak dikenal. Muat ulang halaman.']);
                }
                if ($p->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'items' => "Stok \"{$p->nama_produk}\" tinggal {$p->stok} buah, kurang untuk nota ini.",
                    ]);
                }

                $p->decrement('stok', $jumlah);

                $pesanan->detail()->create([
                    'produk_id' => $p->id,
                    'jumlah' => $jumlah,
                    'harga' => $p->harga,
                    'subtotal' => $p->harga * $jumlah,
                ]);

                $total += $p->harga * $jumlah;
            }

            $pesanan->forceFill(['total_harga' => $total])->save();

            return $pesanan->load('detail.produk');
        });
    }

    private function buatKode(): string
    {
        do {
            $kode = 'TM-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Pesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }
}
