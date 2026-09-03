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
        $pegawai = Pegawai::withoutGlobalScopes()->with('pegawaiLembagas')->findOrFail(session('guru_id'));

        $bulan = now()->month;
        $tahun = now()->year;

        /*
        |--------------------------------------------------------------------------
        | PAYROLL BULAN BERJALAN -- BISA LEBIH DARI 1
        |--------------------------------------------------------------------------
        |
        | Payroll bisa digenerate terpisah per jenis (struktural/
        | fungsional) supaya bisa dibayar di tanggal berbeda dalam 1
        | bulan yang sama. Payroll lama (sebelum fitur ini ada) tetap
        | 1 baris gabungan (jenis null).
        */
        $payrollsBulanIni = Payroll::withoutGlobalScopes()
            ->with(['items', 'adjustments'])
            ->where('pegawai_id', $pegawai->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $payrollStruktural = $payrollsBulanIni->firstWhere('jenis', 'struktural');
        $payrollFungsional = $payrollsBulanIni->firstWhere('jenis', 'fungsional');
        $payrollGabungan = $payrollsBulanIni->first(fn ($p) => is_null($p->jenis));

        // Riwayat Payroll (semua periode, semua jenis dicampur -- tetap
        // urut dari yang terbaru)
        $riwayatPayroll = Payroll::withoutGlobalScopes()
            ->where('pegawai_id', $pegawai->id)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT MENGAJAR PER SESI (khusus honor per JP)
        |--------------------------------------------------------------------------
        */
        $jabatanPerJpIds = $pegawai->pegawaiLembagas
            ->where('metode_penggajian', 'per_jp')
            ->pluck('id');

        $riwayatMengajar = collect();

        if ($jabatanPerJpIds->isNotEmpty()) {

            $riwayatMengajar = JurnalMengajar::withoutGlobalScopes()
                ->whereIn('pegawai_lembaga_id', $jabatanPerJpIds)
                ->where('status', 'valid')
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->with(['kelas', 'mataPelajaran', 'jamPelajaran', 'pegawaiLembaga'])
                ->orderByDesc('tanggal')
                ->get()
                ->map(function ($jurnal) {

                    $durasiJp = $jurnal->jamPelajaran->durasi_jp ?? 0;

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

        /*
        |--------------------------------------------------------------------------
        | SUSUN SLIP YANG DITAMPILKAN
        |--------------------------------------------------------------------------
        |
        | - Pegawai rangkap (struktural + fungsional digenerate
        |   terpisah) -> 2 slip, Riwayat Mengajar cuma nempel di slip
        |   Fungsional.
        | - Pegawai cuma salah satu jenis -> 1 slip dengan label itu.
        | - Tenant belum pakai fitur split (payroll lama, jenis null)
        |   -> 1 slip gabungan tanpa label, Riwayat Mengajar tetap
        |   nempel di situ kalau ada honor per JP.
        | - Belum ada payroll bulan ini sama sekali -> slip kosong
        |   (ditangani di view lewat @forelse/@empty).
        */
        $slipList = collect();

        if ($payrollStruktural) {
            $slipList->push([
                'payroll' => $payrollStruktural,
                'label' => 'Struktural',
                'riwayatMengajar' => collect(),
            ]);
        }

        if ($payrollFungsional) {
            $slipList->push([
                'payroll' => $payrollFungsional,
                'label' => 'Fungsional',
                'riwayatMengajar' => $riwayatMengajar,
            ]);
        }

        if ($slipList->isEmpty() && $payrollGabungan) {
            $slipList->push([
                'payroll' => $payrollGabungan,
                'label' => null,
                'riwayatMengajar' => $riwayatMengajar,
            ]);
        }

        return view('guru.gaji', compact(
            'pegawai',
            'slipList',
            'riwayatPayroll',
            'riwayatMengajar'
        ));
    }
}
