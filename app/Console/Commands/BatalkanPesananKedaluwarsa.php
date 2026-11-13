<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use Illuminate\Console\Command;

class BatalkanPesananKedaluwarsa extends Command
{
    protected $signature = 'pesanan:batalkan-kedaluwarsa';

    protected $description = 'Membatalkan pesanan daring yang melewati batas waktu pembayaran (expired_at)';

    public function handle(): int
    {
        $jumlah = Pesanan::where('status_pesanan', Pesanan::STATUS_MENUNGGU_PEMBAYARAN)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->get()
            ->each(fn (Pesanan $p) => $p->tandaiBatal())
            ->count();

        $this->info("{$jumlah} pesanan kedaluwarsa dibatalkan.");

        return self::SUCCESS;
    }
}
