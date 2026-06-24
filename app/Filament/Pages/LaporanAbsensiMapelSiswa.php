<?php

namespace App\Filament\Pages;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiMapel;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;

use Filament\Pages\Page;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;

class LaporanAbsensiMapelSiswa extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-academic-cap';

    protected static string $view =
        'filament.pages.laporan-absensi-mapel-siswa';

    protected static ?string $navigationGroup =
        'Absensi';

    protected static ?string $title =
        'Laporan Absensi Mapel Siswa';

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanAbsensiMapelSiswa');
    }

    public ?array $formData = [];

    /*
    |--------------------------------------------------------------------------
    | MOUNT
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->form->fill();
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    public function filter()
    {
        $this->resetTable();
    }

    public function resetFilter()
    {
        $this->formData = [];
        $this->form->fill();
        $this->resetTable();
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public function form(Form $form): Form
    {
        return $form

            ->schema([

                DatePicker::make('tanggal_awal')
                    ->label('Dari Tanggal')
                    ->native(false),

                DatePicker::make('tanggal_akhir')
                    ->label('Sampai Tanggal')
                    ->native(false),

                Select::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->preload()
                    ->options(
                        Kelas::orderBy('nama')
                            ->pluck('nama', 'id')
                    ),

                Select::make('mapel')
                    ->label('Mapel')
                    ->placeholder('Semua Mapel')
                    ->searchable()
                    ->preload()
                    ->options(
                        MataPelajaran::orderBy('nama')
                            ->pluck('nama', 'id')
                    ),

                Group::make([

                    Actions::make([

                        FormAction::make('filter')
                            ->label('Filter')
                            ->icon('heroicon-m-funnel')
                            ->color('primary')
                            ->submit('filter'),

                        FormAction::make('reset')
                            ->label('Reset')
                            ->icon('heroicon-m-arrow-path')
                            ->color('gray')
                            ->action(
                                fn () => $this->resetFilter()
                            ),

                    ])

                ])->extraAttributes([
                    'class' => 'flex items-end h-full pb-1'
                ])

            ])

            ->statePath('formData')
            ->columns(5);
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY FILTER
    |--------------------------------------------------------------------------
    */

    protected function applyFilter($query)
    {
        // FILTER MAPEL
        if ($this->formData['mapel'] ?? null) {

            $query->whereHas(
                'jadwalPelajaran',
                fn ($q) =>

                $q->where(
                    'mata_pelajaran_id',
                    $this->formData['mapel']
                )
            );
        }

        // FILTER TANGGAL
        if (
            ($this->formData['tanggal_awal'] ?? null)
            &&
            ($this->formData['tanggal_akhir'] ?? null)
        ) {

            $query->whereBetween('tanggal', [

                $this->formData['tanggal_awal'],
                $this->formData['tanggal_akhir'],

            ]);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL MAPEL
    |--------------------------------------------------------------------------
    */

    protected function getTotalMapel()
    {
        $query = JadwalPelajaran::query();

        if ($this->formData['mapel'] ?? null) {

            $query->where(
                'mata_pelajaran_id',
                $this->formData['mapel']
            );
        }

        if (
            ($this->formData['tanggal_awal'] ?? null)
            &&
            ($this->formData['tanggal_akhir'] ?? null)
        ) {

            $query->whereBetween('tanggal', [

                $this->formData['tanggal_awal'],
                $this->formData['tanggal_akhir'],

            ]);
        }

        return $query->count();
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()
                    ->when(
                        $this->formData['kelas'] ?? null,
                        fn ($q, $kelas) =>
                        $q->where('kelas_id', $kelas)
                    )

            )

            ->columns([

                TextColumn::make('nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->badge(),

                /*
                |--------------------------------------------------------------------------
                | HADIR
                |--------------------------------------------------------------------------
                */

                TextColumn::make('hadir')
                ->label('Hadir')
                ->badge()
                ->color('success')
                ->getStateUsing(function ($record) {
                    $query = AbsensiMapel::query()
                        ->where('siswa_id', $record->id)
                        ->where('status', 'Hadir')
                        ->whereHas(
                            'jurnalMengajar',
                            fn ($q) =>
                                $q->where('status', 'valid')
                        );

                    $this->applyFilter($query);
                    return $query->count();
                }),

                /*
                |--------------------------------------------------------------------------
                | IZIN
                |--------------------------------------------------------------------------
                */

                TextColumn::make('izin')
                ->label('Izin')
                ->badge()
                ->color('warning')
                ->getStateUsing(function ($record) {
                    $query = AbsensiMapel::query()
                        ->where('siswa_id', $record->id)
                        ->where('status', 'Izin')
                        ->whereHas(
                            'jurnalMengajar',
                            fn ($q) =>
                                $q->where('status', 'valid')
                        );
                    $this->applyFilter($query);
                    return $query->count();
                }),

                /*
                |--------------------------------------------------------------------------
                | SAKIT
                |--------------------------------------------------------------------------
                */

                TextColumn::make('sakit')
                ->label('Sakit')
                ->badge()
                ->color('info')
                ->getStateUsing(function ($record) {
                    $query = AbsensiMapel::query()
                        ->where('siswa_id', $record->id)
                        ->where('status', 'Sakit')
                        ->whereHas(
                            'jurnalMengajar',
                            fn ($q) =>
                                $q->where('status', 'valid')
                        );
                    $this->applyFilter($query);
                    return $query->count();
                }),

                /*
                |--------------------------------------------------------------------------
                | ALPHA
                |--------------------------------------------------------------------------
                */

                TextColumn::make('alpha')
                ->label('Alpha')
                ->badge()
                ->color('danger')
                ->getStateUsing(function ($record) {
                    $query = AbsensiMapel::query()
                        ->where('siswa_id', $record->id)
                        ->where('status', 'Alpha')
                        ->whereHas(
                            'jurnalMengajar',
                            fn ($q) =>
                                $q->where('status', 'valid')
                        );
                    $this->applyFilter($query);
                    return $query->count();
                }),

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                TextColumn::make('total')
                ->label('Total')
                ->badge()
                ->color('primary')
                ->getStateUsing(function ($record) {
                    $query = AbsensiMapel::query()
                        ->where('siswa_id', $record->id)
                        ->whereHas(
                            'jurnalMengajar',
                            fn ($q) =>
                                $q->where('status', 'valid')
                        );
                    $this->applyFilter($query);
                    return $query->count();
                }),
            ])

            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}