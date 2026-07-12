<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class GuruJadwalController extends Controller
{
    public function index(Request $request)
    {
        $guru = Pegawai::find(session('guru_id'));

        abort_if(!$guru, 404);

        $semesterAktif = TahunAjaran::aktif();

        $jadwal = JadwalPelajaran::query()
        ->with([
            'kelas',
            'mataPelajaran',
            'jamPelajaran',
        ])
        ->join(
            'jam_pelajarans',
            'jadwal_pelajarans.jam_pelajaran_id',
            '=',
            'jam_pelajarans.id'
        )
        ->where('jadwal_pelajarans.pegawai_id', $guru->id)
        ->orderByRaw("
            FIELD(
                jadwal_pelajarans.hari,
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
            )
        ")
        ->orderBy('jam_pelajarans.urutan')
        ->select('jadwal_pelajarans.*')
        ->get();

        return view('guru.jadwal', compact(
            'guru',
            'semesterAktif',
            'jadwal'
        ));
    }
}