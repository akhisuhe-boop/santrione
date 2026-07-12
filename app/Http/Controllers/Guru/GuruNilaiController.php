<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruNilaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawaiId = session('guru_id');

        /*
        |--------------------------------------------------------------------------
        | Kelas & Mata Pelajaran yang Diampu Guru
        |--------------------------------------------------------------------------
        */
        
        $jadwals = JadwalPelajaran::with([
        'kelas',
        'mataPelajaran',
        ])
        ->where('pegawai_id', $pegawaiId)
        ->orderBy('kelas_id')
        ->get()
        ->unique(function ($item) {
            return $item->kelas_id . '-' . $item->mata_pelajaran_id;
        })
        ->values();

        /*
        |--------------------------------------------------------------------------
        | Daftar siswa
        |--------------------------------------------------------------------------
        */

        $siswas = collect();

        $jadwal = null;
        
        if ($request->filled('jadwal_id')) {
        
            $jadwal = JadwalPelajaran::with([
                'kelas',
                'mataPelajaran',
                'jamPelajaran',
            ])->findOrFail($request->jadwal_id);
        
            $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
                ->orderBy('nama_lengkap')
                ->get();
        }

        return view('guru.nilai', compact(
            'jadwals',
            'jadwal',
            'siswas'
        ));
    }

    /**
     * Simpan Massal Nilai
     */
    public function store(Request $request)
    {
        $request->validate([

            'jadwal_id'   => 'required|exists:jadwal_pelajarans,id',
            'tipe_nilai'  => 'required',
            'nilai'       => 'required|array',
        
        ]);
        
        $jadwal = JadwalPelajaran::where('id', $request->jadwal_id)
        ->where('pegawai_id', session('guru_id'))
        ->firstOrFail();

        $tahunAjaran = TahunAjaran::where('aktif', true)->first();

        DB::transaction(function () use ($request, $tahunAjaran, $jadwal) {

            foreach ($request->nilai as $siswaId => $nilai) {

                if ($nilai === null || $nilai === '') {
                    continue;
                }

                Nilai::updateOrCreate(

                    [

                        'siswa_id' => $siswaId,

                        'kelas_id' => $jadwal->kelas_id,

                        'mapel_id' => $jadwal->mata_pelajaran_id,
                        
                        'guru_id' => $jadwal->pegawai_id,

                        'tahun_ajaran_id' => $tahunAjaran->id,

                        'tipe_nilai' => $request->tipe_nilai,

                    ],

                    [

                        'nilai' => $nilai,
                    ]

                );

            }

        });

        return back()->with(
            'success',
            'Nilai berhasil disimpan.'
        );
    }
}