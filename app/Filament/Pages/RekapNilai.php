<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\TahunAjaran;
use App\Models\Kurikulum;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class RekapNilai extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Rekap Nilai';
    protected static ?int $navigationSort = 8;
    protected static string $view = 'filament.pages.rekap-nilai';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_RekapNilai');
    }
    public ?array $data = [];
    public array $rekap = [];

    public function mount(): void
    {
        $tahunAjaran = TahunAjaran::where('aktif', true)
            ->first();

        $this->form->fill([

            'tahun_ajaran_id' => $tahunAjaran?->id,

            'tahun_ajaran_label' =>
                $tahunAjaran?->nama .
                ' - ' .
                ucfirst($tahunAjaran?->semester),

        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Filter Rekap Nilai')
                    ->schema([

                        Forms\Components\TextInput::make('tahun_ajaran_label')
                            ->label('Tahun Ajaran')
                            ->readOnly()
                            ->dehydrated(false),

                        Forms\Components\Hidden::make('tahun_ajaran_id'),

                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(
                                Kelas::pluck('nama', 'id')
                            )
                            ->searchable()
                            ->required(),

                    ])
                    ->columns(2),

            ])
            ->statePath('data');
    }

    public function tampilkan()
    {
        $this->rekap = [];

        $kurikulums = Kurikulum::with([
                'mataPelajaran',
                'pegawai',
            ])
            ->where('kelas_id', $this->data['kelas_id'])
            ->get();

        foreach ($kurikulums as $kurikulum) {

            $nilai = Nilai::where(
                    'tahun_ajaran_id',
                    $this->data['tahun_ajaran_id']
                )
                ->where(
                    'kelas_id',
                    $this->data['kelas_id']
                )
                ->where(
                    'mapel_id',
                    $kurikulum->mata_pelajaran_id
                )
                ->get();

            $this->rekap[] = [

                'mapel_id' => $kurikulum->mata_pelajaran_id,

                'mapel' =>
                    $kurikulum->mataPelajaran->nama ?? '-',

                'guru' =>
                    $kurikulum->pegawai->nama ?? '-',

                'tugas' =>
                    $nilai->where(
                        'tipe_nilai',
                        'tugas'
                    )->count()
                        ? 'Selesai'
                        : 'Belum',

                'harian' =>
                    $nilai->where(
                        'tipe_nilai',
                        'harian'
                    )->count()
                        ? 'Selesai'
                        : 'Belum',

                'uts' =>
                    $nilai->where(
                        'tipe_nilai',
                        'uts'
                    )->count()
                        ? 'Selesai'
                        : 'Belum',

                'uas' =>
                    $nilai->where(
                        'tipe_nilai',
                        'uas'
                    )->count()
                        ? 'Selesai'
                        : 'Belum',

                'progress' => collect([

                    $nilai->where(
                        'tipe_nilai',
                        'tugas'
                    )->count(),

                    $nilai->where(
                        'tipe_nilai',
                        'harian'
                    )->count(),

                    $nilai->where(
                        'tipe_nilai',
                        'uts'
                    )->count(),

                    $nilai->where(
                        'tipe_nilai',
                        'uas'
                    )->count(),

                ])->filter()->count() * 25,

            ];
        }
    }
}