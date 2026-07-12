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

            if (
                $tugas === null ||
                $harian === null ||
                $uts === null ||
                $uas === null
            ) {
            
                RekapNilai::where([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $kelasId,
                    'mapel_id' => $mapelId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                ])->delete();
            
                continue;
            }

            $nilaiAkhir = NilaiService::hitungNilaiAkhir(
                $tugas,
                $harian,
                $uts,
                $uas
            );

            RekapNilai::updateOrCreate(

                [
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $kelasId,
                    'mapel_id' => $mapelId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                ],

                [
                    'guru_id' => $kurikulum?->pegawai_id,
                    'nilai_akhir' => $nilaiAkhir,
                    'grade' => NilaiService::generateGrade($nilaiAkhir),
                    'deskripsi' => NilaiService::generateDeskripsi(
                        $nilaiAkhir,
                        $kurikulum?->mataPelajaran?->kompetensi
                    ),
                    'status' => 'draft',
                ]

            );
        }
    }
}