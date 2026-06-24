<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YayasanResource\Pages;
use App\Filament\Resources\YayasanResource\RelationManagers;
use App\Models\Yayasan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class YayasanResource extends Resource
{
    protected static ?string $model = Yayasan::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Yayasan';
    protected static ?string $pluralModelLabel = 'Yayasan';
    protected static ?string $navigationLabel = 'Yayasan';    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Yayasan')
            ->description('Informasi dasar tentang yayasan')
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Yayasan')
                    ->required(),

                TextInput::make('ketua')
                    ->label('Ketua Yayasan'),

                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('yayasan'),
            ])
            ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\ImageColumn::make('logo')
                ->label('Logo')
                ->circular()
                ->size(40),

            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Yayasan')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('ketua')
                ->label('Ketua Yayasan')
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->date('d M Y')
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListYayasans::route('/'),
            'create' => Pages\CreateYayasan::route('/create'),            
            'edit' => Pages\EditYayasan::route('/{record}/edit'),
        ];
    }
}
