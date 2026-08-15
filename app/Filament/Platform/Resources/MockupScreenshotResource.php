<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\MockupScreenshotResource\Pages;
use App\Models\MockupScreenshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class MockupScreenshotResource extends BaseResource
{
    protected static ?string $model = MockupScreenshot::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Screenshot Aplikasi';
    protected static ?string $modelLabel = 'Screenshot Aplikasi';
    protected static ?string $pluralModelLabel = 'Screenshot Aplikasi';
    protected static ?string $navigationIcon = 'heroicon-o-photo';

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
        return $form->schema([
            Forms\Components\Section::make('Screenshot')
                ->schema([
                    Forms\Components\TextInput::make('judul')->required()->maxLength(255)
                        ->placeholder('Contoh: Dashboard Keuangan SPP'),
                    Forms\Components\TextInput::make('deskripsi')->maxLength(255),
                    Forms\Components\FileUpload::make('gambar')
                        ->label('Gambar Screenshot')
                        ->image()
                        ->disk('r2-public')
                        ->directory('landing/mockup')
                        ->imageEditor()
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Upload screenshot asli dari aplikasi (disarankan rasio 16:9).'),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\ImageColumn::make('gambar')->disk('r2-public'),
                Tables\Columns\TextColumn::make('judul')->searchable(),
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
