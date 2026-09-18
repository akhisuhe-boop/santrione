<?php

namespace App\Services;

use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kurikulum;
use App\Models\RekapNilai;

class RekapNilaiService
{
    public static function generate(
        int $kelasId,
        int $mapelId,
        int $tahunAjaranId
    ): void {

        $kurikulum = Kurikulum::with('mataPelajaran')
        ->where('kelas_id', $kelasId)
        ->where('mata_pelajaran_id', $mapelId)
        ->first();

        $siswas = Siswa::where('kelas_id', $kelasId)->get();

        foreach ($siswas as $siswa) {

            $nilai = Nilai::where([
                'siswa_id' => $siswa->id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'tahun_ajaran_id' => $tahunAjaranId,
            ])->get()->keyBy('tipe_nilai');

            $tugas  = $nilai['tugas']->nilai  ?? null;
            $harian = $nilai['harian']->nilai ?? null;
            $uts    = $nilai['uts']->nilai    ?? null;
            $uas    = $nilai['uas']->nilai    ?? null;

            /*
            |--------------------------------------------------------------------------
            | REKAP PTS (PENILAIAN TENGAH SEMESTER)
            |--------------------------------------------------------------------------
            | Butuh tugas + harian + uts. UAS belum wajib ada.
            |--------------------------------------------------------------------------
            */

            self::generateSatuJenis(
                jenisPenilaian: 'pts',
                siswaId: $siswa->id,
                kelasId: $kelasId,
                mapelId: $mapelId,
                tahunAjaranId: $tahunAjaranId,
                guruId: $kurikulum?->pegawai_id,
                kompetensi: $kurikulum?->mataPelajaran?->kompetensi,
                syaratTerpenuhi: (
                    $tugas !== null &&
                    $harian !== null &&
                    $uts !== null
                ),
                nilaiAkhir: NilaiService::hitungNilaiAkhirPts(
                    $tugas,
                    $harian,
                    $uts
                ),
            );

            /*
            |--------------------------------------------------------------------------
            | REKAP PAS (PENILAIAN AKHIR SEMESTER)
            |--------------------------------------------------------------------------
            | Butuh tugas + harian + uts + uas lengkap, seperti sebelumnya.
            |--------------------------------------------------------------------------
            */

            self::generateSatuJenis(
                jenisPenilaian: 'pas',
                siswaId: $siswa->id,
                kelasId: $kelasId,
                mapelId: $mapelId,
                tahunAjaranId: $tahunAjaranId,
                guruId: $kurikulum?->pegawai_id,
                kompetensi: $kurikulum?->mataPelajaran?->kompetensi,
                syaratTerpenuhi: (
                    $tugas !== null &&
                    $harian !== null &&
                    $uts !== null &&
                    $uas !== null
                ),
                nilaiAkhir: NilaiService::hitungNilaiAkhir(
                    $tugas,
                    $harian,
                    $uts,
                    $uas
                ),
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE SATU JENIS REKAP (PTS ATAU PAS)
    |--------------------------------------------------------------------------
    */

    protected static function generateSatuJenis(
        string $jenisPenilaian,
        int $siswaId,
        int $kelasId,
        int $mapelId,
        int $tahunAjaranId,
        ?int $guruId,
        ?string $kompetensi,
        bool $syaratTerpenuhi,
        $nilaiAkhir,
    ): void {

        $query = [
            'siswa_id' => $siswaId,
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelId,
            'tahun_ajaran_id' => $tahunAjaranId,
            'jenis_penilaian' => $jenisPenilaian,
        ];

        if (! $syaratTerpenuhi) {

            RekapNilai::where($query)->delete();

            return;
        }

        RekapNilai::updateOrCreate(

            $query,

            [
                'guru_id' => $guruId,
                'nilai_akhir' => $nilaiAkhir,
                'grade' => NilaiService::generateGrade($nilaiAkhir),
                'deskripsi' => NilaiService::generateDeskripsi(
                    $nilaiAkhir,
                    $kompetensi
                ),
                'status' => 'draft',
            ]

        );
    }
}