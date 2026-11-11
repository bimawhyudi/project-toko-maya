<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_PELANGGAN = 'pelanggan';
    public const ROLE_KASIR = 'kasir';
    public const ROLE_ADMIN_GUDANG = 'admin_gudang';
    public const ROLE_PEMILIK = 'pemilik';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_hp',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'alamat' => 'array',
        ];
    }

    // Breeze memakai kolom "name" untuk tampilan; kita arahkan ke "nama".
    public function getNameAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function keranjangAktif(): Pesanan
    {
        return $this->pesanan()
            ->where('status_pesanan', Pesanan::STATUS_KERANJANG)
            ->first() ?? $this->pesanan()->create([
                'status_pesanan' => Pesanan::STATUS_KERANJANG,
            ]);
    }

    public function jumlahItemKeranjang(): int
    {
        return (int) $this->pesanan()
            ->where('status_pesanan', Pesanan::STATUS_KERANJANG)
            ->withSum('detail as total_item', 'jumlah')
            ->get()
            ->sum('total_item');
    }

    /* ===================== BUKU ALAMAT (users.alamat, JSON) ===================== */
    public function tambahAlamat(array $data): array
    {
        $daftar = $this->alamat ?? [];
        $baru = [
            'id' => (string) Str::uuid(),
            'label' => $data['label'] ?? 'Rumah',
            'nama_penerima' => $data['nama_penerima'],
            'no_hp' => $data['no_hp'] ?? $this->no_hp,
            'provinsi' => $data['provinsi'],
            'kota' => $data['kota'],
            'kecamatan' => $data['kecamatan'],
            'kode_pos' => $data['kode_pos'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'id_kota_api' => $data['id_kota_api'] ?? null,
            'is_default' => false,
        ];

        if (empty($daftar)) {
            $baru['is_default'] = true;
        } elseif (! empty($data['is_default'])) {
            $daftar = array_map(fn ($a) => ['is_default' => false] + $a, $daftar);
            $baru['is_default'] = true;
        }

        $daftar[] = $baru;
        $this->forceFill(['alamat' => $daftar])->save();

        return $baru;
    }

    public function ubahAlamat(string $idAlamat, array $data): ?array
    {
        $daftar = $this->alamat ?? [];
        $diubah = null;

        foreach ($daftar as $i => $alamat) {
            if ($alamat['id'] === $idAlamat) {
                $daftar[$i] = array_merge($alamat, $data, ['id' => $idAlamat]);
                if (! empty($data['is_default'])) {
                    foreach ($daftar as $j => $a) {
                        $daftar[$j]['is_default'] = $j === $i;
                    }
                }
                $diubah = $daftar[$i];
                break;
            }
        }

        if ($diubah) {
            $this->forceFill(['alamat' => $daftar])->save();
        }

        return $diubah;
    }

    public function hapusAlamat(string $idAlamat): bool
    {
        $daftar = $this->alamat ?? [];
        $sisa = array_values(array_filter($daftar, fn ($a) => $a['id'] !== $idAlamat));

        if (count($sisa) === count($daftar)) {
            return false;
        }

        if ($sisa && ! array_filter($sisa, fn ($a) => $a['is_default'])) {
            $sisa[0]['is_default'] = true;
        }

        $this->forceFill(['alamat' => $sisa])->save();

        return true;
    }

    public function setAlamatDefault(string $idAlamat): bool
    {
        $daftar = $this->alamat ?? [];
        $ketemu = false;

        foreach ($daftar as $i => $alamat) {
            $daftar[$i]['is_default'] = $alamat['id'] === $idAlamat;
            $ketemu = $ketemu || $daftar[$i]['is_default'];
        }

        if ($ketemu) {
            $this->forceFill(['alamat' => $daftar])->save();
        }

        return $ketemu;
    }

    public function alamatDefault(): ?array
    {
        foreach ($this->alamat ?? [] as $alamat) {
            if ($alamat['is_default']) {
                return $alamat;
            }
        }

        return $this->alamat[0] ?? null;
    }
}