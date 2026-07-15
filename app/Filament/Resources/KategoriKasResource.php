<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriKasResource\Pages;
use App\Filament\Resources\KategoriKasResource\RelationManagers;
use App\Models\KategoriKas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

class KategoriKasResource extends BaseResource
{
    protected static ?string $model = KategoriKas::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Kategori Kas';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Data Kategori Kas')
                ->description('Kelola kategori untuk kas masuk dan kas keluar')
                ->icon('heroicon-o-tag')
                ->schema([

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\Select::make('tipe')
                        ->label('Tipe')
                        ->options([
                            'masuk' => 'Kas Masuk',
                            'keluar' => 'Kas Keluar',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('kode')
                        ->label('Kode Kategori')
                        ->hidden(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                ])
                ->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'success' => 'masuk',
                        'danger' => 'keluar',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListKategoriKas::route('/'),
            'create' => Pages\CreateKategoriKas::route('/create'),
            'edit' => Pages\EditKategoriKas::route('/{record}/edit'),
        ];
    }
}
