<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\TestimoniResource\Pages;
use App\Models\Testimoni;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class TestimoniResource extends BaseResource
{
    protected static ?string $model = Testimoni::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Testimoni';
    protected static ?string $modelLabel = 'Testimoni';
    protected static ?string $pluralModelLabel = 'Testimoni';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

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
            Forms\Components\Section::make('Isi Testimoni')
                ->schema([
                    Forms\Components\TextInput::make('nama')->required()->maxLength(255),
                    Forms\Components\TextInput::make('jabatan')->maxLength(255)
                        ->placeholder('Contoh: Pimpinan Yayasan Al-Amanah'),
                    Forms\Components\TextInput::make('asal_pesantren')
                        ->label('Asal Lembaga')
                        ->maxLength(255)
                        ->helperText('Opsional, ditampilkan setelah jabatan jika diisi.'),
                    Forms\Components\Select::make('rating')
                        ->options([1 => '1 Bintang', 2 => '2 Bintang', 3 => '3 Bintang', 4 => '4 Bintang', 5 => '5 Bintang'])
                        ->default(5)
                        ->required(),
                    Forms\Components\Textarea::make('isi')->required()->rows(4)->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('asal_pesantren')->label('Asal Lembaga'),
                Tables\Columns\TextColumn::make('rating')->badge(),
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
