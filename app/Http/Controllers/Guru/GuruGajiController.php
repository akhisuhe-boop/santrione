<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Payroll;

class GuruGajiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::findOrFail(session('guru_id'));

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

        return view('guru.gaji', compact(
            'pegawai',
            'payroll',
            'riwayatPayroll'
        ));
    }
}