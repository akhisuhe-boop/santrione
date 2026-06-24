<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kurikulum;
use App\Models\AbsensiMapel;
use App\Models\RekapNilai;
use App\Models\TahunAjaran;
use App\Models\RaportNonAkademik;

use Barryvdh\DomPDF\Facade\Pdf;

class PrintRaportController extends Controller
{
    public function generate(Siswa $siswa)
    {
        /*
        |--------------------------------------------------------------------------
        | TAHUN AJARAN AKTIF
        |--------------------------------------------------------------------------
        */

        $tahunAjaran = TahunAjaran::query()

            ->where('aktif', true)

            ->first();

        /*
        |--------------------------------------------------------------------------
        | GURU MAPEL SESUAI KURIKULUM
        |--------------------------------------------------------------------------
        */

        $guruMapel = Kurikulum::query()

            ->with('pegawai')

            ->where(
                'kelas_id',
                $siswa->kelas_id
            )

            ->get()

            ->keyBy('mata_pelajaran_id');

        /*
        |--------------------------------------------------------------------------
        | NILAI AKADEMIK
        |--------------------------------------------------------------------------
        */

        $rekapNilai = RekapNilai::query()

            ->with([
                'mapel',
            ])

            ->where(
                'siswa_id',
                $siswa->id
            )

            ->where(
                'kelas_id',
                $siswa->kelas_id
            )

            ->where(
                'tahun_ajaran_id',
                $tahunAjaran->id
            )

            ->orderBy('mapel_id')

            ->get();

        $nilaiAkademik = [];

        foreach ($rekapNilai as $item) {

            $nilaiAkademik[] = [

                'mapel' =>

                    $item->mapel->nama ?? '-',

                'guru' =>

                    $guruMapel[$item->mapel_id]
                        ->pegawai
                        ->nama ?? '-',

                'nilai_akhir' =>

                    $item->nilai_akhir
                        ? round(
                            $item->nilai_akhir
                        )
                        : 0,

                'grade' =>

                    $item->grade ?? '-',

                'deskripsi' =>

                    $item->deskripsi ?? '-',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | NON AKADEMIK
        |--------------------------------------------------------------------------
        */

        $nonAkademik =
            RaportNonAkademik::query()

                ->with([
                    'kepribadians',
                    'ekstrakurikulers',
                ])

                ->where(
                    'siswa_id',
                    $siswa->id
                )

                ->where(
                    'kelas_id',
                    $siswa->kelas_id
                )

                ->where(
                    'tahun_ajaran_id',
                    $tahunAjaran->id
                )

                ->first();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $nilaiCollection =
            collect($nilaiAkademik);

        $total =

            $nilaiCollection
                ->sum('nilai_akhir');

        $rataRata =

            $nilaiCollection->count() > 0

                ? round(
                    $nilaiCollection
                        ->avg('nilai_akhir')
                )

                : 0;

        $tertinggi =

            $nilaiCollection->max(
                'nilai_akhir'
            ) ?? 0;

        $terendah =

            $nilaiCollection->min(
                'nilai_akhir'
            ) ?? 0;

        /*
        |--------------------------------------------------------------------------
        | ABSENSI SISWA
        |--------------------------------------------------------------------------
        */

        $absensi = AbsensiMapel::query()

        ->where(
            'siswa_id',
            $siswa->id
        )

        ->get();

        $absensiSummary = [

            'hadir' =>

                $absensi

                    ->where(
                        'status',
                        'Hadir'
                    )

                    ->count(),

            'izin' =>

                $absensi

                    ->where(
                        'status',
                        'Izin'
                    )

                    ->count(),

            'sakit' =>

                $absensi

                    ->where(
                        'status',
                        'Sakit'
                    )

                    ->count(),

            'alpha' =>

                $absensi

                    ->where(
                        'status',
                        'Alpha'
                    )

                    ->count(),

        ];

        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'exports.raport',
            compact(
                'siswa',
                'tahunAjaran',
                'nilaiAkademik',
                'nonAkademik',
                'total',
                'rataRata',
                'tertinggi',
                'terendah',
                'absensiSummary',
            )
        )

        ->setPaper(
            'a4',
            'portrait'
        );

        /*
        |--------------------------------------------------------------------------
        | STREAM PDF
        |--------------------------------------------------------------------------
        */

        return $pdf->stream(

            'raport-' .

            $siswa->nama_lengkap .

            '.pdf'
        );
    }
}