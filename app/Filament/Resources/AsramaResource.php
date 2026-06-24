<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsramaResource\Pages;
use App\Filament\Resources\AsramaResource\RelationManagers;
use App\Models\Asrama;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Lembaga;
use App\Models\Pegawai;

class AsramaResource extends Resource
{
    protected static ?string $model = Asrama::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make('Data Asrama')
                ->description('Informasi utama asrama pesantren')
                ->schema([

                    Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->relationship('lembaga', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('nama')
                        ->label('Nama Asrama')
                        ->required()
                        ->maxLength(255),

                    Select::make('wali_asrama_id')
                        ->label('Wali Asrama')
                        ->relationship('waliAsrama', 'nama')
                        ->searchable()
                        ->preload(),

                    TextInput::make('kapasitas')
                        ->label('Kapasitas')
                        ->numeric()
                        ->suffix('Santri'),

                ])
                ->columns(2),

            Section::make('Keterangan Tambahan')
                ->schema([

                    \Filament\Forms\Components\Textarea::make('keterangan')
                        ->rows(4)
                        ->columnSpanFull(),

                ]),

        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

        Tables\Columns\TextColumn::make('nama')
            ->label('Nama Asrama')
            ->searchable()
            ->sortable(),

        Tables\Columns\TextColumn::make('lembaga.nama')
            ->label('Lembaga')
            ->badge()
            ->color(fn ($record) => match ($record->lembaga_id) {
                1 => 'success',
                2 => 'warning',
                3 => 'primary',
                4 => 'info',
                default => 'gray',
            })
            ->sortable(),

        Tables\Columns\TextColumn::make('waliAsrama.nama')
            ->label('Wali Asrama')
            ->badge()
            ->color('success')
            ->searchable(),

        Tables\Columns\TextColumn::make('siswa_count')
            ->label('Jumlah Santri')
            ->counts('siswa')
            ->badge()
            ->color('primary')
            ->suffix(' Santri'),

        Tables\Columns\TextColumn::make('kapasitas')
            ->label('Kapasitas')
            ->badge()
            ->color('warning')
            ->suffix(' Orang'),

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
            'index' => Pages\ListAsramas::route('/'),
            'create' => Pages\CreateAsrama::route('/create'),
            'edit' => Pages\EditAsrama::route('/{record}/edit'),
        ];
    }
}
