<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * PERUBAHAN 29 Agt 2026: Export "Buku Induk Siswa" -- beda dari
 * SiswaPdfExport (yang cuma tabel ringkas 5 kolom). Ini format
 * lengkap 1 profil per siswa, sesuai data yang diinput admin
 * (biodata, alamat, data ayah/ibu/wali), TERMASUK foto -- meniru
 * format buku induk fisik yang biasa dipakai sekolah/pesantren.
 *
 * Filter lembaga_id/kelas_id SENGAJA dipakai lagi (pola sama seperti
 * SiswaPdfExport) -- karena tiap siswa jadi 1 halaman penuh, export
 * TANPA filter (semua lembaga/kelas) bisa jadi ratusan halaman
 * sekaligus, berat untuk DomPDF & bisa timeout kalau di-generate
 * langsung tanpa scoping.
 */
class BukuIndukPdfExport
{
    protected $lembaga_id;
    protected $kelas_id;

    public function __construct($lembaga_id = null, $kelas_id = null)
    {
        $this->lembaga_id = $lembaga_id;
        $this->kelas_id = $kelas_id;
    }

    public function download()
    {
        $query = Siswa::query();

        if ($this->lembaga_id) {
            $query->where('lembaga_id', $this->lembaga_id);
        }

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        $siswas = $query
            ->with(['kelas', 'lembaga'])
            ->orderBy('nama_lengkap')
            ->get();

        // Konversi foto tiap siswa jadi base64 SEKALI di sini (bukan
        // di dalam view/loop blade) -- supaya kalau ada 1 foto rusak/
        // hilang, cuma foto itu yang kosong, tidak menggagalkan
        // seluruh proses generate PDF. DomPDF juga JAUH lebih stabil
        // menerima gambar sebagai data URI base64 dibanding link
        // langsung ke R2 (yang perlu fetch remote, gampang timeout
        // kalau siswa-nya banyak).
        foreach ($siswas as $siswa) {
            $siswa->foto_base64 = $this->fotoKeBase64($siswa->foto);
        }

        $lembaga = $this->lembaga_id
            ? Lembaga::find($this->lembaga_id)
            : null;

        $kelas = $this->kelas_id
            ? Kelas::find($this->kelas_id)
            : null;

        $pdf = Pdf::loadView('exports.buku-induk', [
            'siswas' => $siswas,
            'lembaga' => $lembaga,
            'kelas' => $kelas,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('buku-induk-siswa.pdf');
    }

    /**
     * Cek foto di r2-public dulu (lokasi standar sekarang, lihat
     * FileUpload::make('foto') & fitur Upload Foto Massal), kalau
     * tidak ketemu coba disk lokal 'public' (kemungkinan foto lama
     * hasil Import Excel sebelum semua foto dipindah ke R2). Kalau
     * dua-duanya tidak ada, kembalikan null -- blade akan tampilkan
     * kotak foto kosong, bukan gagal total.
     */
    protected function fotoKeBase64(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        foreach (['r2-public', 'public'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    $isi = Storage::disk($disk)->get($path);
                    $mime = Storage::disk($disk)->mimeType($path) ?? 'image/jpeg';

                    return 'data:' . $mime . ';base64,' . base64_encode($isi);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
