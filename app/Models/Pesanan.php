<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pesanan extends Model
{
    public const STATUS_KERANJANG = 'keranjang';
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';

    public const PEMENUHAN_AMBIL_DI_TOKO = 'ambil_di_toko';
    public const PEMENUHAN_KURIR = 'kurir';
    public const PEMENUHAN_BELI_LANGSUNG = 'beli_langsung_kasir';

    protected $table = 'pesanan';

    protected $casts = [
        'tanggal_pesanan' => 'datetime',
        'expired_at' => 'datetime',
        'total_harga' => 'decimal:2',
        'ongkir' => 'decimal:2',
    ];

    protected $fillable = [
        'user_id',
        'kode_pesanan',
        'tanggal_pesanan',
        'total_harga',
        'ongkir',
        'metode_pemenuhan',
        'alamat_pengiriman',
        'ekspedisi',
        'no_resi',
        'metode_pembayaran',
        'status_pesanan',
        'expired_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->detail->sum('subtotal');
    }

    public function getTotalTagihanAttribute(): float
    {
        return (float) $this->total_harga + (float) $this->ongkir;
    }

    public function scopeBukanKeranjang($query)
    {
        return $query->where('status_pesanan', '!=', self::STATUS_KERANJANG);
    }

    public function scopeTerbaru($query)
    {
        return $query->orderByDesc('tanggal_pesanan')->orderByDesc('id');
    }

    /* ===================== KERANJANG ===================== */
    public function tambahProduk(Produk $produk, int $jumlah = 1): void
    {
        $jumlah = max(1, $jumlah);
        $detail = $this->detail()->firstOrNew(['produk_id' => $produk->id]);
        $detail->jumlah = ($detail->exists ? $detail->jumlah : 0) + $jumlah;
        $detail->harga = $produk->harga;
        $detail->subtotal = $detail->harga * $detail->jumlah;
        $detail->save();

        $this->hitungUlangTotal();
    }

    public function ubahJumlah(DetailPesanan $detail, int $jumlah): void
    {
        $jumlah = max(1, min($jumlah, $detail->produk->stok));
        $detail->jumlah = $jumlah;
        $detail->subtotal = $detail->harga * $jumlah;
        $detail->save();

        $this->hitungUlangTotal();
    }

    public function hapusDetail(DetailPesanan $detail): void
    {
        $detail->delete();
        $this->hitungUlangTotal();
    }

    public function kosongkan(): void
    {
        $this->detail()->delete();
        $this->hitungUlangTotal();
    }

    public function hitungUlangTotal(): void
    {
        $this->forceFill(['total_harga' => $this->detail()->sum('subtotal')])->save();
    }

    public function muatUlangHarga(): void
    {
        foreach ($this->detail as $d) {
            $d->harga = $d->produk->harga;
            $d->subtotal = $d->harga * $d->jumlah;
            $d->save();
        }

        $this->hitungUlangTotal();
    }

    /* ===================== STOK & STATUS ===================== */
    public function tandaiDiprosesDanPotongStok(): void
    {
        DB::transaction(function () {
            $p = self::whereKey($this->id)->lockForUpdate()->first() ?? $this;
            if ($p->status_pesanan !== self::STATUS_MENUNGGU_PEMBAYARAN) {
                return;
            }

            foreach ($p->detail()->with('produk')->get() as $d) {
                $produk = Produk::whereKey($d->produk_id)->lockForUpdate()->first();
                if ($produk && $produk->stok >= $d->jumlah) {
                    $produk->decrement('stok', $d->jumlah);
                }
            }

            $p->forceFill(['status_pesanan' => self::STATUS_DIPROSES])->save();
        });
    }

    public function tandaiBatal(): void
    {
        DB::transaction(function () {
            $p = self::whereKey($this->id)->lockForUpdate()->first() ?? $this;
            if ($p->status_pesanan === self::STATUS_MENUNGGU_PEMBAYARAN) {
                $p->forceFill(['status_pesanan' => self::STATUS_BATAL])->save();
            }
        });
    }
}