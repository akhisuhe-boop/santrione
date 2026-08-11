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

        /*
        |--------------------------------------------------------------------------
        | HARD DELETE BERSIH
        |--------------------------------------------------------------------------
        |
        | Foreign key di database ini campuran cascade/nullOnDelete —
        | sebagian tabel (Lembaga, Kelas, Siswa, dst) otomatis ikut
        | kehapus lewat cascade bawaan, tapi beberapa tabel LAIN
        | (User, Pegawai, Kas, Asrama, Ppdb) sengaja di-set nullOnDelete
        | supaya tidak gagal migrasi dulu — efeknya kalau dibiarkan,
        | baris itu jadi "nyangkut" (yayasan_id/lembaga_id kosong) tanpa
        | pernah kehapus, numpuk di server tanpa guna.
        |
        | Hook ini membersihkan SEMUA sisa itu secara eksplisit SEBELUM
        | proses delete utama jalan, supaya hard-delete Yayasan benar-
        | benar bersih tanpa sisa sama sekali.
        */
        static::deleting(function (self $yayasan) {

            $lembagaIds = $yayasan->lembagas()->pluck('id');

            // Pegawai yang kerja di lembaga2 yayasan ini (pegawai hanya
            // boleh kerja dalam 1 yayasan yang sama, lihat catatan di
            // Pegawai::applyTenantScope) — aman dihapus bersama.
            $pegawaiIds = \Illuminate\Support\Facades\DB::table('pegawai_lembaga')
                ->whereIn('lembaga_id', $lembagaIds)
                ->pluck('pegawai_id')
                ->unique();

            \App\Models\Pegawai::withoutGlobalScopes()
                ->whereIn('id', $pegawaiIds)
                ->get()
                ->each(fn ($p) => $p->delete());

            // User (akun admin panel) yang terhubung ke yayasan ini.
            \App\Models\User::withoutGlobalScopes()
                ->where('yayasan_id', $yayasan->id)
                ->delete();

            // Tabel yang FK-nya nullOnDelete ke lembaga — hapus manual
            // dulu selagi lembaga_id-nya masih terisi.
            foreach (['kas', 'asramas', 'ppdbs'] as $table) {
                \Illuminate\Support\Facades\DB::table($table)
                    ->whereIn('lembaga_id', $lembagaIds)
                    ->delete();
            }

            // Data langganan/pembayaran yayasan ini (riwayat billing) —
            // ikut dibersihkan juga supaya benar2 tuntas.
            $subscriptionIds = $yayasan->subscriptions()->pluck('id');
            \Illuminate\Support\Facades\DB::table('subscription_payments')
                ->whereIn('subscription_id', $subscriptionIds)
                ->delete();
            $yayasan->subscriptions()->delete();
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
        // REVISI: sebelumnya trial = buka SEMUA menu tanpa syarat
        // (bypass total). Diubah supaya trial & active diperlakukan
        // SAMA untuk keperluan gating -- modul yang menentukan menu
        // terbuka, bukan status trial/active. Ini penting supaya
        // halaman "Langganan" (pilih modul) benar-benar berarti sejak
        // hari pertama, bukan cuma preferensi billing pasca-trial.
        if (! in_array($this->status, ['trial', 'active'], true)) {
            return false;
        }

        $subscription = $this->activeSubscription();

        // Grandfathered: tidak pernah ada baris subscription sama
        // sekali -> anggap semua fitur terbuka. Ini murni untuk
        // Yayasan LAMA yang sudah ada sebelum sistem billing baru ini
        // (belum pernah punya Subscription record apapun) -- Yayasan
        // BARU selalu langsung dapat Subscription otomatis saat
        // daftar (lihat PublicRegistrationController), jadi baris ini
        // tidak berlaku untuk mereka.
        if (! $this->subscriptions()->exists()) {
            return true;
        }

        if ($subscription && $subscription->plan?->hasFeature($key)) {
            return true;
        }

        // Skema à la carte (per-Lembaga): kalau paket dasar TIDAK
        // membuka fitur ini tapi ADA minimal 1 Lembaga di yayasan ini
        // yang mengaktifkan modul tersebut (lihat LembagaModule/
        // ModulePrice), tetap buka menunya. Ini gating di level
        // Yayasan (menu sidebar tampil untuk semua Lembaga di bawah
        // yayasan itu), BUKAN scoping data per-Lembaga — kalau ke
        // depan dibutuhkan penyembunyian menu yang benar-benar
        // berbeda per Lembaga dalam satu Yayasan, itu perubahan
        // arsitektur terpisah (tenant panel saat ini = Yayasan).
        return $this->lembagas()
            ->whereHas('activeModules.modulePrice', fn ($q) => $q->where('key', $key))
            ->exists();
    }
}
