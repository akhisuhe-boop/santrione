<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Login otomatis platform admin sebagai admin dari yayasan tertentu --
 * dipakai tombol "Login" di Daftar Yayasan (Platform admin).
 *
 * Panel Platform & panel tenant (admin/{slug}) ada di DOMAIN BERBEDA
 * (lihat config/platform.php), jadi sesi login TIDAK otomatis ikut
 * pindah domain. Alurnya 2 langkah pakai token sekali-pakai:
 *
 * 1. requestLogin() -- jalan di domain PLATFORM (tempat admin sedang
 *    login), verifikasi dia memang platform admin, simpan token
 *    sementara di cache, lalu redirect ke domain tenant.
 * 2. consume() -- jalan di domain TENANT, tukar token itu jadi sesi
 *    login sungguhan sebagai admin yayasan tersebut.
 *
 * Token cuma berlaku 60 detik & sekali pakai (langsung dihapus begitu
 * dipakai) supaya tidak bisa disalahgunakan kalau ke-share/ke-log.
 */
class PlatformImpersonateController extends Controller
{
    public function requestLogin(Yayasan $yayasan)
    {
        abort_unless((bool) auth()->user()?->is_platform_admin, 403);

        $targetUser = User::where('yayasan_id', $yayasan->id)
            ->where('is_platform_admin', false)
            ->orderBy('id')
            ->first();

        if (! $targetUser) {
            return back()->with('error', 'Yayasan ini belum punya akun admin sama sekali -- tidak bisa di-login-kan.');
        }

        $token = Str::random(48);

        Cache::put('impersonate:' . $token, [
            'target_user_id' => $targetUser->id,
            'impersonator_id' => auth()->id(),
        ], now()->addSeconds(60));

        $tenantUrl = rtrim(config('app.url'), '/') . '/impersonate/consume/' . $token
            . '?redirect=' . urlencode('/admin/' . $yayasan->slug);

        return redirect($tenantUrl);
    }

    public function consume(string $token)
    {
        $data = Cache::pull('impersonate:' . $token);

        abort_unless($data, 404);

        $targetUser = User::find($data['target_user_id']);

        abort_unless($targetUser, 404);

        // Simpan siapa platform admin aslinya, supaya bisa "Kembali ke
        // Platform" nanti dari dalam panel tenant.
        session(['impersonator_id' => $data['impersonator_id']]);

        Auth::login($targetUser);

        return redirect(request()->query('redirect', '/'));
    }

    /**
     * Kembali jadi platform admin lagi -- dipanggil dari dalam panel
     * tenant (lewat banner impersonate), jalan di domain TENANT juga.
     * Sama seperti requestLogin, perlu lompat balik ke domain platform
     * lewat token sekali pakai supaya sesinya benar-benar pindah.
     */
    public function stopRequest()
    {
        $impersonatorId = session('impersonator_id');

        if (! $impersonatorId) {
            return redirect('/');
        }

        $token = Str::random(48);

        Cache::put('stop-impersonate:' . $token, [
            'impersonator_id' => $impersonatorId,
        ], now()->addSeconds(60));

        session()->forget('impersonator_id');

        return redirect(
            'https://' . config('platform.domain') . '/impersonate/stop-consume/' . $token
        );
    }

    public function stopConsume(string $token)
    {
        $data = Cache::pull('stop-impersonate:' . $token);

        abort_unless($data, 404);

        $impersonator = User::find($data['impersonator_id']);

        abort_unless($impersonator, 404);

        Auth::login($impersonator);

        return redirect('/yayasan-overviews');
    }
}
