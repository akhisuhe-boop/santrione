<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kurikulum;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class InputNilai extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-pencil-square';
    protected static ?string $navigationGroup =
        'Akademik';
    protected static ?string $navigationLabel =
        'Input Nilai';
    protected static ?int $navigationSort = 6;

    protected static string $view =
        'filament.pages.input-nilai';
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('page_InputNilai');
    }

    public ?array $data = [];

    public array $siswas = [];

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make(
    'Informasi Penilaian'
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

                TahunAjaran::where(
                    'aktif',
                    true
                )
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
            ->default(

                TahunAjaran::where(
                    'aktif',
                    true
                )->value('id')

            )
            ->disabled()
            ->dehydrated()
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

                Kelas::orderBy('nama')
                    ->pluck(
                        'nama',
                        'id'
                    )

            )
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(function () {

                /*
                |--------------------------------------------------------------------------
                | RESET MAPEL
                |--------------------------------------------------------------------------
                */
                $this->data['mapel_id'] = null;

                /*
                |--------------------------------------------------------------------------
                | RESET DATA SISWA
                |--------------------------------------------------------------------------
                */
                $this->siswas = [];

            })
            ->required(),

        /*
        |--------------------------------------------------------------------------
        | MAPEL
        |--------------------------------------------------------------------------
        */
        Forms\Components\Select::make(
            'mapel_id'
        )
            ->label('Mapel')
            ->options(function () {

                /*
                |--------------------------------------------------------------------------
                | JIKA KELAS BELUM DIPILIH
                |--------------------------------------------------------------------------
                */
                if (
                    empty(
                        $this->data['kelas_id']
                    )
                ) {
                    return [];
                }

                /*
                |--------------------------------------------------------------------------
                | AMBIL MAPEL SESUAI KURIKULUM KELAS
                |--------------------------------------------------------------------------
                */
                return Kurikulum::where(
                    'kelas_id',
                    $this->data['kelas_id']
                )
                    ->with('mataPelajaran')
                    ->get()

                    /*
                    |--------------------------------------------------------------------------
                    | HINDARI DUPLIKAT MAPEL
                    |--------------------------------------------------------------------------
                    */
                    ->unique('mata_pelajaran_id')

                    /*
                    |--------------------------------------------------------------------------
                    | FORMAT:
                    | [id => nama]
                    |--------------------------------------------------------------------------
                    */
                    ->pluck(
                        'mataPelajaran.nama',
                        'mata_pelajaran_id'
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | SORT NAMA MAPEL
                    |--------------------------------------------------------------------------
                    */
                    ->sort();
            })
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(
                fn () =>
                $this->loadSiswasDanNilai()
            )
            ->required(),

    ])
    ->columns(3)

            ])
            ->statePath('data');
    }

    public function loadSiswasDanNilai()
    {
        if (

            empty($this->data['kelas_id']) ||

            empty($this->data['mapel_id']) ||

            empty($this->data['tahun_ajaran_id'])

        ) {
            return;
        }

        $siswas = Siswa::where(

            'kelas_id',
            $this->data['kelas_id']

        )->get();

        $this->siswas = [];

        foreach ($siswas as $siswa) {

            $this->siswas[] = [

                'id' =>
                    $siswa->id,

                'nama_lengkap' =>
                    $siswa->nama_lengkap,

                'tugas' =>

                    Nilai::where([

                        'siswa_id' =>
                            $siswa->id,

                        'kelas_id' =>
                            $this->data['kelas_id'],

                        'mapel_id' =>
                            $this->data['mapel_id'],

                        'tahun_ajaran_id' =>
                            $this->data['tahun_ajaran_id'],

                        'tipe_nilai' =>
                            'tugas',

                    ])->value('nilai'),

                'harian' =>

                    Nilai::where([

                        'siswa_id' =>
                            $siswa->id,

                        'kelas_id' =>
                            $this->data['kelas_id'],

                        'mapel_id' =>
                            $this->data['mapel_id'],

                        'tahun_ajaran_id' =>
                            $this->data['tahun_ajaran_id'],

                        'tipe_nilai' =>
                            'harian',

                    ])->value('nilai'),

                'uts' =>

                    Nilai::where([

                        'siswa_id' =>
                            $siswa->id,

                        'kelas_id' =>
                            $this->data['kelas_id'],

                        'mapel_id' =>
                            $this->data['mapel_id'],

                        'tahun_ajaran_id' =>
                            $this->data['tahun_ajaran_id'],

                        'tipe_nilai' =>
                            'uts',

                    ])->value('nilai'),

                'uas' =>

                    Nilai::where([

                        'siswa_id' =>
                            $siswa->id,

                        'kelas_id' =>
                            $this->data['kelas_id'],

                        'mapel_id' =>
                            $this->data['mapel_id'],

                        'tahun_ajaran_id' =>
                            $this->data['tahun_ajaran_id'],

                        'tipe_nilai' =>
                            'uas',

                    ])->value('nilai'),

            ];
        }
    }

    public function simpan()
    {
        foreach ($this->siswas as $siswa) {

            $types = [

                'tugas',
                'harian',
                'uts',
                'uas',

            ];

            foreach ($types as $type) {

                $query = [

                    'siswa_id' =>
                        $siswa['id'],

                    'kelas_id' =>
                        $this->data['kelas_id'],

                    'mapel_id' =>
                        $this->data['mapel_id'],

                    'tahun_ajaran_id' =>
                        $this->data['tahun_ajaran_id'],

                    'tipe_nilai' =>
                        $type,

                ];

                if (

                    isset($siswa[$type]) &&

                    $siswa[$type] !== null &&

                    $siswa[$type] !== ''

                ) {

                    Nilai::updateOrCreate(

                        $query,

                        [

                            'nilai' =>
                                $siswa[$type],

                            'is_publish' =>
                                false,

                        ]

                    );

                } else {

                    Nilai::where($query)->delete();

                }
            }
        }

        Notification::make()
            ->title('Berhasil')
            ->body(
                'Nilai siswa berhasil disimpan.'
            )
            ->success()
            ->send();

        $this->loadSiswasDanNilai();
    }
}