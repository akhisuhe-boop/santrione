<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahunAjaranResource\Pages;
use App\Filament\Resources\TahunAjaranResource\RelationManagers;
use App\Models\TahunAjaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TahunAjaranResource extends BaseResource
{
    protected static ?string $model = TahunAjaran::class;
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
            ->label('Tahun Ajaran')
            ->required()
            ->placeholder('2025/2026'),

        Forms\Components\Select::make('semester')
            ->options([
                'Ganjil' => 'Semester Ganjil',
                'Genap' => 'Semester Genap',
            ])
            ->required(),

        Forms\Components\Toggle::make('aktif')
            ->label('Tahun Aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                ->label('Tahun Ajaran')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('semester')
                ->badge()
                ->color('primary'),

            Tables\Columns\IconColumn::make('aktif')
                ->boolean()
                ->label('Aktif'),
        ])
            ->actions([
            Tables\Actions\EditAction::make(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListTahunAjarans::route('/'),
            'edit' => Pages\EditTahunAjaran::route('/{record}/edit'),
        ];
    }
}
