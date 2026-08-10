<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\ModulePriceResource\Pages;
use App\Models\ModulePrice;
use App\Support\FeatureGate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Master harga modul add-on (skema à la carte). Terpisah dari
 * SubscriptionPlanResource — plan mengatur "Akses Platform" (biaya
 * dasar wajib), resource ini mengatur harga TIAP MODUL yang bisa
 * diaktifkan sekolah satu-satu per Lembaga (lihat LembagaModule di
 * LembagaResource untuk mengaktifkan/menonaktifkannya per Lembaga).
 */
class ModulePriceResource extends BaseResource
{
    protected static ?string $model = ModulePrice::class;
    protected static ?string $navigationGroup = 'Platform (SaaS)';
    protected static ?string $navigationLabel = 'Harga Modul';
    protected static ?string $modelLabel = 'Harga Modul';
    protected static ?string $pluralModelLabel = 'Harga Modul';
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    // Sama seperti Paket Langganan — harga adalah keputusan platform,
    // cuma Platform Admin yang boleh kelola.
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

                Forms\Components\Section::make('Modul')
                    ->schema([

                        Forms\Components\Select::make('key')
                            ->label('Modul')
                            ->options(FeatureGate::all())
                            ->required()
                            ->disabledOn('edit')
                            ->helperText('Menu sidebar yang dibuka modul ini. Tidak bisa diubah setelah dibuat — hapus & buat baru kalau salah pilih.'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Tampilan')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama yang muncul di invoice/rincian tagihan sekolah.'),

                        Forms\Components\TextInput::make('harga_bulanan')
                            ->label('Harga per Bulan')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->required()
                            ->default(0)
                            ->helperText('Untuk modul gratis (mis. Keuangan, e-Kantin), isi tetap dengan harga referensi "kalau bayar satu-satu" — nilai ini TIDAK ditagih ke sekolah kalau "Gratis untuk Sekolah" di bawah diaktifkan.'),

                        Forms\Components\Toggle::make('is_gratis')
                            ->label('Gratis untuk Sekolah')
                            ->helperText('Aktifkan untuk modul yang dimonetisasi lewat fee transaksi wali murid (Keuangan, e-Kantin) — sekolah tidak ditagih apapun untuk modul ini.')
                            ->live(),

                        Forms\Components\Select::make('dibebankan_ke')
                            ->label('Dibebankan ke')
                            ->options([
                                'sekolah' => 'Sekolah (ditagih flat bulanan)',
                                'wali_murid' => 'Wali Murid (fee per transaksi pembayaran)',
                            ])
                            ->required()
                            ->default('sekolah'),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif (bisa dipilih sekolah)')
                            ->default(true),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Modul')
                    ->searchable(),

                Tables\Columns\TextColumn::make('harga_bulanan')
                    ->label('Harga / Bulan')
                    ->formatStateUsing(fn ($state, $record) => $record->is_gratis
                        ? 'GRATIS'
                        : 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('dibebankan_ke')
                    ->label('Dibebankan ke')
                    ->formatStateUsing(fn ($state) => $state === 'wali_murid' ? 'Wali Murid' : 'Sekolah')
                    ->badge()
                    ->color(fn ($state) => $state === 'wali_murid' ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('lembaga_modules_count')
                    ->label('Lembaga Pakai')
                    ->counts('lembagaModules'),

            ])
            ->defaultSort('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModulePrices::route('/'),
            'create' => Pages\CreateModulePrice::route('/create'),
            'edit' => Pages\EditModulePrice::route('/{record}/edit'),
        ];
    }
}
