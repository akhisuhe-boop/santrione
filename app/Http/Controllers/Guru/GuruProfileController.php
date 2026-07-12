<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruProfileController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::with('pegawaiLembagas.lembaga')
            ->findOrFail(session('guru_id'));
    
        return view('guru.profil', compact('pegawai'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
    
        $pegawai = Pegawai::findOrFail(session('guru_id'));
    
        if (!Hash::check($request->old_password, $pegawai->password)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }
    
        $pegawai->update([
            'password' => Hash::make($request->password),
        ]);
    
        return back()->with('success', 'Password berhasil diperbarui.');
    }
}