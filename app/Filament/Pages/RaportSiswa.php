<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Kurikulum;
use App\Models\RekapNilai;
use App\Models\TahunAjaran;
use App\Models\RaportNonAkademik;

class RaportSiswa extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-document-text';
    protected static ?string $navigationGroup =
        'Akademik';
    protected static ?string $navigationLabel =
        'Raport Siswa';
    protected static ?int $navigationSort = 9;
    protected static string $view =
        'filament.pages.raport-siswa';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_RaportSiswa');
    }

    /*
    |--------------------------------------------------------------------------
    | FORM DATA
    |--------------------------------------------------------------------------
    */

    public ?array $data = [];

    /*
    |--------------------------------------------------------------------------
    | RAPORT DATA
    |--------------------------------------------------------------------------
    */

    public ?Siswa $siswa = null;

    public ?TahunAjaran $tahunAjaran = null;

    public array $nilaiAkademik = [];

    public ?RaportNonAkademik $nonAkademik = null;

    public array $summary = [];

    /*
    |--------------------------------------------------------------------------
    | MOUNT
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->form->fill([

            'tahun_ajaran_id' =>

                TahunAjaran::where(
                    'aktif',
                    true
                )->value('id'),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public function form(
        Forms\Form $form
    ): Forms\Form {

        return $form

            ->schema([

                Forms\Components\Section::make(
                    'Filter Raport'
                )

                    ->schema([

                        /*
                        |--------------------------------------------------------------------------
                        | TAHUN AJARAN
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Select::make(
                            'tahun_ajaran_id'
                        )

                            ->label('Tahun Ajaran')

                            ->options(

                                TahunAjaran::query()

                                    ->orderByDesc('id')

                                    ->get()

                                    ->mapWithKeys(
                                        fn ($item) => [

                                            $item->id =>

                                                $item->nama .
                                                ' - ' .
                                                ucfirst(
                                                    $item->semester
                                                ),

                                        ]
                                    )

                            )

                            ->searchable()

                            ->preload()

                            ->live()

                            ->required(),

                        /*
                        |--------------------------------------------------------------------------
                        | KELAS
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Select::make(
                            'kelas_id'
                        )

                            ->label('Kelas')

                            ->options(

                                Kelas::query()

                                    ->orderBy('nama')

                                    ->pluck(
                                        'nama',
                                        'id'
                                    )

                            )

                            ->searchable()

                            ->preload()

                            ->live()

                            ->afterStateUpdated(
                                function (
                                    callable $set
                                ) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | RESET SISWA
                                    |--------------------------------------------------------------------------
                                    */

                                    $set(
                                        'siswa_id',
                                        null
                                    );

                                    $this->resetRaport();
                                }
                            )

                            ->required(),

                        /*
                        |--------------------------------------------------------------------------
                        | SISWA
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Select::make(
                            'siswa_id'
                        )

                            ->label('Siswa')

                            ->options(function () {

                                if (
                                    empty(
                                        $this->data['kelas_id']
                                    )
                                ) {
                                    return [];
                                }

                                return Siswa::query()

                                    ->where(
                                        'kelas_id',
                                        $this->data['kelas_id']
                                    )

                                    ->orderBy(
                                        'nama_lengkap'
                                    )

                                    ->pluck(
                                        'nama_lengkap',
                                        'id'
                                    );
                            })

                            ->searchable()

                            ->preload()

                            ->live()

                            ->afterStateUpdated(
                                fn () =>
                                $this->loadRaport()
                            )

                            ->required(),

                    ])

                    ->columns(3),

            ])

            ->statePath('data');
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD RAPORT
    |--------------------------------------------------------------------------
    */

    public function loadRaport(): void
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        if (

            empty(
                $this->data['tahun_ajaran_id']
            ) ||

            empty(
                $this->data['kelas_id']
            ) ||

            empty(
                $this->data['siswa_id']
            )

        ) {

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SISWA
        |--------------------------------------------------------------------------
        */

        $this->siswa = Siswa::with([
            'kelas',
        ])->find(

            $this->data['siswa_id']
        );

        /*
        |--------------------------------------------------------------------------
        | TAHUN AJARAN
        |--------------------------------------------------------------------------
        */

        $this->tahunAjaran =
            TahunAjaran::find(
                $this->data['tahun_ajaran_id']
            );

        /*
        |--------------------------------------------------------------------------
        | GURU MAPEL SESUAI KURIKULUM
        |--------------------------------------------------------------------------
        */

        $guruMapel = Kurikulum::query()

            ->with('pegawai')

            ->where(
                'kelas_id',
                $this->data['kelas_id']
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
                $this->data['siswa_id']
            )

            ->where(
                'kelas_id',
                $this->data['kelas_id']
            )

            ->where(
                'tahun_ajaran_id',
                $this->data['tahun_ajaran_id']
            )

            ->orderBy('mapel_id')

            ->get();

        $this->nilaiAkademik = [];

        foreach ($rekapNilai as $item) {

            $this->nilaiAkademik[] = [

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

        $this->nonAkademik =
            RaportNonAkademik::query()

                ->with([
                    'kepribadians',
                    'ekstrakurikulers',
                ])

                ->where(
                    'siswa_id',
                    $this->data['siswa_id']
                )

                ->where(
                    'kelas_id',
                    $this->data['kelas_id']
                )

                ->where(
                    'tahun_ajaran_id',
                    $this->data['tahun_ajaran_id']
                )

                ->first();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $nilaiCollection =
            collect(
                $this->nilaiAkademik
            );

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

        $this->summary = [

            'jumlah_mapel' =>

                $nilaiCollection->count(),

            'rata_rata' =>
                $rataRata,

            'tertinggi' =>
                $tertinggi,

            'terendah' =>
                $terendah,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RESET RAPORT
    |--------------------------------------------------------------------------
    */

    protected function resetRaport(): void
    {
        $this->siswa = null;

        $this->tahunAjaran = null;

        $this->nilaiAkademik = [];

        $this->nonAkademik = null;

        $this->summary = [];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}