<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\CetakKartuSiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
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

    /**
     * DITAMBAHKAN -- hanya tampilkan siswa berstatus Aktif (siswa
     * yang sudah Lulus/Pindah tidak relevan dicetakkan kartu lagi).
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('status_siswa', 'Aktif');
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
                // DITAMBAHKAN -- digabung jadi SATU filter (bukan 3
                // SelectFilter terpisah lagi) supaya Lembaga & Kelas
                // bisa saling "dengar" pilihan Yayasan/Lembaga lewat
                // $get() -- reactive select berantai butuh berbagi
                // form state, yang tidak bisa dilakukan across
                // SelectFilter yang independen.
                Tables\Filters\Filter::make('yayasan_lembaga_kelas')
                    ->label('Yayasan / Lembaga / Kelas')
                    ->form([
                        Forms\Components\Select::make('yayasan_id')
                            ->label('Yayasan')
                            ->options(fn () => \App\Models\Yayasan::pluck('nama', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('lembaga_id', null);
                                $set('kelas_id', null);
                            }),

                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->options(fn (Forms\Get $get) => \App\Models\Lembaga::query()
                                ->when($get('yayasan_id'), fn ($q, $v) => $q->where('yayasan_id', $v))
                                ->pluck('nama', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('kelas_id', null)),

                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(fn (Forms\Get $get) => \App\Models\Kelas::query()
                                ->when($get('lembaga_id'), fn ($q, $v) => $q->where('lembaga_id', $v))
                                ->pluck('nama', 'id'))
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['yayasan_id'] ?? null,
                                fn ($q, $v) => $q->whereHas('lembaga', fn ($lq) => $lq->where('yayasan_id', $v))
                            )
                            ->when($data['lembaga_id'] ?? null, fn ($q, $v) => $q->where('lembaga_id', $v))
                            ->when($data['kelas_id'] ?? null, fn ($q, $v) => $q->where('kelas_id', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['yayasan_id'] ?? null) {
                            $indicators[] = 'Yayasan: '.(\App\Models\Yayasan::find($data['yayasan_id'])?->nama ?? '-');
                        }
                        if ($data['lembaga_id'] ?? null) {
                            $indicators[] = 'Lembaga: '.(\App\Models\Lembaga::find($data['lembaga_id'])?->nama ?? '-');
                        }
                        if ($data['kelas_id'] ?? null) {
                            $indicators[] = 'Kelas: '.(\App\Models\Kelas::find($data['kelas_id'])?->nama ?? '-');
                        }

                        return $indicators;
                    }),
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
