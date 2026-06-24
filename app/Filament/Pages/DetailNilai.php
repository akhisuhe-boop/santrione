<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kurikulum;
use App\Models\RekapNilai;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;

use App\Services\NilaiService;

class DetailNilai extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug =
        'detail-nilai/{kelas}/{mapel}/{tahun}';

    protected static string $view =
        'filament.pages.detail-nilai';

    public $kelas;

    public $mapel;

    public $tahunAjaran;

    public $guru;

    public array $siswas = [];

    public array $statistik = [];

    public function mount(
        $kelas,
        $mapel,
        $tahun
    ) {

        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $this->kelas =
            Kelas::findOrFail($kelas);

        $this->mapel =
            MataPelajaran::findOrFail($mapel);

        $this->tahunAjaran =
            TahunAjaran::findOrFail($tahun);

        /*
        |--------------------------------------------------------------------------
        | GURU PENGAMPU
        |--------------------------------------------------------------------------
        */

        $kurikulum = Kurikulum::with('pegawai')

            ->where('kelas_id', $kelas)

            ->where(
                'mata_pelajaran_id',
                $mapel
            )

            ->first();

        $this->guru =
            $kurikulum?->pegawai?->nama ?? '-';

        /*
        |--------------------------------------------------------------------------
        | SISWA
        |--------------------------------------------------------------------------
        */

        $siswas = Siswa::where(
            'kelas_id',
            $kelas
        )->get();

        foreach ($siswas as $siswa) {

            /*
            |--------------------------------------------------------------------------
            | NILAI
            |--------------------------------------------------------------------------
            */

            $tugas = Nilai::where([

                'siswa_id' =>
                    $siswa->id,

                'kelas_id' =>
                    $kelas,

                'mapel_id' =>
                    $mapel,

                'tahun_ajaran_id' =>
                    $tahun,

                'tipe_nilai' =>
                    'tugas',

            ])->value('nilai');

            $harian = Nilai::where([

                'siswa_id' =>
                    $siswa->id,

                'kelas_id' =>
                    $kelas,

                'mapel_id' =>
                    $mapel,

                'tahun_ajaran_id' =>
                    $tahun,

                'tipe_nilai' =>
                    'harian',

            ])->value('nilai');

            $uts = Nilai::where([

                'siswa_id' =>
                    $siswa->id,

                'kelas_id' =>
                    $kelas,

                'mapel_id' =>
                    $mapel,

                'tahun_ajaran_id' =>
                    $tahun,

                'tipe_nilai' =>
                    'uts',

            ])->value('nilai');

            $uas = Nilai::where([

                'siswa_id' =>
                    $siswa->id,

                'kelas_id' =>
                    $kelas,

                'mapel_id' =>
                    $mapel,

                'tahun_ajaran_id' =>
                    $tahun,

                'tipe_nilai' =>
                    'uas',

            ])->value('nilai');

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $status = (

                $tugas !== null &&
                $harian !== null &&
                $uts !== null &&
                $uas !== null

            )

                ? 'Lengkap'
                : 'Belum Lengkap';

            /*
            |--------------------------------------------------------------------------
            | NILAI AKHIR
            |--------------------------------------------------------------------------
            */

            $nilaiAkhir = null;

            $grade = '-';

            $deskripsi = '-';

            if ($status == 'Lengkap') {

                $nilaiAkhir =
                    NilaiService::hitungNilaiAkhir(

                        $tugas,
                        $harian,
                        $uts,
                        $uas

                    );

                $grade =
                    NilaiService::generateGrade(
                        $nilaiAkhir
                    );

                $deskripsi =
                    NilaiService::generateDeskripsi(
                        $nilaiAkhir
                    );

                /*
                |--------------------------------------------------------------------------
                | SIMPAN REKAP NILAI
                |--------------------------------------------------------------------------
                */

                RekapNilai::updateOrCreate(

                    [

                        'siswa_id' =>
                            $siswa->id,

                        'kelas_id' =>
                            $kelas,

                        'mapel_id' =>
                            $mapel,

                        'tahun_ajaran_id' =>
                            $tahun,

                    ],

                    [

                        'guru_id' =>
                            $kurikulum?->pegawai_id,

                        'nilai_akhir' =>
                            $nilaiAkhir,

                        'grade' =>
                            $grade,

                        'deskripsi' =>
                            $deskripsi,

                        'status' =>
                            'draft',

                    ]

                );
            }

            /*
            |--------------------------------------------------------------------------
            | DATA SISWA
            |--------------------------------------------------------------------------
            */

            $this->siswas[] = [

                'nama' =>
                    $siswa->nama_lengkap,

                'tugas' =>
                    $tugas,

                'harian' =>
                    $harian,

                'uts' =>
                    $uts,

                'uas' =>
                    $uas,

                'nilai_akhir' =>

                    $nilaiAkhir !== null

                        ? round($nilaiAkhir)

                        : null,

                'grade' =>
                    $grade,

                'deskripsi' =>
                    $deskripsi,

                'status' =>
                    $status,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIK
        |--------------------------------------------------------------------------
        */

        $total = count($this->siswas);

        $lengkap = collect($this->siswas)

            ->where('status', 'Lengkap')

            ->count();

        $belum = $total - $lengkap;

        $this->statistik = [

            'total' =>
                $total,

            'lengkap' =>
                $lengkap,

            'belum' =>
                $belum,

            'progress' =>

                $total > 0

                    ? round(
                        ($lengkap / $total) * 100
                    )

                    : 0,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER ACTION
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [

            Action::make('kembali')

                ->label('Kembali ke Rekap Nilai')

                ->icon('heroicon-m-arrow-left')

                ->color('primary')

                ->url(url('/admin/rekap-nilai'))

                ->button(),

        ];
    }
}