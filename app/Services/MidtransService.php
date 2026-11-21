<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;


class MidtransService
{
    /**
     * Metode pembayaran di aplikasi -> payment_type Core API Midtrans.
     * VA semua bank memakai payment_type "bank_transfer" (beda dengan Snap yang punya bca_va/bni_va/bri_va).
     */
    private const PEMBAYARAN_KE_CORE = [
        'midtrans_qris' => 'qris',
        'midtrans_va_bca' => 'bank_transfer',
        'midtrans_va_bni' => 'bank_transfer',
        'midtrans_va_bri' => 'bank_transfer',
    ];

    /**
     * Bank tujuan Virtual Account untuk metode VA.
     */
    private function bankVa(string $metodePembayaran): string
    {
        return match ($metodePembayaran) {
            'midtrans_va_bca' => 'bca',
            'midtrans_va_bni' => 'bni',
            'midtrans_va_bri' => 'bri',
        };
    }

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
     * Buat pembayaran langsung via Core API sesuai metode yang dipilih saat checkout.
     * Hasil charge (nomor Virtual Account / string QRIS, dsb) disimpan di kolom pembayaran.
     */
    public function buatPembayaran(Pesanan $pesanan): array
    {
        $paymentType = self::PEMBAYARAN_KE_CORE[$pesanan->metode_pembayaran] ?? null;

        if ($paymentType === null) {
            throw new \InvalidArgumentException('Metode pembayaran tidak dikenal: ' . $pesanan->metode_pembayaran);
        }

        $params = array_merge([
            'payment_type' => $paymentType,
            'transaction_details' => [
                'order_id' => $pesanan->kode_pesanan,
                'gross_amount' => (float) $pesanan->totalTagihan,
            ],
            'item_details' => $this->susunItemDetails($pesanan),
            'customer_details' => $this->susunCustomerDetails($pesanan),
            // Core API memakai custom_expiry (bukan "expiry" seperti Snap)
            'custom_expiry' => [
                'expiry_duration' => (int) config('toko.jam_kedaluwarsa', 24),
                'unit' => 'hour',
            ],
        ], $this->payloadPaymentType($paymentType, $pesanan));

        $res = CoreApi::charge($params);
        $hasil = json_decode(json_encode($res), true) ?: [];

        $detail = [
            'transaction_id' => $hasil['transaction_id'] ?? null,
            'payment_type' => $hasil['payment_type'] ?? $paymentType,
            'transaction_status' => $hasil['transaction_status'] ?? 'pending',
            'expiry_time' => $hasil['expiry_time'] ?? null,
            'charged_at' => now()->toDateTimeString(),
        ];

        // Virtual Account: bank + nomor VA
        if (! empty($hasil['va_numbers'][0])) {
            $detail['bank'] = $hasil['va_numbers'][0]['bank'] ?? null;
            $detail['va_number'] = $hasil['va_numbers'][0]['va_number'] ?? null;
        } elseif (! empty($hasil['permata_va_number'])) {
            $detail['bank'] = 'permata';
            $detail['va_number'] = $hasil['permata_va_number'];
        }

        // QRIS: string QR + URL gambar QR siap tampil
        if (! empty($hasil['qr_string'])) {
            $detail['qr_string'] = $hasil['qr_string'];
        }
        foreach (($hasil['actions'] ?? []) as $aksi) {
            if (($aksi['name'] ?? '') === 'generate-qr-code') {
                $detail['qr_url'] = $aksi['url'] ?? null;
            }
        }

        $pesanan->forceFill(['pembayaran' => $detail])->save();

        return $detail;
    }

    /**
     * Payload tambahan spesifik per payment_type Core API.
     */
    private function payloadPaymentType(string $paymentType, Pesanan $pesanan): array
    {
        return match ($paymentType) {
            // QRIS Core API wajib memilih acquirer (saat ini gopay)
            'qris' => ['qris' => ['acquirer' => 'gopay']],
            // VA semua bank lewat bank_transfer + nama bank
            'bank_transfer' => ['bank_transfer' => ['bank' => $this->bankVa($pesanan->metode_pembayaran)]],
            default => [],
        };
    }

    private function susunItemDetails(Pesanan $pesanan): array
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

        return $itemDetails;
    }

    private function susunCustomerDetails(Pesanan $pesanan): array
    {
        return [
            'first_name' => $pesanan->user?->nama ?? 'Pelanggan',
            'email' => $pesanan->user?->email ?? 'pelanggan@tokomaya.test',
            'phone' => $pesanan->user?->no_hp ?? '',
        ];
    }

    public function sinkronkanStatus(Pesanan $pesanan): void
    {
        if (! $this->aktif() || $pesanan->status_pesanan !== Pesanan::STATUS_MENUNGGU_PEMBAYARAN) {
            return;
        }

        try {
            $hasil = Transaction::status($pesanan->kode_pesanan);
            $payload = json_decode(json_encode($hasil), true) ?: [];
            $payload['order_id'] ??= $pesanan->kode_pesanan;

            $this->terapkanStatus($payload);
            $pesanan->refresh();
        } catch (\Throwable $e) {
            
        }
    }

 
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