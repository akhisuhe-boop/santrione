<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MataPelajaranResource\Pages;
use App\Filament\Resources\MataPelajaranResource\RelationManagers;
use App\Models\MataPelajaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;


class MataPelajaranResource extends Resource
{
    protected static ?string $model = MataPelajaran::class;
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Mata Pelajaran')
                ->description('Isi data mata pelajaran')
                ->icon('heroicon-o-book-open')
                ->schema([
            
                    TextInput::make('nama')
                        ->label('Nama Mata Pelajaran')
                        ->required()
                        ->maxLength(100),
            
                    Textarea::make('kompetensi')
                        ->label('Kompetensi Pembelajaran')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Contoh: konsep gaya, energi, dan gerak.')
                        ->helperText('Kompetensi ini akan digunakan untuk membuat deskripsi raport secara otomatis.'),
            
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
            
                TextColumn::make('kompetensi')
                    ->label('Kompetensi')
                    ->limit(80)
                    ->wrap(),
            
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
            'index' => Pages\ListMataPelajarans::route('/'),
            'create' => Pages\CreateMataPelajaran::route('/create'),
            'edit' => Pages\EditMataPelajaran::route('/{record}/edit'),
        ];
    }
}
