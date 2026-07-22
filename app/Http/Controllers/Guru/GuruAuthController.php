<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Concerns\ResolvesPublicTenant;
use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruAuthController extends Controller
{
    use ResolvesPublicTenant;

    /**
     * Form Login
     */
    public function login()
    {
        $yayasan = $this->currentYayasan();

        return view('guru.auth.login', compact('yayasan'));
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

        $yayasanId = $this->currentYayasanId();

        $query = Pegawai::where('niy', trim($request->login));

        // Kalau ada context tenant (dari /y/{slug}), scope ketat ke yayasan itu
        // lewat relasi many-to-many pegawai <-> lembaga.
        if ($yayasanId) {
            $query->whereHas('lembagas', function ($q) use ($yayasanId) {
                $q->where('yayasan_id', $yayasanId);
            });
        }

        $guru = $query->first();

        if (! $guru) {
            return back()->with('error', 'NIY tidak ditemukan');
        }

        if (! Hash::check($request->password, $guru->password)) {
            return back()->with('error', 'Password salah');
        }

        session([
            'guru_id'   => $guru->id,
            'guru_nama' => $guru->nama,
        ]);

        if (! session('active_public_yayasan_id')) {
            $lembaga = $guru->lembagas()->first();
            if ($lembaga) {
                session(['active_public_yayasan_id' => $lembaga->yayasan_id]);
            }
        }

        return redirect()->route('guru.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['guru_id', 'guru_nama']);
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
