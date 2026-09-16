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
    'promo_pendaftaran_persen',
    'promo_pendaftaran_teks',
    'promo_pendaftaran_terpakai',
    'modul_snapshot_sebelum_full',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'promo_pendaftaran_terpakai' => 'boolean',
            'modul_snapshot_sebelum_full' => 'array',
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
    // Cache in-memory SEKALI PER OBJEK (bukan lintas request -- properti
    // protected, direset otomatis tiap kali Yayasan di-fetch ulang dari
    // database). activeSubscription() dipanggil berkali-kali dalam 1
    // render (TenantBillingCalculator::aksesPlatformPlan() misalnya
    // manggil ini 1x per Lembaga + 1x lagi terpisah) -- tanpa cache ini,
    // tiap panggilan query database dari nol. Pakai flag terpisah
    // ($activeSubscriptionCached), BUKAN null-coalescing (??=), karena
    // hasil "tidak ada subscription aktif" (null) itu sendiri VALID dan
    // harus ikut di-cache -- ??= akan terus query ulang selama hasilnya
    // null.
    protected ?Subscription $activeSubscriptionCache = null;

    protected bool $activeSubscriptionCached = false;

    public function activeSubscription(): ?Subscription
    {
        if (! $this->activeSubscriptionCached) {
            $this->activeSubscriptionCache = $this->subscriptions()
                ->where('status', 'active')
                ->where('berakhir_pada', '>', now())
                ->latest('berakhir_pada')
                ->first();

            $this->activeSubscriptionCached = true;
        }

        return $this->activeSubscriptionCache;
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
    // Cache in-memory PER KEY MODUL (bukan lintas request) -- hasFeature()
    // sekarang dipanggil berkali-kali dalam 1 render sejak sidebar
    // kembali auto-generate penuh (7 Sep 2026, lihat AdminPanelProvider):
    // banyak Resource cek hasFeature() masing-masing buat tentukan
    // tampil/tidaknya di menu, dan beberapa Resource kemungkinan cek key
    // yang SAMA (mis. beberapa halaman e-Kantin semua cek 'e_kantin').
    // Tanpa cache ini, tiap panggilan query subscriptions+lembagas+
    // lembaga_modules+module_prices dari nol -- ditemukan sebagai
    // penyebab render 1 halaman sempat tembus 213 query (turun dari 416
    // setelah perbaikan activeSubscription(), tapi masih tinggi karena
    // ini belum ikut di-cache).
    protected array $hasFeatureCache = [];

    public function hasFeature(string $key): bool
    {
        if (array_key_exists($key, $this->hasFeatureCache)) {
            return $this->hasFeatureCache[$key];
        }

        return $this->hasFeatureCache[$key] = $this->hitungHasFeature($key);
    }

    protected function hitungHasFeature(string $key): bool
    {
        // Selama trial: akses PENUH tanpa syarat, tidak peduli modul
        // mana yang sudah/belum dipilih -- ini SENGAJA (revisi final
        // setelah diskusi ulang), supaya sekolah benar-benar bisa
        // rasakan value produk selama masa coba, bukan cuma lihat
        // kerangka kosong. Modul yang dipilih di halaman Langganan
        // selama trial itu preferensi "nanti mau lanjut modul apa",
        // bukan gerbang akses saat trial masih berjalan.
        if ($this->status === 'trial') {
            return $this->isOnTrial();
        }

        // DITAMBAHKAN -- Master Data (Lembaga, Siswa, Kelas, dst) TIDAK
        // PERNAH dikunci status langganan, titik -- bukan cuma saat
        // Lembaga masih 0 (syarat sebelumnya TERLALU SEMPIT: begitu
        // Yayasan berhasil buat Lembaga pertamanya, syarat "count===0"
        // langsung gagal lagi, jadi menu Siswa/Kelas -- satu grup yang
        // SAMA, Master Data -- ikut kekunci lagi padahal mereka justru
        // baru mau isi data siswa ke Lembaga yang baru dibuat itu).
        //
        // Alasan Master Data memang seharusnya SELALU terbuka: data di
        // grup ini (Lembaga, Siswa, Kelas) itu justru INPUT yang
        // dipakai menghitung tagihan di Checkout (total siswa
        // se-Yayasan) -- Yayasan yang belum/tidak lagi bayar tetap
        // perlu bisa mengisi data ini dengan benar supaya estimasi
        // tagihan mereka akurat, bukan sekadar Rp0 karena datanya
        // kosong. Fitur BERBAYAR sungguhan (Akademik, Absensi, dst)
        // tetap terkunci seperti biasa lewat pengecekan di bawah ini
        // -- HANYA Master Data yang dikecualikan.
        if ($key === \App\Support\FeatureGate::MASTER_DATA) {
            return true;
        }

        if ($this->status !== 'active') {
            return false;
        }

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

        // DIHAPUS (15 Sep 2026) -- checklist "Fitur Premium yang
        // Dibuka" per-plan sudah dicabut dari form admin, diganti
        // hardcode di sini. Alasannya: admin cukup atur HARGA per
        // modul (Harga Modul) & tenant yang tentukan aktivasinya
        // sendiri lewat toggle di Checkout -- checklist per-plan itu
        // cuma nambah lapisan yang membingungkan tanpa manfaat nyata
        // (Paket Full sudah auto-toggle semua LembagaModule saat
        // pembayaran sukses, jadi tidak butuh jalur checklist
        // terpisah lagi untuk buka aksesnya).
        //
        // Fitur BAWAAN (bukan modul yang dijual/ditoggle terpisah) --
        // selalu terbuka untuk Yayasan yang statusnya aktif, apa pun
        // plan-nya:
        //  - Manajemen Sekolah, Master Setting: fitur dasar aplikasi.
        //  - Keuangan, e-Kantin: GRATIS untuk sekolah (dibebankan ke
        //    wali murid) -- TIDAK PERNAH ditoggle lewat LembagaModule
        //    sama sekali (tidak ada mekanisme otomatis lain buat
        //    modul gratis), jadi harus selalu terbuka di sini juga,
        //    supaya tidak sengaja ikut terkunci.
        if (in_array($key, [
            \App\Support\FeatureGate::MANAJEMEN_SEKOLAH,
            \App\Support\FeatureGate::MASTER_SETTING,
            \App\Support\FeatureGate::KEUANGAN,
            \App\Support\FeatureGate::E_KANTIN,
        ], true)) {
            return true;
        }

        // Skema à la carte (per-Lembaga): 6 modul BERBAYAR
        // (Akademik, Absensi, PSB, Tahfidz, Perizinan, Konseling)
        // MURNI ditentukan tenant lewat toggle Checkout (lihat
        // LembagaModule/ModulePrice) -- BUKAN dari plan sama sekali.
        // Paket Full tetap otomatis dapat semua, tapi lewat jalur
        // yang SAMA ini (semua LembagaModule di-toggle aktif otomatis
        // saat pembayaran Paket Full sukses -- lihat
        // DokuWebhookController/XenditWebhookController/
        // SubscriptionPaymentResource), bukan jalur terpisah lagi.
        //
        // Ini gating di level Yayasan (menu sidebar tampil untuk
        // semua Lembaga di bawah yayasan itu), BUKAN scoping data
        // per-Lembaga — kalau ke depan dibutuhkan penyembunyian menu
        // yang benar-benar berbeda per Lembaga dalam satu Yayasan,
        // itu perubahan arsitektur terpisah (tenant panel saat ini =
        // Yayasan).
        return $this->lembagas()
            ->whereHas('activeModules.modulePrice', fn ($q) => $q->where('key', $key))
            ->exists();
    }

    /**
     * True kalau Yayasan ini punya promo pendaftaran yang masih BELUM
     * dipakai (belum pernah diterapkan ke tagihan mana pun). Dipakai
     * TenantBillingCalculator untuk tahu apakah tagihan BERIKUTNYA yang
     * dihitung untuk Yayasan ini harus dapat diskon promo tersebut.
     */
    public function promoPendaftaranBelumDipakai(): bool
    {
        return ! $this->promo_pendaftaran_terpakai
            && (int) ($this->promo_pendaftaran_persen ?? 0) > 0;
    }
}
