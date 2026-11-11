<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private OngkirInterface $ongkir) {}

    /**
     * Ubah keranjang menjadi pesanan "menunggu_pembayaran" dalam satu transaksi:
     * validasi ulang stok, harga diperbarui ke harga terkini, kode pesanan
     * dibuat, ongkir dihitung, dan alamat disalin sebagai snapshot teks.
     *
     * @param  array{metode_pemenuhan:string, alamat_id?:?string, ekspedisi?:?string, metode_pembayaran:?string}  $data
     */
    public function proses(User $user, array $data): Pesanan
    {
        return DB::transaction(function () use ($user, $data) {
            $keranjang = $user->pesanan()
                ->where('status_pesanan', Pesanan::STATUS_KERANJANG)
                ->lockForUpdate()
                ->first();

            if (! $keranjang || $keranjang->detail()->count() === 0) {
                throw ValidationException::withMessages([
                    'keranjang' => 'Keranjang Anda kosong. Tambahkan produk terlebih dahulu.',
                ]);
            }

            // Kunci baris produk agar stok tidak diubah proses lain saat validasi.
            $produkIds = $keranjang->detail->pluck('produk_id');
            $produk = Produk::whereIn('id', $produkIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($keranjang->detail as $d) {
                $p = $produk[$d->produk_id];
                if ($p->stok < $d->jumlah) {
                    throw ValidationException::withMessages([
                        'stok' => "Stok \"{$p->nama_produk}\" tinggal {$p->stok} buah. Silakan sesuaikan jumlah di keranjang.",
                    ]);
                }
            }

            $keranjang->muatUlangHarga();

            $ongkir = 0.0;
            $alamatTeks = null;
            $ekspedisi = null;

            if ($data['metode_pemenuhan'] === Pesanan::PEMENUHAN_KURIR) {
                $alamat = collect($user->alamat ?? [])->firstWhere('id', $data['alamat_id'] ?? '');
                if (! $alamat) {
                    throw ValidationException::withMessages([
                        'alamat_id' => 'Pilih salah satu alamat pengiriman yang tersimpan.',
                    ]);
                }

                $pilihan = collect($this->ongkir->hitung($alamat));
                $terpilih = $pilihan->firstWhere('kode', $data['ekspedisi'] ?? '');
                if (! $terpilih) {
                    throw ValidationException::withMessages([
                        'ekspedisi' => 'Pilih layanan ekspedisi yang tersedia.',
                    ]);
                }

                $ongkir = (float) $terpilih['biaya'];
                $ekspedisi = $terpilih['kurir'].' '.$terpilih['layanan'];
                $alamatTeks = $this->formatAlamat($alamat);
            }

            $keranjang->forceFill([
                'kode_pesanan' => $this->buatKode(),
                'tanggal_pesanan' => now(),
                'total_harga' => $keranjang->detail()->sum('subtotal'),
                'ongkir' => $ongkir,
                'metode_pemenuhan' => $data['metode_pemenuhan'],
                'alamat_pengiriman' => $alamatTeks,
                'ekspedisi' => $ekspedisi,
                'metode_pembayaran' => $data['metode_pembayaran'] ?? null,
                'status_pesanan' => Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
                'expired_at' => now()->addHours(config('toko.jam_kedaluwarsa', 24)),
            ])->save();

            return $keranjang;
        });
    }

    private function buatKode(): string
    {
        do {
            $kode = 'TM-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Pesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }

    private function formatAlamat(array $a): string
    {
        return sprintf(
            "%s (%s)\n%s, Kec. %s\n%s, %s\nNo. HP: %s",
            $a['alamat_lengkap'],
            $a['label'],
            $a['kota'],
            $a['kecamatan'],
            $a['provinsi'],
            $a['kode_pos'],
            $a['no_hp']
        );
    }
}
