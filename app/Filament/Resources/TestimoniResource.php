<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimoniResource\Pages;
use App\Models\Testimoni;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimoniResource extends Resource
{
    protected static ?string $model = Testimoni::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Testimoni';

    // Konten landing page bersifat platform-wide, bukan milik satu Yayasan tertentu.
    // Jika properti ini belum tersedia di versi Filament yang dipakai, lihat catatan
    // di INSTRUKSI-INSTALASI.md untuk alternatifnya.
    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_platform_admin ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('jabatan')->maxLength(255)
                ->placeholder('Contoh: Pimpinan Ponpes Al-Amanah'),
            Forms\Components\TextInput::make('asal_pesantren')->maxLength(255)
                ->helperText('Opsional, akan ditampilkan setelah jabatan jika diisi.'),
            Forms\Components\Textarea::make('isi')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('rating')
                ->options([1 => '1 Bintang', 2 => '2 Bintang', 3 => '3 Bintang', 4 => '4 Bintang', 5 => '5 Bintang'])
                ->default(5)
                ->required(),
            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('asal_pesantren')->label('Asal Pesantren'),
                Tables\Columns\TextColumn::make('rating')->badge(),
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
            'index' => Pages\ListTestimonis::route('/'),
            'create' => Pages\CreateTestimoni::route('/create'),
            'edit' => Pages\EditTestimoni::route('/{record}/edit'),
        ];
    }
}
