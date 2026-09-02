<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JurnalMengajar;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class GuruAbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Periode -- default bulan berjalan, sama seperti Absensi Santri
        // di portal wali. Sebelumnya tidak ada filter periode sama
        // sekali di halaman ini.
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        // withoutGlobalScopes() -- portal guru sudah scoping sendiri
        // lewat pegawai_id yang login (session('guru_id')), tidak perlu
        // (dan tidak boleh bergantung pada) tenant scope otomatis yang
        // sebenarnya dirancang untuk panel admin.
        $pegawai = Pegawai::withoutGlobalScopes()->findOrFail(session('guru_id'));

        $absensis = Absensi::withoutGlobalScopes()
            ->with(['jadwalKegiatan.template'])
            ->where('pegawai_id', $pegawai->id)
            ->whereMonth('waktu', $bulan)
            ->whereYear('waktu', $tahun)
            ->latest('waktu')
            ->get();

        $absensiHarians = $pegawai->absensiHarians()
            ->withoutGlobalScopes()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $jurnals = JurnalMengajar::withoutGlobalScopes()
            ->with(['kelas', 'mataPelajaran', 'jamPelajaran'])
            ->where('pegawai_id', $pegawai->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->latest('tanggal')
            ->get();

        // Tingkat Kehadiran resmi -- MURNI dari AbsensiHarian (absen
        // masuk & pulang sekolah), bukan lagi dari Absensi kegiatan
        // (rapat, sholat berjamaah, dll -- itu insight terpisah).
        $totalHariTercatat = $absensiHarians->count();
        $hariHadir = $absensiHarians->whereIn('status_masuk', ['Hadir', 'Terlambat'])->count();

        $persentaseKehadiran = $totalHariTercatat > 0
            ? round(($hariHadir / $totalHariTercatat) * 100)
            : 0;

        return view('guru.absensi', compact(
            'pegawai',
            'absensis',
            'absensiHarians',
            'jurnals',
            'bulan',
            'tahun',
            'persentaseKehadiran',
            'totalHariTercatat',
            'hariHadir'
        ));
    }
}