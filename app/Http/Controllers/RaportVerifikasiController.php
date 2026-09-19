<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TahunAjaran;

class RaportVerifikasiController extends Controller
{
    /**
     * Halaman publik konfirmasi keaslian raport, dibuka lewat QR code
     * yang dicetak di PDF raport. URL-nya ditandatangani (signed route)
     * jadi tidak bisa dipalsukan/diubah tanpa ketahuan -- kalau ada
     * parameter yang diutak-atik, middleware 'signed' otomatis menolak
     * sebelum masuk ke sini.
     *
     * SENGAJA tidak menampilkan nilai/rincian akademik di sini -- cuma
     * konfirmasi identitas & keabsahan, supaya QR code tidak jadi celah
     * kebocoran nilai ke siapapun yang iseng scan.
     */
    public function show(int $siswaId, int $tahunAjaranId, string $jenisPenilaian)
    {
        $siswa = Siswa::with('kelas.lembaga.yayasan')->findOrFail($siswaId);

        $tahunAjaran = TahunAjaran::findOrFail($tahunAjaranId);

        $lembaga = $siswa->kelas?->lembaga;

        return view('raport.verifikasi', compact(
            'siswa',
            'tahunAjaran',
            'jenisPenilaian',
            'lembaga'
        ));
    }
}
