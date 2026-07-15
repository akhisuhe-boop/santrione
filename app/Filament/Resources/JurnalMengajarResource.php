<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JurnalMengajarResource\Pages;
use App\Models\JurnalMengajar;
use App\Models\MataPelajaran;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Components\Repeater;
use App\Models\PegawaiLembaga;
use App\Models\Siswa;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;


class JurnalMengajarResource extends BaseResource
{
    protected static ?string $model = JurnalMengajar::class;
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Jurnal Mengajar Guru';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
{
    return $form->schema([

        Section::make('Jurnal Mengajar')
            ->schema([

                // 👨‍🏫 Guru
                Select::make('pegawai_id')
                    ->label('Nama Guru')
                    ->relationship('pegawai', 'nama')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('pegawai_lembaga_id', null);
                    }),

                    Select::make('pegawai_lembaga_id')
                    ->label('Jabatan Mengajar')
                    ->options(function (callable $get) {
                        $pegawaiId = $get('pegawai_id');
                        if (!$pegawaiId) {
                            return [];
                        }

                        return PegawaiLembaga::query()
                            ->where('pegawai_id', $pegawaiId)
                            ->with('lembaga')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [
                                    $item->id =>

                                    $item->jabatan
                                    . ' - '
                                    . ($item->lembaga->nama ?? '-')
                                ];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive(),

                // 📅 Tanggal
                DatePicker::make('tanggal')
                    ->label('Tanggal Mengajar')
                    ->required()
                    ->reactive(),

                Select::make('jadwal_pelajaran_id')
                    ->label('Pilih Jadwal')
                    ->required()
                    ->live()
                    ->dehydrated(true)
                    ->disabled(fn (callable $get) => blank($get('pegawai_id')) || blank($get('tanggal')))
                    ->options(function (callable $get) {

                        $pegawaiId = $get('pegawai_id');
                        $tanggal = $get('tanggal');

                        if (blank($pegawaiId) || blank($tanggal)) {
                            return [];
                        }

                        $hariMap = [
                            'Monday' => 'senin',
                            'Tuesday' => 'selasa',
                            'Wednesday' => 'rabu',
                            'Thursday' => 'kamis',
                            'Friday' => 'jumat',
                            'Saturday' => 'sabtu',
                            'Sunday' => 'minggu',
                        ];

                        $hari = $hariMap[\Carbon\Carbon::parse($tanggal)->format('l')] ?? null;

                        return \App\Models\JadwalPelajaran::query()
                        ->select('jadwal_pelajarans.*')
                        ->join(
                            'jam_pelajarans',
                            'jam_pelajarans.id',
                            '=',
                            'jadwal_pelajarans.jam_pelajaran_id'
                        )
                        ->where('jadwal_pelajarans.pegawai_id', $pegawaiId)
                        ->where('jadwal_pelajarans.hari', $hari)
                        ->with([
                            'kelas',
                            'mataPelajaran',
                            'jamPelajaran',
                        ])
                        ->orderBy('jam_pelajarans.urutan')
                        ->get()
                            ->mapWithKeys(fn ($j) => [
    $j->id => sprintf(
        '%s (%s–%s) • %s • %s',
        $j->jamPelajaran->nama,
        date('H:i', strtotime($j->jamPelajaran->jam_mulai)),
        date('H:i', strtotime($j->jamPelajaran->jam_selesai)),
        $j->kelas->nama,
        $j->mataPelajaran->nama,
    ),
]);
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                    $jadwal = \App\Models\JadwalPelajaran::find($state);

                    // jika jadwal tidak ditemukan
                    if (!$jadwal) {
                        $set('absensi_siswa', []);
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | AUTO FILL FIELD
                    |--------------------------------------------------------------------------
                    */
                    $set('kelas_id', $jadwal->kelas_id);

                    $set('mata_pelajaran_id', $jadwal->mata_pelajaran_id);
                    $set('jam_ke', $jadwal->jamPelajaran->urutan);
                    $set('durasi_jam', $jadwal->jamPelajaran->durasi_jp);
                    $set('jam_pelajaran_id', $jadwal->jam_pelajaran_id);
                    /*
                    |--------------------------------------------------------------------------
                    | GENERATE ABSENSI SISWA
                    |--------------------------------------------------------------------------
                    */
                    $siswa = \App\Models\Siswa::query()
                        ->where('kelas_id', $jadwal->kelas_id)
                        ->orderBy('nama_lengkap')
                        ->get()
                        ->map(fn ($item) => [
                            'siswa_id' => $item->id,
                            'nama' => $item->nama_lengkap,
                            'status' => 'Hadir',
                        ])
                        ->toArray();

                    $set('absensi_siswa', $siswa);
                })
                    ->placeholder('Pilih guru & tanggal dulu'),

                // 📌 AUTO FIELD (hidden logic)
                Hidden::make('kelas_id'),
                Hidden::make('mata_pelajaran_id'),
                Hidden::make('jam_pelajaran_id'),
                Hidden::make('jam_ke')
                    ->dehydrated(false),
                
                Hidden::make('durasi_jam')
                    ->dehydrated(false),

                // 📝 Materi
                Textarea::make('materi')
                    ->label('Materi Pembelajaran')
                    ->rows(2)
                    ->columnSpanFull()
                    ->required(),
            ])
            ->columns(2),

            Section::make('Absensi Siswa')
            ->description('Default semua siswa hadir')

            ->schema([
                Repeater::make('absensi_siswa')
                    ->label(false)
                    ->schema([
                        Hidden::make('siswa_id'),
                        Select::make('siswa_id')

    ->label('Nama Siswa')
    ->options(
        \App\Models\Siswa::query()
            ->orderBy('nama_lengkap')
            ->pluck('nama_lengkap', 'id')
    )
    ->disabled()
    ->dehydrated(),

                        ToggleButtons::make('status')
                        ->inline()
                        ->options([
                            'Hadir' => 'Hadir',
                            'Izin' => 'Izin',
                            'Sakit' => 'Sakit',
                            'Alpha' => 'Alpha',
                        ])
                        ->default('Hadir')
                        ->colors([
                            'Hadir' => 'success',
                            'Izin' => 'warning',
                            'Sakit' => 'info',
                            'Alpha' => 'danger',
                        ])
                        ->icons([
                            'Hadir' => 'heroicon-o-check-circle',
                            'Izin' => 'heroicon-o-clock',
                            'Sakit' => 'heroicon-o-heart',
                            'Alpha' => 'heroicon-o-x-circle',
                        ])
                        ->grouped()
                        ->required()
                    ])
                    ->columns(2)

                    ->default(function ($get) {

                        // ambil kelas
                        $kelasId = $get('kelas_id');

                        // jika kosong
                        if (!$kelasId) {
                            return [];
                        }

                        // generate siswa default hadir
                        return Siswa::query()
                            ->where('kelas_id', $kelasId)
                            ->orderBy('nama_lengkap')
                            ->get()
                            ->map(fn ($item) => [
                                'siswa_id' => $item->id,
                                'status' => 'Hadir',
                            ])
                            ->toArray();
                    })
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->collapsible(false)
                    ->columnSpanFull()
            ])
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pegawai.nama')->label('Guru')->searchable(),
                TextColumn::make('tanggal')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)
                    ->locale('id')
                    ->translatedFormat('d F Y')
                ),
                TextColumn::make('kelas.nama'),
                TextColumn::make('mataPelajaran.nama')->label('Mapel'),
                TextColumn::make('jadwal.jamPelajaran.jam_mulai')
                    ->label('Mulai')
                    ->time('H:i'),
                
                TextColumn::make('jadwal.jamPelajaran.jam_selesai')
                    ->label('Selesai')
                    ->time('H:i'),
                
                TextColumn::make('jadwal.jamPelajaran.durasi_jp')
                    ->label('JP'),
                TextColumn::make('materi')->label('Materi')->limit(50)->wrap(),
                TextColumn::make('rekap_absensi')
                    ->label('Absensi')
                    ->getStateUsing(function ($record) {
                        $hadir = $record->absensiMapels()
                            ->where('status', 'Hadir')
                            ->count();
                        $izin = $record->absensiMapels()
                            ->where('status', 'Izin')
                            ->count();
                        $sakit = $record->absensiMapels()
                            ->where('status', 'Sakit')
                            ->count();
                        $alpha = $record->absensiMapels()
                            ->where('status', 'Alpha')
                            ->count();
                        $total = $record->absensiMapels()->count();

                        return
                            "Hadir: $hadir | "
                            . "Izin: $izin | "
                            . "Sakit: $sakit | "
                            . "Alpha: $alpha | "
                            . "Total: $total";
                    })
                    ->badge()
                    ->color('gray'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'valid',
                    ]),
            ])

            ->filters([

    /*
    |--------------------------------------------------------------------------
    | FILTER BULAN
    |--------------------------------------------------------------------------
    */

    Filter::make('bulan')

        ->form([

            Select::make('bulan')

                ->label('Bulan')

                ->placeholder('Semua Bulan')

                ->options([

                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember',

                ])

        ])

        ->query(function ($query, array $data) {

            return $query

                ->when(

                    $data['bulan'] ?? null,

                    fn ($q, $bulan) =>

                    $q->whereMonth('tanggal', $bulan)

                );

        }),

    /*
    |--------------------------------------------------------------------------
    | FILTER GURU
    |--------------------------------------------------------------------------
    */

    Filter::make('guru')

        ->form([

            Select::make('pegawai_id')
                ->label('Guru')
                ->placeholder('Semua Guru')
                ->searchable()
                ->preload()
                ->options(
                    \App\Models\Pegawai::query()
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                )
        ])

        ->query(function ($query, array $data) {
            return $query
                ->when(
                    $data['pegawai_id'] ?? null,
                    fn ($q, $pegawai) =>
                    $q->where('pegawai_id', $pegawai)
                );
        }),

        /*
        |--------------------------------------------------------------------------
        | FILTER KELAS
        |--------------------------------------------------------------------------
        */

        Filter::make('kelas')
            ->form([
                Select::make('kelas_id')
                    ->label('Kelas')
                    ->placeholder('Semua Kelas')
                    ->searchable()
                    ->preload()
                    ->options(
                        \App\Models\Kelas::query()
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                    )

            ])

            ->query(function ($query, array $data) {
                return $query
                    ->when(
                        $data['kelas_id'] ?? null,
                        fn ($q, $kelas) =>
                        $q->where('kelas_id', $kelas)
                    );
            }),

        /*
        |--------------------------------------------------------------------------
        | FILTER MAPEL
        |--------------------------------------------------------------------------
        */

        Filter::make('mapel')
            ->form([
                Select::make('mata_pelajaran_id')
                    ->label('Mapel')
                    ->placeholder('Semua Mapel')
                    ->searchable()
                    ->preload()
                    ->options(
                        \App\Models\MataPelajaran::query()
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                    )
            ])

            ->query(function ($query, array $data) {
                return $query
                    ->when(
                        $data['mata_pelajaran_id'] ?? null,
                        fn ($q, $mapel) =>
                        $q->where(
                            'mata_pelajaran_id',
                            $mapel
                        )
                    );
            }),
    ])
            ->actions([
                Actions\EditAction::make(),

                Actions\Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'valid',
                            'validated_by' => auth()->id(),
                            'validated_at' => now(),
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalMengajars::route('/'),
            'create' => Pages\CreateJurnalMengajar::route('/create'),
            'edit' => Pages\EditJurnalMengajar::route('/{record}/edit'),
        ];
    }
}