<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToTenant;
use Filament\Models\Contracts\HasName;

class Yayasan extends Model implements HasName
{
    use BelongsToTenant;

    // Filament butuh tahu kolom mana yang jadi "nama tampilan" tenant —
    // tabel kita pakai 'nama', bukan 'name' bawaan default Filament.
    public function getFilamentName(): string
    {
        return $this->nama ?? 'Yayasan';
    }

    // Yayasan adalah tenant itu sendiri — filter langsung berdasarkan id-nya
    // sendiri (bukan lewat relasi lembaga), harus sama dengan yayasan_id
    // milik user yang login.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('id', $yayasanId);
    }

    protected static function booted(): void
    {
        static::creating(function (self $yayasan) {
            if (empty($yayasan->slug) && ! empty($yayasan->nama)) {
                $base = Str::slug($yayasan->nama);
                $slug = $base;
                $i = 1;

                while (
                    static::withoutGlobalScopes()->where('slug', $slug)->exists()
                ) {
                    $slug = $base . '-' . $i;
                    $i++;
                }

                $yayasan->slug = $slug;
            }

            // Yayasan baru otomatis mulai masa trial (Fase 5 — SaaS
            // billing), kecuali status/trial_ends_at sudah di-set
            // eksplisit (mis. dibuat manual oleh platform admin dan
            // ingin langsung 'active').
            if (empty($yayasan->status)) {
                $yayasan->status = 'trial';
            }

            if ($yayasan->status === 'trial' && empty($yayasan->trial_ends_at)) {
                $yayasan->trial_ends_at = now()->addDays(
                    config('subscription.trial_days', 14)
                );
            }
        });
    }

    protected $fillable = [
    'nama',
    'slug',
    'ketua',
    'logo',
    'alamat',
    'telepon',
    'email',
    'website',
    'domain',
    'status',
    'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function lembagas()
    {
        return $this->hasMany(Lembaga::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Langganan yang sedang berjalan (kalau ada). Yayasan bisa punya
     * banyak baris Subscription seiring waktu (riwayat perpanjangan),
     * ini ambil yang statusnya 'active' dan belum lewat tanggal
     * berakhir.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('berakhir_pada', '>', now())
            ->latest('berakhir_pada')
            ->first();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function trialDaysLeft(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Boleh dipakai (akses panel dibuka) atau tidak. `status` adalah
     * SATU-SATUNYA sumber kebenaran di sini secara sengaja:
     *
     *  - 'trial'     -> boleh, SELAMA trial_ends_at belum lewat
     *  - 'active'    -> boleh, tanpa syarat lain (mencakup yayasan lama
     *                   yang di-grandfather saat fitur ini dibuat, DAN
     *                   yayasan yang langganannya sedang berjalan)
     *  - 'suspended' / 'cancelled' -> tidak boleh
     *
     * Baris di tabel `subscriptions` adalah CATATAN/riwayat pembayaran
     * (dipakai command terjadwal untuk MEMUTUSKAN kapan status di atas
     * berubah), bukan pengecekan real-time terpisah di sini — supaya
     * tidak ada 2 sumber kebenaran yang bisa saling kontradiksi.
     */
    public function hasAccess(): bool
    {
        if ($this->status === 'trial') {
            return $this->isOnTrial();
        }

        return $this->status === 'active';
    }

    /**
     * Cek apakah yayasan ini boleh pakai 1 fitur premium tertentu
     * (lihat App\Support\FeatureGate untuk daftar key-nya).
     *
     *  - Masih TRIAL           -> semua fitur premium terbuka (supaya
     *                             calon customer coba pengalaman penuh)
     *  - ACTIVE tanpa langganan
     *    sama sekali (grandfathered, yayasan lama sebelum ada billing)
     *                          -> semua fitur premium terbuka juga,
     *                             supaya tidak ada yang tiba-tiba
     *                             kehilangan akses fitur yang sudah
     *                             dipakai sebelumnya
     *  - ACTIVE dengan langganan berbayar -> ikut daftar fitur di
     *                             paket yang lagi aktif
     *  - selain itu (suspended/cancelled) -> akses panel sudah
     *                             ke-block duluan lewat hasAccess(),
     *                             jadi ini praktis tidak pernah dicek
     */
    public function hasFeature(string $key): bool
    {
        if ($this->status === 'trial') {
            return $this->isOnTrial();
        }

        if ($this->status !== 'active') {
            return false;
        }

        $subscription = $this->activeSubscription();

        // Grandfathered: tidak pernah ada baris subscription sama
        // sekali -> anggap semua fitur terbuka.
        if (! $this->subscriptions()->exists()) {
            return true;
        }

        if (! $subscription) {
            return false;
        }

        return (bool) $subscription->plan?->hasFeature($key);
    }
}
