<?php

namespace App\Services;

use App\Models\Tagihan;
use App\Models\JenisTagihan;
use App\Models\SettingNominalTagihan;
use App\Models\Rekening;
use App\Models\TahunAjaran;

class TagihanService
{
    // ======================
    // 🔥 RESOLVER (INTI SYSTEM)
    // ======================
    public static function resolveNominal($jenis, $ppdb, $bulan = null)
    {
        $tahunAjaran = TahunAjaran::aktif();

        if (!$tahunAjaran) return null;

        $query = SettingNominalTagihan::query()
            ->where('jenis_tagihan_id', $jenis->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id);

        // 🔥 FILTER BULAN (untuk SPP)
        if ($bulan && $jenis->is_bulanan) {
            $query->whereJsonContains('bulan', $bulan);
        }

        $settings = $query->get();

        // 🔥 SORT PRIORITAS (core logic)
        $setting = $settings->sortByDesc(function ($item) use ($ppdb) {

            if ($item->siswa_id == $ppdb->siswa_id) return 4;
            if ($item->kelas_id == $ppdb->kelas_id) return 3;
            if ($item->lembaga_id == $ppdb->lembaga_id) return 2;

            return 1;
        })->first();

        if ($setting) {
            return [
                'nominal' => $setting->nominal,

                // 🔥 wajib dari parent
                'is_cicilan' => $jenis->is_cicilan,

                'jatuh_tempo' => 3,
            ];
        }

        // 🔥 DEFAULT
        return [
            'nominal' => $jenis->default_nominal,
            'is_cicilan' => $jenis->is_cicilan,
            'jatuh_tempo' => 3,
        ];
    }

    // ======================
    // 🟡 PENDAFTARAN
    // ======================
    public static function buatPendaftaran($ppdb)
    {
        $jenis = JenisTagihan::where('tipe_sistem', 'pendaftaran_ppdb')->first();
        $rekening = Rekening::where('lembaga_id', $ppdb->lembaga_id)->first();
        $tahunAjaran = TahunAjaran::aktif();
    
        if (!$jenis || !$rekening || !$tahunAjaran) return;
    
        // ❌ cegah duplikat
        if (
            Tagihan::where('ppdb_id', $ppdb->id)
                ->where('jenis_tagihan_id', $jenis->id)
                ->exists()
        ) {
            return;
        }
        
        $data = self::resolveNominal($jenis, $ppdb);

        if (!$data || !$data['nominal']) return;
    
        Tagihan::create([
            'ppdb_id' => $ppdb->id,
            'siswa_id' => null,
            'jenis_tagihan_id' => $jenis->id,
            'judul' => $jenis->nama,
            'nominal' => $data['nominal'],
            'nominal_terbayar' => 0,
            'status' => 'belum',
            'rekening_id' => $rekening->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'is_cicilan' => $data['is_cicilan'],
            'jatuh_tempo' => now()->addDays($data['jatuh_tempo']),
        ]);

    }

    // ======================
    // 🟢 DAFTAR ULANG
    // ======================
    public static function buatDaftarUlang($ppdb)
    {
        $jenis = JenisTagihan::where('tipe_sistem', 'daftar_ulang_ppdb')->first();
        $rekening = Rekening::where('lembaga_id', $ppdb->lembaga_id)->first();
        $tahunAjaran = TahunAjaran::aktif();

        if (!$jenis || !$rekening || !$tahunAjaran) return;

        if (Tagihan::where('ppdb_id', $ppdb->id)
            ->where('jenis_tagihan_id', $jenis->id)
            ->exists()) {
            return;
        }

        $data = self::resolveNominal($jenis, $ppdb);

        if (!$data || !$data['nominal']) return;

        Tagihan::create([
            'ppdb_id' => $ppdb->id,
            'siswa_id' => null,
            'jenis_tagihan_id' => $jenis->id,
            'judul' => $jenis->nama,
            'nominal' => $data['nominal'],
            'nominal_terbayar' => 0,
            'status' => 'belum',
            'rekening_id' => $rekening->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'is_cicilan' => $data['is_cicilan'],
            'jatuh_tempo' => now()->addDays($data['jatuh_tempo']),
        ]);
    }
    
    // ======================
    // SETELAH PEMBAYARAN LUNAS
    // ======================
    public static function afterPaid(Tagihan $tagihan): void
    {
        $tagihan->loadMissing('jenisTagihan', 'ppdb');
        $tipeSistem = $tagihan->jenisTagihan?->tipe_sistem;
        switch ($tipeSistem) {
    
            /*
            |--------------------------------------------------------------------------
            | FORMULIR PENDAFTARAN
            |--------------------------------------------------------------------------
            */
            case 'pendaftaran_ppdb':
                if ($tagihan->ppdb) {
                    $tagihan->ppdb->update([
                        'status' => 'formulir',
                    ]);
                }
            break;
    
            /*
            |--------------------------------------------------------------------------
            | DAFTAR ULANG PPDB
            |--------------------------------------------------------------------------
            */
            case 'daftar_ulang_ppdb':
                if ($tagihan->ppdb) {
                    // PENTING: status di sini HARUS 'daftar_ulang', BUKAN
                    // 'aktif'. 'aktif' cuma boleh di-set lewat tombol
                    // "Aktifkan Siswa" (di PpdbResource), karena di situ
                    // yang beneran generate NIS dan bikin record Siswa.
                    // Sebelumnya kode ini langsung set 'aktif' tanpa
                    // pernah bikin Siswa -- makanya status PPDB kelihatan
                    // "Peserta Didik Aktif" padahal datanya nggak pernah
                    // ada di tabel siswa.
                    $tagihan->ppdb->update([
                        'status' => 'daftar_ulang',
                    ]);
                }
            break;
    
            /*
            |--------------------------------------------------------------------------
            | SPP / DSP / DLL
            |--------------------------------------------------------------------------
            */
            default:
                // Tidak melakukan apa-apa
            break;
        }
    }
}