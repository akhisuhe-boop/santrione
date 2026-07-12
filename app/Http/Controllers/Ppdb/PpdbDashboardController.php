<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb;
use App\Models\Tagihan;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Pembayaran;

class PpdbDashboardController extends Controller
{
    /**
     * Dashboard Portal PPDB
     */
    public function index(Request $request)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        $yayasan = Yayasan::first();

        // Tagihan PPDB yang belum lunas
        $tagihan = Tagihan::where('ppdb_id', $ppdb->id)
            ->latest()
            ->first();
            
        // Pembayaran terakhir PPDB
        $pembayaran = Pembayaran::whereHas('tagihan', function ($query) use ($ppdb) {
        
            $query->where('ppdb_id', $ppdb->id);
        
        })
        ->latest()
        ->first();
            
        // Pengumuman    
        $pengumuman = Announcement::query()
        ->where(function ($query) {
            $query->where('target_type', 'all')
                  ->orWhere(function ($q) {
                      $q->where('target_type', 'role')
                        ->where('target_role', 'ppdb');
                  });
        })
        ->latest()
        ->take(5)
        ->get();

        return view('ppdb.dashboard', [
            'yayasan'     => $yayasan,
            'ppdb'        => $ppdb,
            'tagihan'     => $tagihan,
            'pembayaran'  => $pembayaran,
            'progress'    => $this->progress($ppdb->status),
            'pengumuman'  => $pengumuman,
        ]);
    }

    /**
     * Progress PPDB
     */
    protected function progress(string $status): array
    {
        $steps = [
            'Akun Dibuat',
            'Pembayaran Formulir',
            'Isi Formulir',
            'Upload Berkas',
            'Verifikasi Berkas',
            'Tes Seleksi',
            'Pengumuman',
            'Daftar Ulang',
            'Resmi Menjadi Siswa',
        ];

        $current = match ($status) {

            'draft' => 0,

            'menunggu_pembayaran' => 1,

            'formulir' => 2,

            'upload_berkas' => 3,

            'verifikasi_berkas' => 4,

            'tes' => 5,

            'lulus' => 6,

            'daftar_ulang' => 7,

            'aktif' => 8,

            default => 0,
        };
        
        return [
            'steps' => $steps,
            'current' => $current,
        ];
    }
}