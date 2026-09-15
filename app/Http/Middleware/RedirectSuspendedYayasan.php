<?php

namespace App\Http\Middleware;

use App\Filament\Pages\Langganan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kalau Yayasan sedang TIDAK punya akses (lihat Yayasan::hasAccess() --
 * status suspended/cancelled, atau trial yang sudah lewat tanggal),
 * SEMUA request di dalam panel admin diarahkan paksa ke halaman
 * Langganan (satu-satunya halaman yang tetap bisa diakses), supaya
 * user bisa lihat kenapa terkunci dan langsung bayar di situ -- bukan
 * diblokir di halaman login (supaya user tetap dapat konteks & jalan
 * keluar, bukan cuma "gagal login" tanpa penjelasan).
 *
 * PERUBAHAN 24 Agustus 2026: sebelumnya kondisi di sini cuma cek
 * `status !== 'suspended'` secara manual -- diganti pakai
 * Yayasan::hasAccess() (method yang sudah ada, didesain eksplisit
 * sebagai SATU-SATUNYA sumber kebenaran soal boleh/tidaknya akses)
 * supaya otomatis ikut nyakup status 'cancelled' juga, dan supaya
 * kalau definisi "boleh akses" berubah nanti, cukup diubah di 1
 * tempat (hasAccess()), middleware ini otomatis ikut update tanpa
 * perlu diingat-ingat untuk disinkronkan manual.
 *
 * Ditemukan juga (24 Agustus 2026) bahwa User::canAccessPanel() dulu
 * SUDAH menolak login total untuk kasus yang sama lewat
 * Yayasan::hasAccess() -- itu sudah dilepas dari sana (lihat
 * User::canAccessPanel()) supaya login tetap berhasil dan middleware
 * inilah yang menangani redirect-nya, sesuai keputusan desain baru.
 *
 * TIDAK menyentuh endpoint Livewire (route itu terdaftar terpisah,
 * tidak lewat middleware panel ini -- dikonfirmasi via
 * `php artisan route:list --path=admin` 24 Agustus 2026) -- jadi
 * interaksi wire:click di halaman Langganan sendiri (toggle siklus,
 * tombol Bayar Sekarang, dst) tetap jalan normal walau Yayasan
 * sedang tidak punya akses.
 */
class RedirectSuspendedYayasan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || $user->is_platform_admin) {
            return $next($request);
        }

        $yayasan = $user->yayasan;

        if (! $yayasan || $yayasan->hasAccess()) {
            return $next($request);
        }

        // Rute yang selalu boleh diakses walau tidak punya akses -- kalau
        // TIDAK di-allowlist di sini, user akan terjebak loop
        // redirect atau nggak bisa logout sama sekali.
        //
        // DITAMBAHKAN (resources.lembagas.*) -- Yayasan yang trial-nya
        // habis TAPI belum punya Lembaga sama sekali kejebak buntu:
        // Checkout tidak bisa hitung tagihan apa pun tanpa Lembaga
        // (total selalu Rp0), tapi tombol "Buat Lembaga" di Checkout
        // itu navigasi ke halaman PENUH (bukan wire:click), jadi kena
        // redirect balik oleh middleware ini kalau tidak di-allowlist.
        // Membiarkan CRUD Lembaga (bukan modul/fitur lain) tetap bisa
        // diakses saat suspended itu aman -- cuma data setup dasar,
        // bukan fitur berbayar.
        // DITAMBAHKAN (Siswa, Kelas) -- celah yang sama seperti Lembaga:
        // begitu Yayasan berhasil buat Lembaga pertamanya, mereka
        // butuh isi data Siswa (supaya total siswa di Checkout tidak
        // Rp0) dan mungkin Kelas -- rute-rute ini juga perlu
        // di-allowlist, bukan cuma di FeatureGate (Yayasan::
        // hitungHasFeature()) yang sudah dibuka duluan. Slug 'kelas'
        // ditambah 2 kemungkinan (Filament bisa pluralkan jadi
        // "kelas" atau "kelases" tergantung heuristik Laravel) --
        // yang tidak ada sebagai rute nyata cuma tidak akan pernah
        // cocok di in_array(), aman ditambahkan berjaga-jaga.
        $ruteBoleh = [
            'filament.admin.pages.langganan',
            'filament.admin.pages.checkout-langganan',
            'filament.admin.auth.logout',
            'filament.admin.resources.lembagas.index',
            'filament.admin.resources.lembagas.create',
            'filament.admin.resources.lembagas.edit',
            'filament.admin.resources.siswas.index',
            'filament.admin.resources.siswas.create',
            'filament.admin.resources.siswas.edit',
            'filament.admin.resources.kelas.index',
            'filament.admin.resources.kelas.create',
            'filament.admin.resources.kelas.edit',
            'filament.admin.resources.kelases.index',
            'filament.admin.resources.kelases.create',
            'filament.admin.resources.kelases.edit',
        ];

        if ($request->route() && in_array($request->route()->getName(), $ruteBoleh, true)) {
            return $next($request);
        }

        return redirect(Langganan::getUrl(tenant: $yayasan));
    }
}
