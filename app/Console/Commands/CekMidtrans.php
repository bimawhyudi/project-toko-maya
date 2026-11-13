<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Alat bantu memeriksa kesiapan kunci Midtrans tanpa harus membuka halaman.
 * Jalankan: php artisan midtrans:cek
 */
class CekMidtrans extends Command
{
    protected $signature = 'midtrans:cek';

    protected $description = 'Menguji kunci Midtrans (sandbox/production) dengan panggilan API nyata';

    public function handle(): int
    {
        $serverKey = (string) config('midtrans.server_key');
        $clientKey = (string) config('midtrans.client_key');
        $produksi = (bool) config('midtrans.is_production');

        $this->info('=== Pemeriksaan Kunci Midtrans ===');
        $this->line('Lingkungan   : '.($produksi ? 'PRODUCTION' : 'SANDBOX'));
        $this->line('Server key   : '.($serverKey === ''
            ? '(KOSONG)'
            : substr($serverKey, 0, 14).'… ('.strlen($serverKey).' karakter)'));
        $this->line('Client key   : '.($clientKey === ''
            ? '(KOSONG)'
            : substr($clientKey, 0, 14).'… ('.strlen($clientKey).' karakter)'));

        if ($serverKey === '' || $clientKey === '') {
            $this->error('Kunci belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di .env lalu jalankan php artisan config:clear.');

            return self::FAILURE;
        }

        $host = $produksi ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        $respons = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post("$host/v2/charge", [
                'payment_type' => 'bank_transfer',
                'transaction_details' => [
                    'order_id' => 'CEK-KUNCI-'.now()->format('YmdHis'),
                    'gross_amount' => 10000,
                ],
                'bank_transfer' => ['bank' => 'bca'],
            ]);

        if ($respons->status() === 401) {
            $this->error('Kunci DITOLAK Midtrans (401 Unauthorized).');
            $this->line('');
            $this->line('Jika Anda memakai dashboard sandbox BARU (dashboard.sandbox.midtrans.com),');
            $this->line('perhatikan: yang dipakai untuk panggilan API adalah kunci dari bagian');
            $this->line('"Server Keys" — BUKAN "Signature Secret Key".');
            $this->line('');
            $this->line('Langkah yang benar:');
            $this->line('  1. Buka dashboard.sandbox.midtrans.com -> Settings -> Access Keys.');
            $this->line('  2. Scroll ke bagian "Server Keys" -> klik "Generate server key".');
            $this->line('     (pilih scope/izin bawaan bila ditanya, lalu simpan)');
            $this->line('  3. Salin kunci yang terbentuk -> tempel ke .env pada MIDTRANS_SERVER_KEY.');
            $this->line('  4. Nilai "Signature Secret Key" boleh ditempel terpisah ke');
            $this->line('     MIDTRANS_SIGNATURE_KEY (dipakai untuk verifikasi webhook).');
            $this->line('  5. Jalankan: php artisan config:clear  lalu  php artisan midtrans:cek');
            $this->line('');
            $this->line('Di dashboard LAMA (dashboard.midtrans.com), Server Key ada di');
            $this->line('Settings -> Access Keys -> tab Sandbox, tanpa perlu generate.');

            return self::FAILURE;
        }

        if ($respons->successful()) {
            $orderId = $respons->json('order_id');
            $this->info('Kunci VALID — API Midtrans merespons normal.');
            $this->line('Contoh transaksi uji terbentuk: '.$orderId.' (VA BCA '.
                ($respons->json('va_numbers.0.va_number') ?? '-').')');
            $this->line('Sistem siap menerima pembayaran. Jangan lupa konfigurasikan Notification URL:');
            $this->line(($produksi ? 'https://domain-anda' : 'https://domain-tunnel-anda (mis. ngrok)').'/midtrans/webhook');

            return self::SUCCESS;
        }

        $this->warn('Respons API: HTTP '.$respons->status().' — '.$respons->body());

        return self::FAILURE;
    }
}
