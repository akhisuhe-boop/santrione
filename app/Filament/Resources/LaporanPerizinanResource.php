<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPerizinanResource\Pages;
use App\Models\Siswa;
use App\Models\Perizinan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class LaporanPerizinanResource extends BaseResource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Perizinan';
    protected static ?string $navigationLabel = 'Laporan Perizinan';
    protected static ?int $navigationSort = 13;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()->with('perizinans')
            )

            ->columns([
                TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->placeholder('-'),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->placeholder('-'),

                TextColumn::make('total_hari')
                ->label('Total Hari')
                ->getStateUsing(function ($record) {

                    return $record->perizinans->sum(function ($izin) {

                        if (!$izin->tanggal_mulai || !$izin->tanggal_selesai) return 0;

                        $mulai = \Carbon\Carbon::parse($izin->tanggal_mulai)->startOfDay();
                        $selesai = \Carbon\Carbon::parse($izin->tanggal_selesai)->startOfDay();

                        return $mulai->diffInDays($selesai) + 1;
                    });
                })
                ->formatStateUsing(fn ($state) => $state . ' hari')
                ->badge()
                ->color('success'),
                        ])

            ->filters([
                SelectFilter::make('lembaga')
                    ->relationship('lembaga', 'nama')
                    ->label('Lembaga'),

                SelectFilter::make('kelas')
                    ->relationship('kelas', 'nama')
                    ->label('Kelas'),

                SelectFilter::make('id')
                    ->label('Siswa')
                    ->relationship('perizinans.siswa', 'nama_lengkap')
                    ->searchable(),
            ])

            ->actions([
                Tables\Actions\Action::make('detail')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->label('Riwayat')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'Riwayat Izin - ' . $record->nama_lengkap)
                    ->modalContent(function ($record) {

                        $data = Perizinan::where('siswa_id', $record->id)
                            ->latest()
                            ->get();

                        return view('filament.pages.detail-perizinan', [
                            'data' => $data
                        ]);
                    })
                    ->modalWidth('4xl'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPerizinans::route('/'),
        ];
    }
}