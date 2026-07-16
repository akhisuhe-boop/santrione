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

        // Ambil yayasan dari lembaga milik PPDB ini, BUKAN yayasan
        // pertama di database -- supaya portal orang tua nampilin
        // branding yayasan yang benar, bukan tertukar antar yayasan.
        $yayasan = $ppdb->lembaga?->yayasan ?? Yayasan::first();

        // Tagihan PPDB yang belum lunas
        $tagihan = Tagihan::where('ppdb_id', $ppdb->id)
            ->latest()
            ->first();
            
        // Pembayaran untuk TAGIHAN YANG SEDANG DITAMPILKAN saja --
        // sebelumnya ambil pembayaran terakhir dari SELURUH riwayat PPDB
        // ini, jadi kalau ada tagihan baru yang belum dibayar (mis. Daftar
        // Ulang), sistem salah nampilin status pembayaran tagihan LAMA
        // yang sudah lunas (mis. Formulir Pendaftaran).
        $pembayaran = $tagihan
            ? Pembayaran::where('tagihan_id', $tagihan->id)->latest()->first()
            : null;
            
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