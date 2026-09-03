<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringMengajarResource\Pages;

use App\Models\Pegawai;
use App\Models\JurnalMengajar;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringMengajarResource extends BaseResource
{
    /*
    |--------------------------------------------------------------------------
    | MODEL
    |--------------------------------------------------------------------------
    */
    protected static ?string $model = Pegawai::class;
    public static function canCreate(): bool
    {
        return false;
    }
    /*
    |--------------------------------------------------------------------------
    | NAVIGATION
    |--------------------------------------------------------------------------
    */
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Rekapitulasi Mengajar';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 7;
    protected static ?string $modelLabel = 'Rekapitulasi Mengajar';
    protected static ?string $pluralModelLabel = 'Rekapitulasi Mengajar';
    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    | Tidak dipakai karena monitoring hanya read-only
    |--------------------------------------------------------------------------
    */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([]);
    }

    /**
     * Tentukan rentang tanggal [mulai, selesai] dari state filter "periode"
     * (dipakai bersama oleh kolom tabel & tombol Export Excel supaya
     * hitungannya selalu konsisten dengan apa yang dipilih admin).
     *
     * @param  array  $state  isi filter: ['tipe' => 'minggu'|'bulan'|'custom', 'dari' => ..., 'sampai' => ...]
     */
    public static function resolvePeriode(?array $state): array
    {
        $tipe = $state['tipe'] ?? 'minggu';

        if ($tipe === 'bulan') {
            return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        }

        if ($tipe === 'custom' && ! empty($state['dari']) && ! empty($state['sampai'])) {
            return [$state['dari'], $state['sampai']];
        }

        // default: minggu ini
        return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()];
    }

    public static function realisasiJp(int $pegawaiId, array $periode): int
    {
        return (int) JurnalMengajar::query()
            ->where('jurnal_mengajars.pegawai_id', $pegawaiId)
            ->where('status', 'valid')
            ->whereBetween('jurnal_mengajars.tanggal', $periode)
            ->join('jadwal_pelajarans', 'jurnal_mengajars.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
            ->sum('jadwal_pelajarans.durasi_jam');
    }

    /**
     * Kewajiban JP diskalakan proporsional sesuai panjang periode yang
     * dipilih -- Kurikulum cuma nyimpen kewajiban PER MINGGU, jadi kalau
     * periode-nya "Bulan Ini" (~4-5 minggu) atau custom range, kewajiban
     * mingguan itu dikali jumlah minggu di rentang itu (hari / 7),
     * supaya perbandingan ke realisasi tetap adil -- bukan lagi
     * dibandingkan mentah-mentah ke angka mingguan tetap.
     */
    public static function kewajibanJp(int $pegawaiId, array $periode): float
    {
        $mingguanPerPegawai = \App\Models\Kurikulum::query()
            ->where('pegawai_id', $pegawaiId)
            ->sum('jumlah_jam_per_minggu');

        $jumlahHari = \Carbon\Carbon::parse($periode[0])->diffInDays(\Carbon\Carbon::parse($periode[1])) + 1;
        $jumlahMinggu = $jumlahHari / 7;

        return round($mingguanPerPegawai * $jumlahMinggu, 1);
    }

    public static function formatJp(float $jp): string
    {
        // Tampilkan tanpa desimal kalau bulat (mis. "8 JP"), tampilkan
        // 1 angka di belakang koma kalau pecahan (mis. "34.3 JP") --
        // wajar terjadi karena skala minggu bisa pecahan (custom range
        // yang tidak persis kelipatan 7 hari).
        return (floor($jp) == $jp ? (int) $jp : $jp) . ' JP';
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    public static function table(Table $table): Table
    {
        return $table
            /*
            |--------------------------------------------------------------------------
            | KOLOM
            |--------------------------------------------------------------------------
            */
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | GURU
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('nama')
                    ->label('Guru')
                    ->searchable(),

                /*
                |--------------------------------------------------------------------------
                | KEWAJIBAN JP
                |--------------------------------------------------------------------------
                */
                Tables\Columns\BadgeColumn::make('kewajiban_jp')
                ->label('Kewajiban JP')
                ->getStateUsing(function ($record, $livewire) {
                    $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);
                    return static::formatJp(static::kewajibanJp($record->id, $periode));
                })
                ->color('primary'),

                /*
                |--------------------------------------------------------------------------
                | REALISASI JP (SESUAI FILTER PERIODE)
                |--------------------------------------------------------------------------
                |
                | Dibatasi sesuai filter "Periode" (Minggu Ini / Bulan Ini /
                | Custom Range) -- sebelumnya HARDCODE minggu ini, sekarang
                | admin bisa pilih sendiri.
                */
                Tables\Columns\BadgeColumn::make('realisasi_jp')
                ->label('Mengajar')
                ->getStateUsing(function ($record, $livewire) {
                    $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);
                    return static::realisasiJp($record->id, $periode) . ' JP';
                })
                ->color('success'),
                /*
                |--------------------------------------------------------------------------
                | TIDAK MENGAJAR (SESUAI FILTER PERIODE)
                |--------------------------------------------------------------------------
                */

                Tables\Columns\BadgeColumn::make('kurang_jp')
                ->label('Tidak Mengajar')
                ->getStateUsing(function ($record, $livewire) {
                    $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);

                    $kewajiban = static::kewajibanJp($record->id, $periode);
                    $realisasi = static::realisasiJp($record->id, $periode);

                    return static::formatJp(max($kewajiban - $realisasi, 0));
                })
                ->color(fn ($state) =>
                    (float) str_replace(' JP', '', $state) > 0
                        ? 'danger'
                        : 'success'
                ),

                /*
                |--------------------------------------------------------------------------
                | PERSENTASE (SESUAI FILTER PERIODE)
                |--------------------------------------------------------------------------
                */
                Tables\Columns\BadgeColumn::make('persentase')
                    ->label('Persentase')
                    ->getStateUsing(function ($record, $livewire) {
                        $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);

                        $kewajiban = static::kewajibanJp($record->id, $periode);
                        $realisasi = static::realisasiJp($record->id, $periode);

                        if ($kewajiban <= 0) {
                            return '0%';
                        }
                        $persen = round(($realisasi / $kewajiban) * 100);
                        return $persen . '%';
                    })

                    ->colors([
                        'success' => fn ($state) => (int) str_replace('%', '', $state) >= 90,
                        'warning' => fn ($state) =>
                            (int) str_replace('%', '', $state) >= 70
                            &&
                            (int) str_replace('%', '', $state) < 90,
                        'danger' => fn ($state) =>
                            (int) str_replace('%', '', $state) < 70,
                    ]),
            ])
            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            */
            ->filters([

                /*
                |--------------------------------------------------------------------------
                | FILTER PERIODE
                |--------------------------------------------------------------------------
                */
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\Select::make('tipe')
                            ->label('Periode')
                            ->options([
                                'minggu' => 'Minggu Ini',
                                'bulan' => 'Bulan Ini',
                                'custom' => 'Custom Range',
                            ])
                            ->default('minggu')
                            ->native(false)
                            ->live(),

                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->visible(fn ($get) => $get('tipe') === 'custom'),

                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->visible(fn ($get) => $get('tipe') === 'custom'),
                    ])
                    // Query-nya sengaja no-op (tidak menyaring baris Pegawai
                    // yang mana) -- filter ini cuma dipakai buat NYIMPEN
                    // periode yang dipilih, lalu dibaca ulang di tiap kolom
                    // (getStateUsing) & tombol Export.
                    ->query(fn ($query) => $query)
                    ->indicateUsing(function (array $data): ?string {
                        $periode = static::resolvePeriode($data);
                        return 'Periode: ' . \Carbon\Carbon::parse($periode[0])->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($periode[1])->translatedFormat('d M Y');
                    }),

                /*
                |--------------------------------------------------------------------------
                | FILTER GURU
                |--------------------------------------------------------------------------
                */
                Tables\Filters\SelectFilter::make('id')
                ->label('Guru')
                ->options(
                    \App\Models\Pegawai::query()
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                )
                ->searchable()
                ->preload(),
            ])
            /*
            |--------------------------------------------------------------------------
            | SORT
            |--------------------------------------------------------------------------
            */
            ->defaultSort('nama')
            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */
            ->paginated([10, 25, 50, 100])
            /*
            |--------------------------------------------------------------------------
            | ACTIONS
            |--------------------------------------------------------------------------
            */
            ->actions([
            Tables\Actions\Action::make('detail')
                ->label('Detail')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) =>
                    route(
                        'filament.admin.resources.monitoring-mengajars.detail',
                        ['tenant' => \Filament\Facades\Filament::getTenant(), 'record' => $record]
                    )
                ),

        ])
            ->bulkActions([]);
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public static function getRelations(): array
    {
        return [];
    }
    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringMengajars::route('/'),
            'detail' => Pages\DetailMonitoringMengajar::route('/{record}/detail'),
        ];
    }
}
