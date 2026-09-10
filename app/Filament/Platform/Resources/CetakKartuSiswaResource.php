<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\CetakKartuSiswaResource\Pages;
use App\Models\Siswa;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- pemindahan tombol "Cetak Kartu Massal" dari
 * SiswaResource (panel admin/tenant). Bulk action itu SEJAK AWAL
 * sudah dikunci is_platform_admin-only (lihat visible() di kode
 * lama) -- sekolah tidak pernah bisa memakainya, jadi ini murni
 * pindah lokasi, bukan perubahan akses.
 *
 * Tombol cetak SATU kartu (per-siswa, tanpa gate is_platform_admin)
 * TETAP di SiswaResource (panel admin) -- itu memang bisa dipakai
 * sekolah sendiri, tidak ikut dipindah.
 *
 * Read-only + aksi cetak saja, tidak ada create/edit/delete --
 * data Siswa tetap dikelola sepenuhnya dari panel admin.
 */
class CetakKartuSiswaResource extends BaseResource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationLabel = 'Cetak Kartu Siswa';
    protected static ?string $navigationGroup = 'Kartu ID';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $modelLabel = 'Cetak Kartu Siswa';
    protected static ?string $pluralModelLabel = 'Cetak Kartu Siswa';

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

                Tables\Columns\TextColumn::make('lembaga.yayasan.nama')
                    ->label('Yayasan')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kelas.nama')
                    ->label('Kelas'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('yayasan_id')
                    ->label('Yayasan')
                    ->options(fn () => \App\Models\Yayasan::pluck('nama', 'id'))
                    ->searchable()
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $value) => $q->whereHas('lembaga', fn ($lq) => $lq->where('yayasan_id', $value))
                    )),

                Tables\Filters\SelectFilter::make('lembaga_id')
                    ->label('Lembaga')
                    ->options(fn () => \App\Models\Lembaga::with('yayasan')
                        ->get()
                        ->mapWithKeys(fn ($l) => [$l->id => trim(($l->nama ?? '-').' — '.($l->yayasan?->nama ?? '-'))]))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(fn () => \App\Models\Kelas::with('lembaga')
                        ->get()
                        ->mapWithKeys(fn ($k) => [$k->id => trim(($k->nama ?? '-').' — '.($k->lembaga?->nama ?? '-'))]))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_kartu')
                    ->label('Cetak Kartu')
                    ->icon('heroicon-o-identification')
                    ->color('success')
                    ->url(fn ($record) => url('/kartu/siswa/'.$record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cetak_massal')
                    ->label('Cetak Kartu Massal')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->implode(',');

                        return redirect(url('/kartu/siswa-massal?ids='.$ids));
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('nama_lengkap');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCetakKartuSiswas::route('/'),
        ];
    }
}
