<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruAuthController extends Controller
{
    /**
     * Form Login
     */
    public function login()
    {
        $yayasan = Yayasan::first();

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

        $guru = Pegawai::where('niy', trim($request->login))->first();

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

        return redirect()->route('guru.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}