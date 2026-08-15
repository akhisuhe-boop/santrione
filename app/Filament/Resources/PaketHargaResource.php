<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaketHargaResource\Pages;
use App\Models\PaketHarga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaketHargaResource extends Resource
{
    protected static ?string $model = PaketHarga::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Paket Harga';

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_platform_admin ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('tagline')->maxLength(255),
            Forms\Components\TextInput::make('target_pasar')
                ->maxLength(255)
                ->placeholder('Contoh: Pesantren Kecil')
                ->helperText('Label kecil di atas nama paket.'),
            Forms\Components\TextInput::make('harga_bulanan')
                ->label('Harga per Bulan (Rp)')
                ->numeric()
                ->required()
                ->prefix('Rp'),
            Forms\Components\TextInput::make('diskon_tahunan_persen')
                ->label('Diskon Tahunan (%)')
                ->numeric()
                ->default(15)
                ->suffix('%'),
            Forms\Components\Toggle::make('is_recommended')
                ->label('Tandai sebagai "Paling Populer"')
                ->helperText('Hanya 1 paket sebaiknya ditandai supaya tampilan tetap fokus.'),
            Forms\Components\Repeater::make('fitur')
                ->label('Daftar Fitur')
                ->schema([
                    Forms\Components\TextInput::make('label')->required()->columnSpan(2),
                    Forms\Components\Toggle::make('included')->label('Termasuk?')->default(true),
                ])
                ->columns(3)
                ->defaultItems(1)
                ->reorderable()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('cta_text')
                ->label('Teks Tombol')
                ->default('Hubungi via WhatsApp'),
            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('harga_bulanan')->money('IDR', locale: 'id'),
                Tables\Columns\IconColumn::make('is_recommended')->boolean()->label('Populer'),
                Tables\Columns\TextColumn::make('urutan')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaketHargas::route('/'),
            'create' => Pages\CreatePaketHarga::route('/create'),
            'edit' => Pages\EditPaketHarga::route('/{record}/edit'),
        ];
    }
}
