<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Payroll;
use App\Models\JurnalMengajar;

class GuruGajiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::with('pegawaiLembagas')->findOrFail(session('guru_id'));

        // Payroll bulan berjalan
        $payroll = Payroll::with([
            'items',
            'adjustments',
        ])
        ->where('pegawai_id', $pegawai->id)
        ->where('bulan', now()->month)
        ->where('tahun', now()->year)
        ->first();

        // Riwayat Payroll
        $riwayatPayroll = Payroll::where('pegawai_id', $pegawai->id)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $bulan = $payroll->bulan ?? now()->month;
        $tahun = $payroll->tahun ?? now()->year;

        // Rincian mengajar per sesi (bukan cuma total agregat) -- buat
        // jabatan yang digaji per JP, supaya guru bisa lihat tanggal,
        // kelas/mapel, & nominal SETIAP sesi, bukan cuma satu baris
        // "Honor Guru (40 JP)".
        $jabatanPerJpIds = $pegawai->pegawaiLembagas
            ->where('metode_penggajian', 'per_jp')
            ->pluck('id');

        $riwayatMengajar = collect();

        if ($jabatanPerJpIds->isNotEmpty()) {

            $riwayatMengajar = JurnalMengajar::query()
                ->whereIn('pegawai_lembaga_id', $jabatanPerJpIds)
                ->where('status', 'valid')
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->with(['kelas', 'mataPelajaran', 'jamPelajaran', 'pegawaiLembaga'])
                ->orderByDesc('tanggal')
                ->get()
                ->map(function ($jurnal) {

                    $durasiJp = $jurnal->jamPelajaran->durasi_jp ?? 0;

                    // JP pengganti (menggantikan guru lain) pakai tarif
                    // manual kalau di-set, fallback ke tarif normal
                    // jabatan pegawai itu sendiri -- sama seperti logika
                    // di PayrollService.
                    $tarif = $jurnal->pegawai_asli_id
                        ? ($jurnal->tarif_pengganti_per_jp ?? $jurnal->pegawaiLembaga->tarif_per_jp ?? 0)
                        : ($jurnal->pegawaiLembaga->tarif_per_jp ?? 0);

                    $jurnal->durasi_jp = $durasiJp;
                    $jurnal->tarif_dipakai = $tarif;
                    $jurnal->nominal = $durasiJp * $tarif;
                    $jurnal->is_pengganti = (bool) $jurnal->pegawai_asli_id;

                    return $jurnal;
                });
        }

        return view('guru.gaji', compact(
            'pegawai',
            'payroll',
            'riwayatPayroll',
            'riwayatMengajar'
        ));
    }
}