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
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('urutan')
            ->get();

        return view('public.daftar', [
            'plans' => $plans,
            'trialDays' => config('subscription.trial_days', 14),
        ]);
    }

    /**
     * Proses pendaftaran: bikin Yayasan (status trial) + 1 akun admin,
     * langsung login-kan, redirect ke panel yayasan itu.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_yayasan' => ['required', 'string', 'max:255'],
            'nama_admin' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
        ]);

        [$yayasan, $admin] = DB::transaction(function () use ($data) {

            $yayasan = Yayasan::create([
                'nama' => $data['nama_yayasan'],
                'email' => $data['email'],
                'telepon' => $data['no_hp'] ?? null,
                // status & trial_ends_at otomatis ke-set 'trial' +
                // trial_days ke depan lewat Yayasan::booted().
            ]);

            $admin = User::create([
                'name' => $data['nama_admin'],
                'email' => $data['email'],
                'password' => $data['password'], // auto-hash via cast
                'yayasan_id' => $yayasan->id,
            ]);

            // Role "Admin Yayasan" dipakai ulang lintas yayasan (sama
            // seperti alur bikin Yayasan manual dari admin panel) —
            // lihat catatan lebih lengkap di CreateYayasan::afterCreate().
            $role = Role::firstOrCreate([
                'name' => 'Admin Yayasan',
                'guard_name' => 'web',
            ]);

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(Permission::all());
            }

            $admin->assignRole($role);

            // Kalau calon customer sempat pilih paket di form, catat
            // sebagai langganan berstatus 'pending' — BELUM aktif,
            // BELUM ganti status yayasan (tetap 'trial' sampai memang
            // dibayar/diverifikasi). Ini cuma jejak "minat" awal supaya
            // gampang di-follow-up sales/admin.
            if (! empty($data['subscription_plan_id'])) {
                $yayasan->subscriptions()->create([
                    'subscription_plan_id' => $data['subscription_plan_id'],
                    'status' => 'pending',
                ]);
            }

            return [$yayasan, $admin];
        });

        Auth::guard('web')->login($admin);

        return redirect()->to('/admin/' . $yayasan->slug);
    }
}
