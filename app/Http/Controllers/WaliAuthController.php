<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicTenant;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WaliAuthController extends Controller
{
    use ResolvesPublicTenant;

    /**
     * Form Login
     */
    public function login()
    {
        $yayasan = $this->currentYayasan();
        return view('wali.auth.login', compact('yayasan'));
    }

    /**
     * Proses Login
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'login'    => ['required'],
            'password' => ['required'],
        ]);

        $login = trim($request->login);
        $yayasanId = $this->currentYayasanId();

        $query = Siswa::withoutGlobalScopes()
            ->where(function ($q) use ($login) {
                $q->where('nis', $login)->orWhere('nisn', $login);
            });

        // Kalau ada context tenant (dari /y/{slug}), scope ketat ke yayasan itu.
        if ($yayasanId) {
            $query->whereHas('lembaga', function ($q) use ($yayasanId) {
                $q->where('yayasan_id', $yayasanId);
            });
        }

        $siswa = $query->first();

        if (! $siswa) {
            return back()->with('error', 'NIS / NISN tidak ditemukan');
        }

        if (! Hash::check($request->password, $siswa->password)) {
            return back()->with('error', 'Password salah');
        }

        session([
            'siswa_id' => $siswa->id,
            'wali_nama' => $siswa->nama_ayah ?? $siswa->nama,
        ]);

        // Sama seperti login Guru — tanpa ini, nama yayasan di header
        // portal tidak muncul (placeholder "Nama Yayasan"), dan semua
        // pengecekan fitur premium (mis. kartu menu Kantin) gagal
        // karena tidak ketemu yayasan aktifnya.
        $lembaga = $siswa->lembaga;

        if ($lembaga) {
            session(['active_public_yayasan_id' => $lembaga->yayasan_id]);
        }

        return redirect()->route('wali.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['siswa_id', 'wali_nama', 'active_public_yayasan_id']);
        $request->session()->regenerateToken();

        return redirect()->route('role.login');
    }
}
