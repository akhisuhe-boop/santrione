<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\KartuTemplateResource\Pages;
use App\Models\KartuTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;

/**
 * DIPINDAH dari app/Filament/Resources/KartuTemplateResource.php --
 * murni pindah lokasi/namespace ke panel Platform, TIDAK ada
 * perubahan fungsi/akses. Resource ini sejak awal SUDAH dikunci
 * is_platform_admin-only (lihat shouldRegisterNavigation/canViewAny
 * dkk di bawah) -- Lembaga/sekolah TIDAK PERNAH bisa akses ini,
 * jadi memindahkannya ke panel Platform tidak menghilangkan akses
 * siapa pun, cuma menaruh kode di panel yang sesuai.
 *
 * PERUBAHAN TEKNIS SATU-SATUNYA: field `lembaga_id` sebelumnya
 * difilter otomatis lewat Filament::getTenant() (context Yayasan
 * yang lagi aktif di panel admin/tenant). Panel Platform TIDAK
 * punya tenant aktif (root domain, semua Yayasan sekaligus), jadi
 * filter itu dilepas -- sekarang tampilkan SEMUA Lembaga lintas
 * Yayasan, dengan nama Yayasan ditambahkan di label supaya tetap
 * jelas milik siapa.
 */
class KartuTemplateResource extends BaseResource
{
    protected static ?string $model = KartuTemplate::class;
    protected static ?string $navigationLabel = 'Template Kartu';
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-identification';

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
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canEdit($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Template Kartu Siswa')
                ->icon('heroicon-o-credit-card')
                ->schema([
                Forms\Components\Select::make('lembaga_id')
                ->label('Lembaga')
                // DIUBAH -- sebelumnya difilter Filament::getTenant()
                // (cuma jalan di panel admin/tenant). Di panel Platform
                // tidak ada tenant aktif, jadi tampilkan semua Lembaga
                // lintas Yayasan, nama Yayasan disertakan di label
                // supaya tetap jelas.
                ->relationship(
                    'lembaga',
                    'nama',
                    modifyQueryUsing: fn ($query) => $query->with('yayasan'),
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => trim(
                    ($record->nama ?? '-').' — '.($record->yayasan?->nama ?? 'Yayasan tidak diketahui')
                ))
                ->required()
                ->preload()
                ->searchable(),

                Forms\Components\Select::make('jenis')
                ->label('Jenis Kartu')
                ->options([
                    'siswa' => 'Kartu Siswa',
                    'pegawai' => 'Kartu Guru / Pegawai',
                ])

                ->required(),
                FileUpload::make('background_depan')
                ->label('Background Kartu Depan')
                ->image()
                ->disk('r2-public')
                ->directory('kartu-template')
                ->required(),

                FileUpload::make('background_belakang')
                ->label('Background Kartu Belakang')
                ->image()
                ->disk('r2-public')
                ->directory('kartu-template')
                ->required(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // DITAMBAHKAN -- kolom Yayasan, supaya jelas siapa
                // pemilik tiap baris sekarang tabelnya lintas tenant.
                Tables\Columns\TextColumn::make('lembaga.yayasan.nama')
                    ->label('Yayasan')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->badge(),

                Tables\Columns\TextColumn::make('jenis')
                ->label('Jenis')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'siswa' => 'Kartu Siswa',
                    'pegawai' => 'Kartu Pegawai',
                    default => '-',
                })
                ->badge()
                ->colors([
                    'success' => 'pegawai',
                    'primary' => 'siswa',
                ]),

                ImageColumn::make('background_depan')
                ->label('Depan')
                ->disk('r2-public'),

                ImageColumn::make('background_belakang')
                ->label('Belakang')
                ->disk('r2-public'),
            ])
            ->filters([
                // DIUBAH -- filter per Lembaga (bukan lewat relationship()
                // nested yang belum ada presedennya di codebase ini),
                // berguna sekarang tabelnya menampilkan semua Yayasan
                // sekaligus.
                Tables\Filters\SelectFilter::make('lembaga_id')
                    ->label('Lembaga')
                    ->options(fn () => \App\Models\Lembaga::with('yayasan')
                        ->get()
                        ->mapWithKeys(fn ($l) => [$l->id => trim(($l->nama ?? '-').' — '.($l->yayasan?->nama ?? '-'))]))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKartuTemplates::route('/'),
            'create' => Pages\CreateKartuTemplate::route('/create'),
            'edit' => Pages\EditKartuTemplate::route('/{record}/edit'),
        ];
    }
}
