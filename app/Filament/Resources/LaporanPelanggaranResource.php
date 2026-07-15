<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPelanggaranResource\Pages;
use App\Models\PelanggaranSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

// 🔥 TAMBAHAN
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;

class LaporanPelanggaranResource extends BaseResource
{
    protected static ?string $model = PelanggaranSiswa::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Laporan Pelanggaran';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('siswa.lembaga.nama')
                    ->label('Lembaga')
                    ->sortable(),

                Tables\Columns\TextColumn::make('siswa.kelas.nama')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelanggaran.nama')
                    ->label('Pelanggaran'),

                // 🔥 FIX: pakai getStateUsing (bukan langsung relasi)
                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->getStateUsing(fn ($record) => $record->pelanggaran->kategori)
                    ->colors([
                        'success' => 'Ringan',
                        'warning' => 'Sedang',
                        'danger' => 'Berat',
                    ])
                    ->summarize(
                        Summarizer::make()
                            ->label('Kesimpulan')
                            ->using(function ($query) {

                            $result = \DB::table('pelanggaran_siswas')
                                ->join('pelanggarans', 'pelanggaran_siswas.pelanggaran_id', '=', 'pelanggarans.id')
                                ->whereIn('pelanggaran_siswas.id', function ($sub) use ($query) {
                                    $sub->fromSub($query, 'filtered')
                                        ->select('filtered.id');
                                })
                                ->selectRaw("
                                    SUM(CASE WHEN pelanggarans.kategori = 'Ringan' THEN 1 ELSE 0 END) as ringan,
                                    SUM(CASE WHEN pelanggarans.kategori = 'Sedang' THEN 1 ELSE 0 END) as sedang,
                                    SUM(CASE WHEN pelanggarans.kategori = 'Berat' THEN 1 ELSE 0 END) as berat
                                ")
                                ->first();

                            if (!$result) return 'Baik';

                            if (($result->berat ?? 0) > 0) return 'Perlu Tindakan';
                            if (($result->sedang ?? 0) > 0) return 'Perlu Pembinaan';

                            return 'Baik';
                        })
                    ),

                // 🔥 POINT + TOTAL
                Tables\Columns\TextColumn::make('point')
                    ->label('Point')
                    ->summarize(
                        Sum::make()->label('Total')
                    ),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y'),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('lembaga')
                    ->relationship('siswa.lembaga', 'nama'),

                Tables\Filters\SelectFilter::make('kelas')
                    ->relationship('siswa.kelas', 'nama'),

                Tables\Filters\SelectFilter::make('siswa')
                    ->relationship('siswa', 'nama_lengkap'),

            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLaporanPelanggarans::route('/'),
        ];
    }
}