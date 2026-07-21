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
    protected static ?int $navigationSort = 5;
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
                ->getStateUsing(function ($record) {
                    return \App\Models\Kurikulum::query()
                        ->where('pegawai_id', $record->id)
                        ->sum('jumlah_jam_per_minggu') . ' JP';
                })
                ->color('primary'),

                /*
                |--------------------------------------------------------------------------
                | REALISASI JP
                |--------------------------------------------------------------------------
                */
                Tables\Columns\BadgeColumn::make('realisasi_jp')
                ->label('Mengajar')
                ->getStateUsing(function ($record) {
                    return \App\Models\JurnalMengajar::query()
                        ->where('jurnal_mengajars.pegawai_id', $record->id)
                        ->where('status', 'valid')
                        ->join('jadwal_pelajarans', 'jurnal_mengajars.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
                        ->sum('jadwal_pelajarans.durasi_jam') . ' JP';
                })
                ->color('success'),
                /*
                |--------------------------------------------------------------------------
                | TIDAK MENGAJAR
                |--------------------------------------------------------------------------
                */

                Tables\Columns\BadgeColumn::make('kurang_jp')
                ->label('Tidak Mengajar')
                ->getStateUsing(function ($record) {
                    $kewajiban = \App\Models\Kurikulum::query()
                        ->where('pegawai_id', $record->id)
                        ->sum('jumlah_jam_per_minggu');

                    $realisasi = \App\Models\JurnalMengajar::query()
                        ->where('jurnal_mengajars.pegawai_id', $record->id)
                        ->where('status', 'valid')
                        ->join('jadwal_pelajarans', 'jurnal_mengajars.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
                        ->sum('jadwal_pelajarans.durasi_jam');

                    return max($kewajiban - $realisasi, 0) . ' JP';
                })
                ->color(fn ($state) =>
                    (int) str_replace(' JP', '', $state) > 0
                        ? 'danger'
                        : 'success'
                ),

                /*
                |--------------------------------------------------------------------------
                | PERSENTASE
                |--------------------------------------------------------------------------
                */
                Tables\Columns\BadgeColumn::make('persentase')
                    ->label('Persentase')
                    ->getStateUsing(function ($record) {
                        $kewajiban = \App\Models\Kurikulum::query()
                            ->where('pegawai_id', $record->id)
                            ->sum('jumlah_jam_per_minggu');
                        $realisasi = \App\Models\JurnalMengajar::query()
                            ->where('jurnal_mengajars.pegawai_id', $record->id)
                            ->where('status', 'valid')
                            ->join('jadwal_pelajarans', 'jurnal_mengajars.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
                            ->sum('jadwal_pelajarans.durasi_jam');
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