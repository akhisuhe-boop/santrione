<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PpdbProfileController extends Controller
{
    public function index()
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        return view('ppdb.profil', compact('ppdb'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        if (! Hash::check($request->old_password, $ppdb->password)) {
            return back()->with('error', 'Password lama salah.');
        }

        $ppdb->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}