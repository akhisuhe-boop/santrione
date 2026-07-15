<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrestasiResource\Pages;
use App\Filament\Resources\PrestasiResource\RelationManagers;
use App\Models\Prestasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class PrestasiResource extends BaseResource
{
    protected static ?string $model = Prestasi::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?string $navigationLabel = 'Master Prestasi';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Data Prestasi')
            ->description('Silakan isi jenis prestasi dan bobot poinnya')
            ->schema([

            Forms\Components\TextInput::make('nama')
                ->label('Nama Prestasi')
                ->placeholder('Contoh: Juara 1 Lomba MTQ')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('point')
                ->label('Point')
                ->numeric()
                ->required(),

        ])
        ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Prestasi')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('point')
                ->label('Point')
                ->sortable()
                ->alignCenter(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->date('d M Y')
                ->sortable(),
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
            'index' => Pages\ListPrestasis::route('/'),
            'create' => Pages\CreatePrestasi::route('/create'),
            'edit' => Pages\EditPrestasi::route('/{record}/edit'),
        ];
    }
}
