<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Yayasan;
use App\Models\Kurikulum;
use App\Models\AbsensiMapel;
use App\Models\RekapNilai;
use App\Models\TahunAjaran;
use App\Models\RaportNonAkademik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintRaportController extends Controller
{
    public function generate(Request $request, Siswa $siswa)
    {
        /*
        |--------------------------------------------------------------------------
        | JENIS PENILAIAN (PTS / PAS)
        |--------------------------------------------------------------------------
        */

        $jenisPenilaian =
            in_array($request->query('jenis_penilaian'), ['pts', 'pas'])
                ? $request->query('jenis_penilaian')
                : 'pas';

        /*
        |--------------------------------------------------------------------------
        | TAHUN AJARAN
        |--------------------------------------------------------------------------
        | Default ke tahun ajaran aktif kalau tidak dikirim lewat query string,
        | supaya link lama (mis. dari dashboard wali) tetap jalan.
        |--------------------------------------------------------------------------
        */

        $tahunAjaran = $request->query('tahun_ajaran_id')
            ? TahunAjaran::find($request->query('tahun_ajaran_id'))
            : TahunAjaran::query()->where('aktif', true)->first();
        
        /*
        |--------------------------------------------------------------------------
        | LEMBAGA & YAYASAN
        |--------------------------------------------------------------------------
        */
        
        $lembaga = $siswa->kelas?->lembaga;
        $yayasan = $lembaga?->yayasan;

        /*
        |--------------------------------------------------------------------------
        | LOGO (LEMBAGA, FALLBACK KE LOGO YAYASAN)
        |--------------------------------------------------------------------------
        | Sama seperti pola di kwitansi/slip-gaji: di-embed sebagai base64
        | supaya pasti muncul di DomPDF (DomPDF tidak selalu bisa fetch
        | langsung dari URL storage disk r2-public).
        |--------------------------------------------------------------------------
        */

        $logoPath = $lembaga?->logo ?? $yayasan?->logo;
        $logoBase64 = null;

        if ($logoPath) {
            try {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(
                    \Storage::disk('r2-public')->get($logoPath)
                );
            } catch (\Throwable $e) {
                $logoBase64 = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TANDA TANGAN WALI KELAS & KEPALA SEKOLAH
        |--------------------------------------------------------------------------
        */

        $waliKelas = $siswa->kelas?->waliKelas;

        $ttdWaliKelasBase64 = $this->gambarKeBase64($waliKelas?->tanda_tangan);

        $ttdKepalaSekolahBase64 = $this->gambarKeBase64($lembaga?->tanda_tangan_kepala_sekolah);

        /*
        |--------------------------------------------------------------------------
        | QR CODE VERIFIKASI KEASLIAN RAPORT
        |--------------------------------------------------------------------------
        | URL ditandatangani (signed route) supaya siswa/tahunAjaran/jenis di
        | dalamnya tidak bisa diubah tanpa ketahuan -- kalau ada yang
        | mengutak-atik query string-nya, middleware 'signed' otomatis
        | menolak sebelum sampai ke controller verifikasi.
        |--------------------------------------------------------------------------
        */

        $urlVerifikasi = URL::signedRoute('raport.verifikasi', [
            'siswa' => $siswa->id,
            'tahunAjaran' => $tahunAjaran->id,
            'jenisPenilaian' => $jenisPenilaian,
        ]);

        $qrCodeSvg = QrCode::size(90)->generate($urlVerifikasi);

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

            ->where(
                'jenis_penilaian',
                $jenisPenilaian
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
            'jenisPenilaian',
            'lembaga',
            'yayasan',
            'logoBase64',
            'ttdWaliKelasBase64',
            'ttdKepalaSekolahBase64',
            'qrCodeSvg',
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

            strtoupper($jenisPenilaian) .

            '-' .

            $siswa->nama_lengkap .

            '.pdf'
        );
    }

    /**
     * Ambil file gambar dari storage disk r2-public lalu ubah jadi
     * data URI base64, supaya pasti bisa ditampilkan DomPDF (tidak
     * bergantung pada DomPDF bisa fetch langsung dari URL storage).
     * Dipakai untuk logo, tanda tangan wali kelas & kepala sekolah.
     */
    private function gambarKeBase64(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            return 'data:image/png;base64,' . base64_encode(
                \Storage::disk('r2-public')->get($path)
            );
        } catch (\Throwable $e) {
            return null;
        }
    }
}