<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

/**
 * Layanan pembayaran Midtrans Snap. Kunci hanya dibaca dari .env —
 * tidak pernah ditulis ke dalam kode.
 */
class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function aktif(): bool
    {
        return config('midtrans.server_key') !== '' && config('midtrans.client_key') !== '';
    }

    /**
     * Buat token Snap untuk satu pesanan. order_id = kode_pesanan.
     */
    public function buatSnapToken(Pesanan $pesanan): string
    {
        $itemDetails = $pesanan->detail->map(fn ($d) => [
            'id' => (string) $d->produk_id,
            'price' => (float) $d->harga,
            'quantity' => $d->jumlah,
            'name' => mb_substr($d->produk->nama_produk, 0, 50),
        ])->values()->all();

        if ((float) $pesanan->ongkir > 0) {
            $itemDetails[] = [
                'id' => 'ONGKIR',
                'price' => (float) $pesanan->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos kirim '.($pesanan->ekspedisi ?? ''),
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $pesanan->kode_pesanan,
                'gross_amount' => (float) $pesanan->totalTagihan,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $pesanan->user?->nama ?? 'Pelanggan',
                'email' => $pesanan->user?->email ?? 'pelanggan@tokomaya.test',
                'phone' => $pesanan->user?->no_hp ?? '',
            ],
            'expiry' => [
                'unit' => 'hour',
                'duration' => (int) config('toko.jam_kedaluwarsa', 24),
            ],
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Verifikasi signature_key webhook:
     * sha512(order_id + status_code + gross_amount + kunci).
     * Mendukung dashboard lama (kunci = server key) maupun dashboard baru
     * (kunci = "Signature Secret Key" yang terpisah dari server key API).
     */
    public function signatureValid(array $payload): bool
    {
        $dariPayload = $payload['signature_key'] ?? '';

        foreach ($this->kandidatKunciSignature() as $kunci) {
            $diharapkan = hash('sha512',
                ($payload['order_id'] ?? '').
                ($payload['status_code'] ?? '').
                ($payload['gross_amount'] ?? '').
                $kunci
            );

            if (hash_equals($diharapkan, $dariPayload)) {
                return true;
            }
        }

        return false;
    }

    private function kandidatKunciSignature(): array
    {
        $kandidat = array_filter([
            config('midtrans.signature_key') ?: null,
            config('midtrans.server_key') ?: null,
        ]);

        return array_values(array_unique($kandidat));
    }

    /**
     * Terjemahkan status webhook Midtrans ke status pesanan.
     * Dipanggil hanya setelah signature valid.
     */
    public function terapkanStatus(array $payload): void
    {
        $pesanan = Pesanan::where('kode_pesanan', $payload['order_id'] ?? '')->first();

        if (! $pesanan) {
            Log::warning('Webhook Midtrans: pesanan tidak ditemukan', ['order_id' => $payload['order_id'] ?? null]);

            return;
        }

        $status = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? 'accept';

        match (true) {
            in_array($status, ['capture', 'settlement'], true) && $fraud === 'accept' => $pesanan->tandaiDiprosesDanPotongStok(),
            in_array($status, ['expire', 'cancel', 'deny'], true) => $pesanan->tandaiBatal(),
            default => null, // pending / lainnya: tidak mengubah status
        };
    }
}
