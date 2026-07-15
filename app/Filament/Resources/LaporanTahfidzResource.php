<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanTahfidzResource\Pages;
use App\Models\Siswa;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LaporanTahfidzResource extends BaseResource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationGroup = 'Tahfidz';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationLabel = 'Laporan Tahfidz';
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form;
    }

    // 🔥 QUERY CEPAT (ANTI LAMBAT)
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'lembaga',
                'kelas',
                'tahfidzSetoran.juz',
                'targetTahfidz',
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga'),

                Tables\Columns\TextColumn::make('kelas.nama')
                    ->label('Kelas'),

                // 🔥 ZIYADAH (NO QUERY ULANG)
                Tables\Columns\TextColumn::make('ziyadah')
                    ->label('Ziyadah')
                    ->getStateUsing(fn ($record) =>
                        $record->tahfidzSetoran
                            ->where('jenis', 'ziyadah')
                            ->count()
                    ),

                // 🔥 AYAT
                Tables\Columns\TextColumn::make('ayat')
                    ->label('Ayat Hafal')
                    ->getStateUsing(fn ($record) =>
                        $record->tahfidzSetoran
                            ->where('jenis', 'ziyadah')
                            ->sum('jumlah_ayat')
                    ),

                // 🔥 MURAJAAH
                Tables\Columns\TextColumn::make('murajaah')
                    ->label('Murajaah')
                    ->getStateUsing(fn ($record) =>
                        $record->tahfidzSetoran
                            ->where('jenis', 'murajaah')
                            ->count()
                    ),

                // 🔥 NILAI
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Rata Nilai')
                    ->getStateUsing(fn ($record) =>
                        round(
                            $record->tahfidzSetoran->avg('nilai') ?? 0,
                            1
                        )
                    ),

                // 🔥 SURAH TERAKHIR
                Tables\Columns\TextColumn::make('surah')
                    ->label('Terakhir')
                    ->getStateUsing(function ($record) {

                        $last = $record->tahfidzSetoran
                            ->where('jenis', 'ziyadah')
                            ->sortByDesc('created_at')
                            ->first();

                        return $last
                            ? $last->surah?->nama . ' (' . $last->ayat_sampai . ')'
                            : '-';
                    }),

                //JUZ TERAKHIR
                Tables\Columns\TextColumn::make('juz_terakhir')
                ->label('Juz Terakhir')
                ->getStateUsing(function ($record) {

                    $last = $record->tahfidzSetoran()
                    ->where('jenis', 'ziyadah')
                    ->with('juz')
                    ->latest()
                    ->first();

                    return $last?->juz?->nama ?? '-';
                }),


                // 🔥 JUZ (CORE)
                Tables\Columns\TextColumn::make('juz')
                    ->label('Juz')
                    ->getStateUsing(fn ($record) =>
                        $record->progress_tahfidz['juz'] ?? '-'
                    ),

                // 🔥 PROGRESS
                Tables\Columns\TextColumn::make('progress')
                ->label('Progress Juz')
                ->getStateUsing(function ($record) {

                    $last = $record->tahfidzSetoran()
                        ->where('jenis', 'ziyadah')
                        ->with(['surah', 'juz'])
                        ->latest()
                        ->first();

                    if (!$last || !$last->juz) return '0%';

                    // 🔥 total ayat per juz (rata-rata pendekatan cepat)
                    $totalAyatPerJuz = [
                        1=>148,2=>111,3=>126,4=>131,5=>124,6=>110,7=>149,8=>142,9=>159,10=>127,
                        11=>151,12=>170,13=>154,14=>227,15=>185,16=>269,17=>190,18=>202,19=>339,
                        20=>171,21=>178,22=>169,23=>357,24=>175,25=>246,26=>195,27=>399,28=>137,
                        29=>431,30=>564
                    ];

                    // ambil nomor juz dari nama: "Juz 30"
                    $juzNumber = (int) str_replace('Juz ', '', $last->juz->nama);

                    $totalAyatJuz = $totalAyatPerJuz[$juzNumber] ?? 0;

                    if ($totalAyatJuz === 0) return '0%';

                    $progress = ($last->ayat_sampai / $totalAyatJuz) * 100;

                    return round($progress, 1) . '%';
                }),

                // 🔥 STATUS
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) =>
                        $record->progress_tahfidz['status'] ?? '-'
                    ),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('lembaga_id')
                    ->label('Lembaga')
                    ->options(\App\Models\Lembaga::pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(\App\Models\Kelas::pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('id')
                    ->label('Siswa')
                    ->options(\App\Models\Siswa::pluck('nama_lengkap', 'id')),

            ])

            ->actions([

                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'Riwayat Setoran - ' . $record->nama_lengkap)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')

                    ->modalContent(function ($record) {

                    $data = $record->tahfidzSetoran()
                        ->with(['juz', 'surah'])
                        ->latest()
                        ->get();

                    return view('filament.tahfidz.detail-setoran', [
                        'data' => $data,
                    ]);
                })

            ])

            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLaporanTahfidz::route('/'),
        ];
    }
}