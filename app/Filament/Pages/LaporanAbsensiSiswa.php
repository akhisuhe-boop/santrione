<?php

namespace App\Filament\Pages;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\JadwalKegiatan;

use Filament\Pages\Page;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;

class LaporanAbsensiSiswa extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-academic-cap';

    protected static string $view =
        'filament.pages.laporan-absensi-siswa';

    protected static ?string $navigationGroup =
        'Absensi';

    protected static ?string $title =
        'Laporan Absensi Siswa';

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanAbsensiSiswa');
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
                    ->placeholder('Pilih Tanggal Awal')
                    ->native(false),

                DatePicker::make('tanggal_akhir')
                    ->label('Sampai Tanggal')
                    ->placeholder('Pilih Tanggal Akhir')
                    ->native(false),

                Select::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->preload()
                    ->options(
                        Kelas::orderBy('nama')
                            ->pluck('nama', 'id')
                    ),

                Select::make('kegiatan')
                    ->label('Kegiatan')
                    ->placeholder('Semua Kegiatan')
                    ->searchable()
                    ->preload()
                    ->options(

                        JadwalKegiatan::with('template')

                            ->whereHas('template', function ($q) {
                                $q->where('tipe', 'siswa');
                            })

                            ->orderBy('tanggal', 'desc')

                            ->get()

                            ->mapWithKeys(function ($item) {

                                return [

                                    $item->id =>

                                        ($item->template->nama_kegiatan ?? '-')

                                        . ' - ' .

                                        \Carbon\Carbon::parse(
                                            $item->tanggal
                                        )->format('d/m/Y')

                                ];
                            })

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
        if ($this->formData['kegiatan'] ?? null) {

            $query->where(
                'jadwal_kegiatan_id',
                $this->formData['kegiatan']
            );
        }

        if (
            ($this->formData['tanggal_awal'] ?? null)
            &&
            ($this->formData['tanggal_akhir'] ?? null)
        ) {

            $query->whereHas(
                'jadwalKegiatan',
                function ($q) {

                    $q->whereBetween('tanggal', [

                        $this->formData['tanggal_awal'],

                        $this->formData['tanggal_akhir'],

                    ]);

                }
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL KEGIATAN
    |--------------------------------------------------------------------------
    */

    protected function getTotalKegiatan()
    {
        $query = JadwalKegiatan::query()

            ->whereHas('template', function ($q) {

                $q->where('tipe', 'siswa');

            });

        if ($this->formData['kegiatan'] ?? null) {

            $query->where(
                'id',
                $this->formData['kegiatan']
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

                TextColumn::make('hadir')

                    ->label('Hadir')

                    ->badge()

                    ->color('success')

                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'siswa_id',
                            $record->id
                        )

                            ->where('tipe', 'siswa')

                            ->where('status', 'Hadir');

                        $this->applyFilter($query);

                        return $query->count();

                    }),

                TextColumn::make('waktu_absen')
                    ->label('Waktu Absen')
                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'siswa_id',
                            $record->id
                        )
                        ->where('tipe', 'siswa')
                        ->whereIn('status', [
                            'Hadir',
                            'Terlambat'
                        ]);

                        $this->applyFilter($query);

                        $absen = $query->latest('jam_scan')->first();

                        return $absen?->jam_scan
                            ? \Carbon\Carbon::parse($absen->jam_scan)
                                ->format('H:i')
                            : '-';
                    }),

                TextColumn::make('terlambat')

                    ->label('Terlambat')

                    ->badge()

                    ->color('warning')

                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'siswa_id',
                            $record->id
                        )

                            ->where('tipe', 'siswa')

                            ->where('status', 'Terlambat');

                        $this->applyFilter($query);

                        return $query->count();

                    }),

                TextColumn::make('total_telat')
                    ->label('Total Telat')
                    ->badge()
                    ->color('danger')

                    ->getStateUsing(function ($record) {

                        $query = Absensi::with(
                            'jadwalKegiatan.template'
                        )
                        ->where('siswa_id', $record->id)
                        ->where('tipe', 'siswa')
                        ->where('status', 'Terlambat');

                        $this->applyFilter($query);

                        $absensis = $query->get();

                        if ($absensis->count() == 0) {
                            return '0 Menit';
                        }

                        $totalMenit = 0;

                        foreach ($absensis as $absen) {

                            if (
                                !$absen->jadwalKegiatan ||
                                !$absen->jam_scan
                            ) {
                                continue;
                            }

                            $tanggal = \Carbon\Carbon::parse(
                                $absen->jam_scan
                            )->format('Y-m-d');

                            $jamMulai = \Carbon\Carbon::parse(
                                $tanggal . ' ' .
                                $absen->jadwalKegiatan->jam_mulai
                            );

                            $toleransi = $absen
                                ->jadwalKegiatan
                                ->template
                                ->toleransi_telat ?? 0;

                            $batasTelat = $jamMulai
                                ->copy()
                                ->addMinutes($toleransi);

                            $jamScan = \Carbon\Carbon::parse(
                                $absen->jam_scan
                            );

                            $menitTelat = $batasTelat
                                ->diffInMinutes(
                                    $jamScan,
                                    false
                                );

                            if ($menitTelat < 0) {
                                $menitTelat = 0;
                            }

                            $totalMenit += $menitTelat;
                        }

                        return ceil($totalMenit) . ' Menit';
                    }),

                TextColumn::make('izin')

                    ->label('Izin')

                    ->badge()

                    ->color('info')

                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'siswa_id',
                            $record->id
                        )

                            ->where('tipe', 'siswa')

                            ->where('status', 'Izin');

                        $this->applyFilter($query);

                        return $query->count();

                    }),

                TextColumn::make('sakit')

                    ->label('Sakit')

                    ->badge()

                    ->color('gray')

                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'siswa_id',
                            $record->id
                        )

                            ->where('tipe', 'siswa')

                            ->where('status', 'Sakit');

                        $this->applyFilter($query);

                        return $query->count();

                    }),

                TextColumn::make('alpha')

                    ->label('Alpha')

                    ->badge()

                    ->color('danger')

                    ->getStateUsing(function ($record) {

                        $totalKegiatan =
                            $this->getTotalKegiatan();

                        $totalAbsen = Absensi::where(
                            'siswa_id',
                            $record->id
                        )

                            ->where('tipe', 'siswa')

                            ->whereIn('status', [

                                'Hadir',

                                'Terlambat',

                                'Izin',

                                'Sakit'

                            ]);

                        $this->applyFilter($totalAbsen);

                        $totalAbsen =
                            $totalAbsen->count();

                        $alpha =
                            $totalKegiatan - $totalAbsen;

                        return $alpha > 0
                            ? $alpha
                            : 0;

                    }),

                TextColumn::make('total')

                    ->label('Total')

                    ->badge()

                    ->color('primary')

                    ->getStateUsing(function () {

                        return $this->getTotalKegiatan();

                    }),

            ])

            ->actions([
                Action::make('edit_status')
                    ->label('Edit Kehadiran')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Select::make('status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Terlambat' => 'Terlambat',
                                'Izin' => 'Izin',
                                'Sakit' => 'Sakit',
                            ])
                            ->required()
                    ])

                    ->action(function (
                        array $data,
                        $record
                    ) {

                        if (!($this->formData['kegiatan'] ?? null)) {
                            return;
                        }

                        Absensi::updateOrCreate(
                            [
                                'siswa_id' => $record->id,
                                'jadwal_kegiatan_id' =>
                                    $this->formData['kegiatan'],
                                'tipe' => 'siswa',
                            ],

                            [
                                'status' => $data['status'],
                                'jam_scan' => now(),
                                'metode' => 'manual',
                            ]
                        );
                    })
            ])

            ->defaultPaginationPageOption(10)

            ->paginated([10, 25, 50, 100])

            ->striped();
    }
}