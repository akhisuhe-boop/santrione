<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LembagaResource\Pages;
use App\Filament\Resources\LembagaResource\RelationManagers;
use App\Models\Lembaga;
use App\Models\Yayasan;
use Filament\Forms\Get;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LembagaResource extends BaseResource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Lembaga';
    protected static ?string $pluralModelLabel = 'Lembaga';
    protected static ?string $navigationLabel = 'Lembaga';

    /**
     * WAJIB override eksplisit -- resource ini pakai model Lembaga::class
     * yang SAMA dengan PengaturanHonorPenggantiResource, dan Shield
     * ternyata memenangkan permission PengaturanHonorPengganti waktu
     * generate LembagaPolicy (viewAny() di sana cuma cek
     * "view_any_pengaturan::honor::pengganti", BUKAN "view_any_lembaga").
     * Ini resource yang PALING SERING dipakai, jadi wajib dipastikan
     * benar -- ditemukan 6 Sep 2026.
     */
    /**
     * WAJIB override eksplisit untuk SEMUA aksi (bukan cuma viewAny)
     * -- resource ini pakai model Lembaga::class yang SAMA dengan
     * PengaturanHonorPenggantiResource, dan Shield ternyata
     * memenangkan permission PengaturanHonorPengganti waktu generate
     * LembagaPolicy -- SEMUA method di sana (viewAny, view, create,
     * update, delete, dst) cuma cek permission
     * "..._pengaturan::honor::pengganti", BUKAN "..._lembaga" milik
     * resource ini sendiri. Karena Lembaga PUNYA create/edit/delete
     * sungguhan (beda dari resource "Laporan" lain yang cuma
     * read-only, di situ cukup canViewAny() saja yang perlu
     * diperbaiki), di sini SEMUA method canX() perlu diperbaiki --
     * ditemukan & diaudit tuntas 6 Sep 2026.
     */
    public static function canViewAny(): bool
    {
        return static::cekAksesLembaga('view_any_lembaga');
    }

    public static function canView($record): bool
    {
        return static::cekAksesLembaga('view_lembaga');
    }

    public static function canCreate(): bool
    {
        return static::cekAksesLembaga('create_lembaga');
    }

    public static function canEdit($record): bool
    {
        return static::cekAksesLembaga('update_lembaga');
    }

    public static function canDelete($record): bool
    {
        return static::cekAksesLembaga('delete_lembaga');
    }

    public static function canDeleteAny(): bool
    {
        return static::cekAksesLembaga('delete_any_lembaga');
    }

    public static function canForceDelete($record): bool
    {
        return static::cekAksesLembaga('force_delete_lembaga');
    }

    public static function canForceDeleteAny(): bool
    {
        return static::cekAksesLembaga('force_delete_any_lembaga');
    }

    public static function canRestore($record): bool
    {
        return static::cekAksesLembaga('restore_lembaga');
    }

    public static function canRestoreAny(): bool
    {
        return static::cekAksesLembaga('restore_any_lembaga');
    }

    public static function canReplicate($record): bool
    {
        return static::cekAksesLembaga('replicate_lembaga');
    }

    public static function canReorder(): bool
    {
        return static::cekAksesLembaga('reorder_lembaga');
    }

    /**
     * Helper bersama untuk SEMUA method canX() di atas -- ditulis
     * SEKALI di sini alih-alih diulang 11x, supaya kalau logikanya
     * (mis. cara cek FeatureGate) berubah nanti, cukup diubah di 1
     * tempat.
     */
    protected static function cekAksesLembaga(string $permission): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        $key = \App\Support\FeatureGate::keyForNavigationGroup(static::$navigationGroup);

        if ($key !== null) {
            $tenant = \Filament\Facades\Filament::getTenant();

            if (! $tenant?->hasFeature($key)) {
                return false;
            }
        }

        return (bool) auth()->user()?->can($permission);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
    
                Section::make('Informasi Lembaga')
                    ->description('Informasi identitas lembaga')
                    ->icon('heroicon-o-building-office')
                    ->schema([
    
                        TextInput::make('nama')
                            ->label('Nama Lembaga')
                            ->required()
                            ->maxLength(255),
    
                        Select::make('jenis')
                            ->label('Jenis Lembaga')
                            ->options([
                                'tk'  => 'TK',
                                'sd'  => 'SD',
                                'smp' => 'SMP',
                                'sma' => 'SMA',
                            ])
                            ->required(),
    
                        FileUpload::make('logo')
                            ->label('Logo Lembaga')
                            ->image()
                            ->disk('r2-public')
                            ->directory('lembaga')
                            ->imageEditor(),
    
                        TextInput::make('npsn')
                            ->label('NPSN')
                            ->maxLength(30),
    
                        TextInput::make('nss')
                            ->label('NSS')
                            ->maxLength(30),
    
                    ])
                    ->columns(3),
    
                Section::make('Manajemen Lembaga')
                    ->description('Informasi kepala sekolah dan bendahara')
                    ->icon('heroicon-o-user-group')
                    ->schema([
    
                        TextInput::make('kepala_sekolah')
                            ->label('Kepala Sekolah'),
    
                        Select::make('bendahara_id')
                            ->label('Bendahara')
                            ->relationship('bendahara', 'nama')
                            ->searchable()
                            ->preload(),
                        
                        Select::make('printer_kwitansi')
                            ->label('Printer Kwitansi')
                            ->options([
                                'thermal58' => 'Thermal 58 mm',
                                'thermal80' => 'Thermal 80 mm',
                                'dotmatrix' => 'Dot Matrix 3 Ply',
                            ])
                            ->default('thermal80')
                            ->native(false)
                            ->helperText('Digunakan saat mencetak kwitansi pembayaran.'),
    
                        Forms\Components\Toggle::make('is_tes')
                            ->label('Menggunakan Tes Masuk?')
                            ->helperText('Jika aktif, calon siswa wajib mengikuti tes sebelum dinyatakan lulus.')
                            ->default(true),

                    ])
                    ->columns(3),
    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('r2-public')
                    ->circular()
                    ->size(40),
            
                Tables\Columns\TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),
            
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('success'),
            
                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->toggleable(),
            
                Tables\Columns\TextColumn::make('kepala_sekolah')
                    ->label('Kepala Sekolah'),
            
                Tables\Columns\TextColumn::make('bendahara.nama')
                    ->label('Bendahara'),
                
                Tables\Columns\BadgeColumn::make('printer_kwitansi')
                    ->label('Printer')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'thermal58' => 'Thermal 58 mm',
                        'thermal80' => 'Thermal 80 mm',
                        'dotmatrix' => 'Dot Matrix',
                        default => '-',
                    })
                    ->colors([
                        'success' => 'thermal80',
                        'warning' => 'thermal58',
                        'primary' => 'dotmatrix',
                    ]),
            
                Tables\Columns\IconColumn::make('is_tes')
                    ->label('Tes')
                    ->boolean(),
            
            ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\LembagaRekeningRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLembagas::route('/'),
            'create' => Pages\CreateLembaga::route('/create'),
            'edit' => Pages\EditLembaga::route('/{record}/edit'),
        ];
    }
}
