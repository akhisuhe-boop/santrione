<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
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

class LaporanAbsensiPegawai extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-document-chart-bar';

    protected static string $view =
        'filament.pages.laporan-absensi-pegawai';

    protected static ?string $navigationGroup =
        'Absensi';

    protected static ?string $title =
        'Laporan Absensi Pegawai';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanAbsensiPegawai');
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
                /*
                |--------------------------------------------------------------------------
                | TANGGAL AWAL
                |--------------------------------------------------------------------------
                */
                DatePicker::make('tanggal_awal')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->placeholder('Pilih Tanggal Awal'),
                /*
                |--------------------------------------------------------------------------
                | TANGGAL AKHIR
                |--------------------------------------------------------------------------
                */
                DatePicker::make('tanggal_akhir')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->placeholder('Pilih Tanggal Akhir'),
                /*
                |--------------------------------------------------------------------------
                | KEGIATAN
                |--------------------------------------------------------------------------
                */
                Select::make('kegiatan')
    ->label('Kegiatan')
    ->placeholder('Semua Kegiatan')
    ->searchable()
    ->preload()
    ->options(
        JadwalKegiatan::with('template')

            ->whereHas('template', function ($q) {
                $q->where('tipe', 'guru');
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
                /*
                |--------------------------------------------------------------------------
                | BUTTON
                |--------------------------------------------------------------------------
                */
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
                            ->action(fn () => $this->resetFilter()),
                    ])
                ])->extraAttributes([
                    'class' => 'flex items-end h-full pb-1'
                ])
            ])
            ->statePath('formData')
            ->columns(4);
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY FILTER
    |--------------------------------------------------------------------------
    */

    protected function applyFilter($query)
{
    /*
    |--------------------------------------------------------------------------
    | FILTER KEGIATAN
    |--------------------------------------------------------------------------
    */

    if ($this->formData['kegiatan'] ?? null) {

        $query->where(
            'jadwal_kegiatan_id',
            $this->formData['kegiatan']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER TANGGAL
    |--------------------------------------------------------------------------
    */

    if (
        ($this->formData['tanggal_awal'] ?? null)
        &&
        ($this->formData['tanggal_akhir'] ?? null)
    ) {

        $query->whereHas('jadwalKegiatan', function ($q) {

            $q->whereBetween('tanggal', [
                $this->formData['tanggal_awal'],
                $this->formData['tanggal_akhir'],
            ]);

        });
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
    $query = JadwalKegiatan::whereHas('template', function ($q) {
        $q->where('tipe', 'guru');
    });
        /*
        |--------------------------------------------------------------------------
        | FILTER KEGIATAN
        |--------------------------------------------------------------------------
        */

        if ($this->formData['kegiatan'] ?? null) {
            $query->where(
                'id',
                $this->formData['kegiatan']
            );
        }
        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
        */

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
                Pegawai::query()
            )
            ->columns([
                /*
                |--------------------------------------------------------------------------
                | NAMA
                |--------------------------------------------------------------------------
                */
                TextColumn::make('nama')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
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
                        $query = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )
                            ->where('tipe', 'guru')
                            ->where('status', 'Hadir');
                        $this->applyFilter($query);
                        return $query->count();
                    }),
                        
                TextColumn::make('waktu_absen')
                    ->label('Waktu Absen')
                    ->getStateUsing(function ($record) {

                        $query = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )
                        ->where('tipe', 'guru')
                        ->whereIn('status', ['Hadir', 'Terlambat']);

                        $this->applyFilter($query);

                        $absen = $query->latest('jam_scan')->first();

                        return $absen?->jam_scan
                            ? \Carbon\Carbon::parse($absen->jam_scan)
                                ->format('H:i')
                            : '-';
                    }),

                /*
                |--------------------------------------------------------------------------
                | TERLAMBAT
                |--------------------------------------------------------------------------
                */

                TextColumn::make('terlambat')
                    ->label('Terlambat')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function ($record) {
                        $query = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )
                            ->where('tipe', 'guru')
                            ->where('status', 'Terlambat');
                        $this->applyFilter($query);
                        return $query->count();
                    }),              
                
                    TextColumn::make('total_telat')
                        ->label('Total Telat')
                        ->badge()
                        ->color('danger')

                        ->getStateUsing(function ($record) {

                            $query = Absensi::with('jadwalKegiatan.template')
                        ->where('pegawai_id', $record->id)
                        ->where('tipe', 'guru')
                        ->where('status', 'Terlambat');

                    $this->applyFilter($query);

                    $absensis = $query->get();

                    if ($absensis->count() == 0) {
                        return '-';
                    }

                    $totalMenit = 0;

                    foreach ($absensis as $absen) {

                        if (
                            !$absen->jadwalKegiatan ||
                            !$absen->jam_scan
                        ) {
                            continue;
                        }

                        // AMBIL TANGGAL
                        $tanggal = \Carbon\Carbon::parse(
                            $absen->jam_scan
                        )->format('Y-m-d');

                        // JAM MULAI
                        $jamMulai = \Carbon\Carbon::parse(
                            $tanggal . ' ' .
                            $absen->jadwalKegiatan->jam_mulai
                        );

                        // TOLERANSI
                        $toleransi = $absen->jadwalKegiatan
                            ->template
                            ->toleransi_telat ?? 0;

                        // BATAS TELAT
                        $batasTelat = $jamMulai
                            ->copy()
                            ->addMinutes($toleransi);

                        // JAM SCAN
                        $jamScan = \Carbon\Carbon::parse(
                            $absen->jam_scan
                        );

                        // HITUNG TELAT
                        $menitTelat = $batasTelat->diffInMinutes(
                            $jamScan,
                            false
                        );

                        if ($menitTelat < 0) {
                            $menitTelat = 0;
                        }

                        $totalMenit += $menitTelat;
                    }

                    return $totalMenit > 0
                        ? ceil($totalMenit) . ' Menit'
                        : '0 Menit';
                        }),

                /*
                |--------------------------------------------------------------------------
                | IZIN
                |--------------------------------------------------------------------------
                */

                TextColumn::make('izin')
                    ->label('Izin')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        $query = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )
                            ->where('tipe', 'guru')
                            ->where('status', 'Izin');
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
                    ->color('gray')
                    ->getStateUsing(function ($record) {
                        $query = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )
                            ->where('tipe', 'guru')
                            ->where('status', 'Sakit');
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
                        $totalKegiatan =
                            $this->getTotalKegiatan();
                        $totalAbsen = Absensi::where(
                            'pegawai_id',
                            $record->id
                        )

                            ->where('tipe', 'guru')
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
                
                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                TextColumn::make('total')
                    ->label('Total')
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(function () {
                        return $this->getTotalKegiatan();
                    }),
            ])

            /*
            |--------------------------------------------------------------------------
            | ACTION
            |--------------------------------------------------------------------------
            */

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

                        // WAJIB PILIH KEGIATAN
                        if (!($this->formData['kegiatan'] ?? null)) {
                            return;
                        }

                        Absensi::updateOrCreate(
                            [
                                'pegawai_id' => $record->id,
                                'jadwal_kegiatan_id' =>
                                    $this->formData['kegiatan'],
                                'tipe' => 'guru',
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