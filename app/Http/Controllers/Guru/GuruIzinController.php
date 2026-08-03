<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\IzinHarian;

class GuruIzinController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::findOrFail(session('guru_id'));

        $izinHarians = IzinHarian::where('pegawai_id', $pegawai->id)
            ->where('tipe', 'guru')
            ->latest()
            ->get();

        return view('guru.izin', compact('pegawai', 'izinHarians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:Izin,Sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = null;

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('izin-harian', 'r2-private');
        }

        IzinHarian::create([
            'pegawai_id' => session('guru_id'),
            'tipe' => 'guru',
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'lampiran' => $path,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('guru.izin')
            ->with('success', 'Pengajuan izin berhasil dikirim, menunggu persetujuan admin');
    }
}
