<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
            ->with(['kelas', 'lembaga.yayasan'])
            ->orderBy('nama_lengkap')
            ->get();

        // Konversi foto tiap siswa jadi base64 SEKALI di sini (bukan
        // di dalam view/loop blade) -- supaya kalau ada 1 foto rusak/
        // hilang, cuma foto itu yang kosong, tidak menggagalkan
        // seluruh proses generate PDF. DomPDF juga JAUH lebih stabil
        // menerima gambar sebagai data URI base64 dibanding link
        // langsung ke R2 (yang perlu fetch remote, gampang timeout
        // kalau siswa-nya banyak).
        //
        // Logo (per lembaga siswa itu, fallback ke logo Yayasan kalau
        // Lembaga-nya sendiri belum upload logo) juga di-cache di sini
        // per lembaga_id -- supaya kalau 1 lembaga punya ratusan
        // siswa, logonya cuma di-convert base64 SEKALI, bukan diulang
        // tiap siswa (buang-buang waktu proses).
        $logoCache = [];

        foreach ($siswas as $siswa) {
            $siswa->foto_base64 = $this->fotoSiswaKeBase64($siswa->foto);

            $lembagaId = $siswa->lembaga_id;

            if (! array_key_exists($lembagaId, $logoCache)) {
                $logoPath = $siswa->lembaga?->logo ?: $siswa->lembaga?->yayasan?->logo;
                $logoCache[$lembagaId] = $this->fileKeBase64($logoPath);
            }

            $siswa->logo_base64 = $logoCache[$lembagaId];
        }

        $lembaga = $this->lembaga_id
            ? Lembaga::with('yayasan')->find($this->lembaga_id)
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
     * Foto siswa: bukan cuma di-encode base64 mentah -- di-crop dulu
     * pakai Image::cover() (persis pola yang sama dipakai fitur
     * upload foto satu-satu & Upload Foto Massal, lihat
     * SiswaResource.php/PegawaiResource.php) supaya benar-benar
     * PENUH mengisi kotak foto tanpa sisa spasi putih. DomPDF TIDAK
     * mendukung CSS object-fit dengan baik -- kalau cuma andalkan
     * <img width height> + object-fit:cover, hasilnya kadang masih
     * ada letterbox/spasi kosong kalau rasio foto asli beda dari
     * kotaknya. Meng-crop gambarnya sendiri di sini (bukan lewat CSS)
     * itu solusi yang pasti bekerja di DomPDF, apa pun rasio foto
     * aslinya.
     */
    protected function fotoSiswaKeBase64(?string $path): ?string
    {
        $isiFile = $this->ambilIsiFile($path);

        if (! $isiFile) {
            return null;
        }

        try {
            // Rasio 100:130 (sama seperti kotak foto di blade),
            // di-scale up 3x (300x390) supaya tetap tajam waktu
            // dicetak, bukan pecah.
            $webp = Image::decode($isiFile)
                ->cover(300, 390)
                ->encodeUsingFileExtension('jpg', quality: 85);

            return 'data:image/jpeg;base64,' . base64_encode((string) $webp);
        } catch (\Throwable $e) {
            // Kalau file-nya rusak/bukan gambar valid, jangan
            // gagalkan seluruh PDF -- cukup kosongkan foto siswa itu.
            return null;
        }
    }

    /**
     * Logo (lembaga/yayasan) -- TIDAK di-crop paksa seperti foto
     * siswa (logo biasanya sudah dalam bentuk yang pas apa adanya,
     * meng-crop paksa bisa memotong bagian penting logo), cukup
     * base64-kan isi filenya langsung.
     */
    protected function fileKeBase64(?string $path): ?string
    {
        $isiFile = $this->ambilIsiFile($path);

        if (! $isiFile) {
            return null;
        }

        $mime = $this->tebakMime($path);

        return 'data:' . $mime . ';base64,' . base64_encode($isiFile);
    }

    /**
     * Cek r2-public dulu (lokasi standar sekarang), kalau tidak
     * ketemu coba disk lokal 'public' (kemungkinan file lama sebelum
     * semua foto/logo dipindah ke R2). Kembalikan null kalau
     * dua-duanya tidak ketemu -- bukan lempar error.
     */
    protected function ambilIsiFile(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        foreach (['r2-public', 'public'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->get($path);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    protected function tebakMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}

