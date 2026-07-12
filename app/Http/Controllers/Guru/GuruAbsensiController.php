<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JurnalMengajar;
use App\Models\Pegawai;

class GuruAbsensiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::findOrFail(session('guru_id'));

        $absensis = Absensi::with([
                'jadwalKegiatan.template',
            ])
            ->where('pegawai_id', $pegawai->id)
            ->latest('waktu')
            ->get();

        $jurnals = JurnalMengajar::with([
                'kelas',
                'mataPelajaran',
                'jamPelajaran',
            ])
            ->where('pegawai_id', $pegawai->id)
            ->latest('tanggal')
            ->get();

        return view('guru.absensi', compact(
            'pegawai',
            'absensis',
            'jurnals'
        ));
    }
}