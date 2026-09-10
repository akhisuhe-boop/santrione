<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\CetakKartuPegawaiResource\Pages;
use App\Models\Pegawai;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- pasangan CetakKartuSiswaResource, untuk Pegawai.
 * Pemindahan bulk action "Cetak Kartu ID" dari PegawaiResource (panel
 * admin/tenant) -- sama seperti siswa, bulk action ini SEJAK AWAL
 * sudah is_platform_admin-only, jadi murni pindah lokasi.
 *
 * Tombol cetak SATU kartu (per-pegawai, "Cetak ID") TETAP di
 * PegawaiResource (panel admin) -- tidak ikut dipindah.
 *
 * Catatan: Pegawai belongsToMany Lembaga (bisa mengajar/kerja di
 * lebih dari 1 Lembaga), jadi kolom & filter Lembaga di sini pakai
 * lembagaUtama() (Lembaga pertama sesuai urutan assignment) untuk
 * kesederhanaan tampilan -- lihat catatan di App\Models\Pegawai.
 */
class CetakKartuPegawaiResource extends BaseResource
{
    protected static ?string $model = Pegawai::class;
    protected static ?string $navigationLabel = 'Cetak Kartu Pegawai';
    protected static ?string $navigationGroup = 'Kartu ID';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $modelLabel = 'Cetak Kartu Pegawai';
    protected static ?string $pluralModelLabel = 'Cetak Kartu Pegawai';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('r2-public')
                    ->circular(),

                Tables\Columns\TextColumn::make('yayasan.nama')
                    ->label('Yayasan')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('lembaga_utama')
                    ->label('Lembaga Utama')
                    ->getStateUsing(fn ($record) => $record->lembagaUtama()?->nama ?? '-')
                    ->badge(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('niy')
                    ->label('NIY')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lembagas')
                    ->label('Lembaga')
                    ->relationship('lembagas', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak ID')
                    ->icon('heroicon-o-identification')
                    ->color('success')
                    ->url(fn ($record) => route('kartu.pegawai', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cetak_kartu')
                    ->label('Cetak Kartu ID Massal')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->join(',');

                        return redirect()->route('kartu.pegawai', ['ids' => $ids]);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCetakKartuPegawais::route('/'),
        ];
    }
}
