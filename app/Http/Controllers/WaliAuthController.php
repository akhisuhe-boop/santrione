<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WaliAuthController extends Controller
{
    /**
     * Form Login
     */
    public function login()
    {
        $yayasan = Yayasan::first();
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

        $siswa = Siswa::where('nis', $login)
            ->orWhere('nisn', $login)
            ->first();

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

        return redirect()->route('wali.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('role.login');
    }
}