<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PublicRegistrationController extends Controller
{
    /**
     * Form pendaftaran yayasan baru (SaaS self-service signup).
     */
    public function create()
    {
        return view('public.daftar', [
            'trialDays' => config('subscription.trial_days', 14),
        ]);
    }

    /**
     * Proses pendaftaran: bikin Yayasan (status trial) + 1 Lembaga
     * default + 1 akun admin + Subscription dasar (plan "Akses
     * Platform", supaya menu inti langsung terbuka) — TIDAK ada lagi
     * pemilihan paket di form ini (dihapus, sesuai keputusan revisi:
     * paket/harga ditentukan tenant sendiri lewat pilih modul di
     * halaman "Langganan", bukan di form daftar).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_yayasan' => ['required', 'string', 'max:255'],
            'nama_admin' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'custom_domain' => ['nullable', 'string', 'max:255'],
        ]);

        [$yayasan, $admin, $lead] = DB::transaction(function () use ($data) {

            // Kalau ada promo landing page yang SEDANG AKTIF pas pendaftaran
            // ini terjadi, snapshot ke Yayasan -- terpisah total dari
            // LandingSetting->promo_* yang bisa berubah/berakhir kapan pun
            // setelahnya. Berlaku SATU KALI untuk tagihan pertama Yayasan
            // ini saja (lihat TenantBillingCalculator + command invoice).
            $landingSetting = \App\Models\LandingSetting::current();
            // PENTING: pakai promoAdaDiskon(), BUKAN promoSedangBerjalan().
            // promoSedangBerjalan() cuma nunjukin "banner-nya tampil atau
            // tidak" (termasuk mode "Cuma Countdown" yang sengaja TANPA
            // diskon harga sama sekali) -- kalau dipakai di sini, sekolah
            // yang daftar pas mode Cuma Countdown aktif bisa keliru
            // tercatat dapat promo (bahkan mungkin angka lama yang masih
            // nyangkut di kolom promo_persen). promoAdaDiskon() sudah
            // pasti berarti diskon sungguhan sedang berlaku.
            $promoAktifSaatDaftar = $landingSetting->promoAdaDiskon();

            $yayasan = Yayasan::create([
                'nama' => $data['nama_yayasan'],
                'email' => $data['email'],
                'telepon' => $data['no_hp'] ?? null,
                'domain' => $data['custom_domain'] ?? null,
                'promo_pendaftaran_persen' => $promoAktifSaatDaftar ? $landingSetting->promo_persen : null,
                'promo_pendaftaran_teks' => $promoAktifSaatDaftar ? $landingSetting->promo_teks : null,
                // status & trial_ends_at otomatis ke-set 'trial' +
                // trial_days ke depan lewat Yayasan::booted().
            ]);

            // TIDAK auto-buat Lembaga lagi -- tenant bikin sendiri
            // lewat Master Data > Lembaga kapan pun mereka siap
            // (biasanya sambil eksplorasi produk selama trial). Ini
            // lebih natural daripada dipaksakan otomatis dengan nama
            // generik yang kemungkinan besar akan diganti lagi.

            $admin = User::create([
                'name' => $data['nama_admin'],
                'email' => $data['email'],
                'password' => $data['password'], // auto-hash via cast
                'yayasan_id' => $yayasan->id,
            ]);

            $role = Role::firstOrCreate([
                'name' => 'Admin Yayasan',
                'guard_name' => 'web',
            ]);

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(Permission::all());
            }

            $admin->assignRole($role);

            // Subscription dasar ke plan "Akses Platform" -- dibuat
            // OTOMATIS untuk SEMUA yayasan baru (bukan opsional lagi),
            // supaya:
            // (a) menu inti (Master Data, Manajemen Sekolah, Master
            //     Setting) langsung terbuka lewat Yayasan::hasFeature(),
            // (b) TenantBillingCalculator langsung punya basis hitung
            //     estimasi yang akurat sejak hari pertama,
            // (c) Yayasan otomatis "kebaca" oleh command autopilot
            //     bulanan begitu masa trial berakhir.
            // berakhir_pada disamakan dengan trial_ends_at -- selama
            // trial, tidak ditagih (command autopilot cuma proses yang
            // subscription-nya AKTIF & belum lewat berakhir_pada, jadi
            // otomatis "aman" sepanjang trial berjalan).
            $planDasar = SubscriptionPlan::where('slug', 'akses-platform')->first();

            if ($planDasar) {
                $yayasan->subscriptions()->create([
                    'subscription_plan_id' => $planDasar->id,
                    'status' => 'active',
                    'mulai_pada' => now(),
                    'berakhir_pada' => $yayasan->trial_ends_at ?? now()->addDays(config('subscription.trial_days', 14)),
                ]);
            }

            // Catat sebagai Lead di modul CRM -- supaya setiap pendaftaran
            // trial otomatis masuk daftar follow-up sales, tidak cuma
            // ada di tabel Yayasan yang lebih teknis.
            $lead = Lead::create([
                'yayasan_id' => $yayasan->id,
                'nama_lembaga' => $data['nama_yayasan'],
                'nama_pic' => $data['nama_admin'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'sumber' => 'Trial Signup',
                'status' => 'baru',
            ]);

            return [$yayasan, $admin, $lead];
        });

        try {
            \App\Services\NotificationService::sendPendaftaranBerhasil(
                $yayasan,
                $data['nama_admin'],
                $data['email'],
                $data['password']
            );
        } catch (\Throwable $e) {
            // Gagal kirim WA welcome TIDAK boleh menggagalkan pendaftaran
            // yang sudah sukses -- cukup dicatat ke log.
            \Illuminate\Support\Facades\Log::error("PublicRegistrationController: gagal kirim WA welcome untuk yayasan {$yayasan->id}: {$e->getMessage()}");
        }

        try {
            \App\Services\NotificationService::sendLeadBaruInternal($lead);
        } catch (\Throwable $e) {
            // Sama seperti di atas -- gagal notif internal tidak boleh
            // menggagalkan pendaftaran yang sudah sukses.
            \Illuminate\Support\Facades\Log::error("PublicRegistrationController: gagal kirim notifikasi lead baru internal untuk lead {$lead->id}: {$e->getMessage()}");
        }

        Auth::guard('web')->login($admin);

        // Arahkan ke Dashboard biasa (BUKAN halaman Langganan) --
        // trial = akses penuh langsung, biar kesan pertama produk
        // terasa "hidup" dipakai, bukan disuruh pilih modul/bayar
        // duluan. Halaman Langganan tetap ada di sidebar, dibuka
        // kapan saja tenant mau lihat estimasi/pilih preferensi modul.
        return redirect()->to(
            '/admin/' . $yayasan->slug
        )->with('success', 'Selamat datang! Masa trial 14 hari sudah aktif — semua fitur bisa langsung dicoba.');
    }
}
