<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Announcement;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;

class GuruDashboardController extends Controller
{
    public function index(Request $request)
    {
        $guru = Pegawai::find(session('guru_id'));

        $semesterAktif = TahunAjaran::aktif();

        $hari = now()->locale('id')->translatedFormat('l');

        $jadwalHariIni = JadwalPelajaran::query()
            ->with([
                'kelas',
                'mataPelajaran',
                'jamPelajaran',
            ])
            ->where('pegawai_id', $guru->id)
            ->where('hari', $hari)
            ->get()
            ->sortBy(fn ($item) => $item->jamPelajaran?->urutan ?? 999)
            ->values()
            ->map(function ($jadwal) {
        
                $jadwal->jam_mulai = $jadwal->jamPelajaran?->jam_mulai?->format('H:i') ?? '-';
                $jadwal->jam_selesai = $jadwal->jamPelajaran?->jam_selesai?->format('H:i') ?? '-';
        
                $jadwal->jurnal_sudah_diisi = JurnalMengajar::query()
                    ->whereDate('tanggal', today())
                    ->where('jadwal_pelajaran_id', $jadwal->id)
                    ->exists();
        
                return $jadwal;
            });
        
        $jadwalSekarang = $jadwalHariIni->first(function ($jadwal) {

            $sekarang = now()->format('H:i');
        
            return $sekarang >= $jadwal->jam_mulai
                && $sekarang <= $jadwal->jam_selesai;
        
        });

        $pengumuman = Announcement::visibleFor('guru')
        ->take(3)
        ->get();

        return view('guru.dashboard', compact(
            'guru',
            'semesterAktif',
            'jadwalHariIni',
            'jadwalSekarang',
            'pengumuman'
        ));
    }
}