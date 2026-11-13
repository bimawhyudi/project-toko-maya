{{-- Template PDF laporan penjualan (dipakai dompdf) --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; margin: 0; }
        .sub { color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
        th { background: #E4ECFF; font-size: 10px; }
        .kanan { text-align: right; }
        .rekap { margin-top: 10px; padding: 8px; background: #F3F8FE; border: 1px solid #C7D2EA; }
        .rekap div { margin: 2px 0; font-weight: bold; }
        .footer { margin-top: 18px; color: #777; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan — {{ config('toko.nama_toko') }}</h1>
    <p class="sub">{{ config('toko.alamat') }} · Dicetak {{ now()->format('d M Y H:i') }}</p>

    <div class="rekap">
        @foreach ($rekap as $r)
            <div>{{ $r }}</div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Kanal</th>
                <th>Metode</th><th class="kanan">Ongkir</th><th class="kanan">Total</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pesanan as $p)
                <tr>
                    <td>{{ $p->kode_pesanan }}</td>
                    <td>{{ $p->tanggal_pesanan?->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->user?->nama ?? 'Walk-in' }}</td>
                    <td>{{ $p->metode_pemenuhan === 'beli_langsung_kasir' ? 'Kasir POS' : ($p->metode_pemenuhan === 'kurir' ? 'Online · Kurir' : 'Online · Ambil') }}</td>
                    <td>{{ $p->metode_pembayaran ?? '-' }}</td>
                    <td class="kanan">{{ number_format($p->ongkir, 0, ',', '.') }}</td>
                    <td class="kanan">{{ number_format($p->total_harga + $p->ongkir, 0, ',', '.') }}</td>
                    <td>{{ $p->status_pesanan }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dokumen dibuat otomatis oleh Sistem Toko Maya — dilaporkan kepada Project Manager.</p>
</body>
</html>
