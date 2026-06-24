<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPrestasiResource\Pages;
use App\Models\PrestasiSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// 🔥 TAMBAHAN
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;

class LaporanPrestasiResource extends Resource
{
    protected static ?string $model = PrestasiSiswa::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?string $navigationLabel = 'Laporan Prestasi';
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?int $navigationSort = 11;

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

                Tables\Columns\TextColumn::make('prestasi.nama')
                    ->label('Prestasi'),

                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat'),

                Tables\Columns\TextColumn::make('peringkat')
                    ->label('Peringkat'),

                // 🔥 POINT + TOTAL
                Tables\Columns\TextColumn::make('point')
                    ->label('Point')
                    ->summarize(
                        Sum::make()->label('Total')
                    ),

                // 🔥 KESIMPULAN
                Tables\Columns\TextColumn::make('kesimpulan')
                    ->label('Kesimpulan')
                    ->getStateUsing(fn () => '-')
                    ->summarize(
                        Summarizer::make()
                            ->label('Kesimpulan')
                            ->using(function ($query) {

                                $result = \DB::table('prestasi_siswas')
                                    ->whereIn('prestasi_siswas.id', function ($sub) use ($query) {
                                        $sub->fromSub($query, 'filtered')
                                            ->select('filtered.id');
                                    })
                                    ->selectRaw("SUM(point) as total")
                                    ->first();

                                $total = $result->total ?? 0;

                                if ($total >= 100) return 'Sangat Berprestasi';
                                if ($total >= 50) return 'Berprestasi';

                                return 'Cukup';
                            })
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
            'index' => Pages\ListLaporanPrestasis::route('/'),
        ];
    }
}