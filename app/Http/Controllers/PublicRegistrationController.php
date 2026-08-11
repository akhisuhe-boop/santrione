<?php

namespace App\Http\Controllers;

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

        [$yayasan, $admin] = DB::transaction(function () use ($data) {

            $yayasan = Yayasan::create([
                'nama' => $data['nama_yayasan'],
                'email' => $data['email'],
                'telepon' => $data['no_hp'] ?? null,
                'domain' => $data['custom_domain'] ?? null,
                // status & trial_ends_at otomatis ke-set 'trial' +
                // trial_days ke depan lewat Yayasan::booted().
            ]);

            // Auto-buat 1 Lembaga default -- supaya begitu selesai
            // daftar, langsung ada Lembaga yang bisa dipilihkan modul
            // di halaman Langganan, tidak perlu bikin manual dulu.
            $yayasan->lembagas()->create([
                'nama' => $data['nama_yayasan'],
                'jenis' => 'Umum',
            ]);

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

            return [$yayasan, $admin];
        });

        Auth::guard('web')->login($admin);

        // Arahkan ke halaman Langganan (bukan dashboard/Lembaga) —
        // tenant WAJIB lewat sini dulu untuk pilih modul yang mau
        // dipakai, sebelum eksplorasi fitur lain.
        return redirect()->to(
            '/admin/' . $yayasan->slug . '/langganan'
        )->with('success', 'Selamat datang! Masa trial 14 hari sudah aktif — silakan pilih modul yang mau dipakai di bawah.');
    }
}
