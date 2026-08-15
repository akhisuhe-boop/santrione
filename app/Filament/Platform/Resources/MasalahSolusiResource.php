<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\MasalahSolusiResource\Pages;
use App\Models\MasalahSolusi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class MasalahSolusiResource extends BaseResource
{
    protected static ?string $model = MasalahSolusi::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Masalah & Solusi';
    protected static ?string $modelLabel = 'Masalah & Solusi';
    protected static ?string $pluralModelLabel = 'Masalah & Solusi';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

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
            Forms\Components\Section::make('Poin Sebelum & Sesudah')
                ->description('Satu baris = satu pasangan poin di section "Tinggalkan Sistem Manual". Urutan yang sama akan disandingkan kiri-kanan.')
                ->schema([
                    Forms\Components\TextInput::make('teks_masalah')
                        ->label('Poin Masalah (kolom kiri)')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('teks_solusi')
                        ->label('Poin Solusi (kolom kanan)')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

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
                Tables\Columns\TextColumn::make('teks_masalah')->label('Masalah')->limit(40),
                Tables\Columns\TextColumn::make('teks_solusi')->label('Solusi')->limit(40),
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
            'index' => Pages\ListMasalahSolusis::route('/'),
            'create' => Pages\CreateMasalahSolusi::route('/create'),
            'edit' => Pages\EditMasalahSolusi::route('/{record}/edit'),
        ];
    }
}
