<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MockupScreenshotResource\Pages;
use App\Models\MockupScreenshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MockupScreenshotResource extends Resource
{
    protected static ?string $model = MockupScreenshot::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Galeri Screenshot Aplikasi';

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_platform_admin ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')->required()->maxLength(255)
                ->placeholder('Contoh: Dashboard Keuangan Syahriah'),
            Forms\Components\TextInput::make('deskripsi')->maxLength(255),
            Forms\Components\FileUpload::make('gambar')
                ->label('Gambar Screenshot')
                ->image()
                ->disk('r2-public')
                ->directory('landing/mockup')
                ->imageEditor()
                ->required()
                ->helperText('Upload screenshot asli dari aplikasi (disarankan rasio 16:9).'),
            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')->disk('r2-public'),
                Tables\Columns\TextColumn::make('judul')->searchable(),
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
            'index' => Pages\ListMockupScreenshots::route('/'),
            'create' => Pages\CreateMockupScreenshot::route('/create'),
            'edit' => Pages\EditMockupScreenshot::route('/{record}/edit'),
        ];
    }
}
