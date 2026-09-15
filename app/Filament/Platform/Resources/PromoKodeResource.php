<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\PromoKodeResource\Pages;
use App\Models\PromoKode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class PromoKodeResource extends BaseResource
{
    protected static ?string $model = PromoKode::class;
    protected static ?string $navigationLabel = 'Kode Promo';
    protected static ?string $navigationGroup = 'Billing & Harga';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $modelLabel = 'Kode Promo';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kode Promo')
                ->schema([
                    Forms\Components\TextInput::make('kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state)))
                        ->helperText('Otomatis disimpan huruf besar semua, mis. "HEMAT20".'),

                    Forms\Components\TextInput::make('diskon_persen')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(100)
                        ->suffix('%'),

                    Forms\Components\TextInput::make('maks_pemakaian')
                        ->label('Maks. Pemakaian per Yayasan')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Kosongkan = tidak terbatas (boleh dipakai berkali-kali oleh Yayasan yang sama).'),

                    Forms\Components\DatePicker::make('berlaku_sampai')
                        ->label('Berlaku Sampai')
                        ->native(false)
                        ->helperText('Kosongkan = tidak ada tanggal kedaluwarsa.'),

                    Forms\Components\Toggle::make('aktif')
                        ->default(true),

                    Forms\Components\TextInput::make('deskripsi')
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->helperText('Catatan internal, tidak tampil ke tenant.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('diskon_persen')
                    ->label('Diskon')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('maks_pemakaian')
                    ->label('Maks. Pemakaian')
                    ->formatStateUsing(fn ($state) => $state ?? 'Tidak terbatas'),

                Tables\Columns\TextColumn::make('pemakaians_count')
                    ->label('Sudah Dipakai')
                    ->counts('pemakaians'),

                Tables\Columns\TextColumn::make('berlaku_sampai')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->formatStateUsing(fn ($state) => $state?->translatedFormat('d M Y') ?? 'Tidak terbatas'),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromoKodes::route('/'),
            'create' => Pages\CreatePromoKode::route('/create'),
            'edit' => Pages\EditPromoKode::route('/{record}/edit'),
        ];
    }
}
