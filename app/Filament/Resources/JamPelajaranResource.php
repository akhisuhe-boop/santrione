<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JamPelajaranResource\Pages;
use App\Models\JamPelajaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JamPelajaranResource extends Resource
{
    protected static ?string $model = JamPelajaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Jam Pelajaran';

    protected static ?int $navigationSort = 2;

    // =========================
    // FORM
    // =========================
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Jam Pelajaran')
                ->columns(2)
                ->schema([

                    Forms\Components\Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama')
                        ->placeholder('Contoh: Jam ke-1 / Jam Pertama')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TimePicker::make('jam_mulai')
                        ->label('Jam Mulai')
                        ->seconds(false)
                        ->required(),

                    Forms\Components\TimePicker::make('jam_selesai')
                        ->label('Jam Selesai')
                        ->seconds(false)
                        ->required()
                        ->after('jam_mulai'),

                    Forms\Components\TextInput::make('durasi_jp')
                        ->label('Durasi JP')
                        ->numeric()
                        ->required()
                        ->default(2),

                    Forms\Components\TextInput::make('urutan')
                        ->label('Urutan')
                        ->numeric()
                        ->required(),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Aktif')
                        ->default(true),

                ]),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jam_mulai')
                    ->time('H:i')
                    ->label('Mulai'),

                Tables\Columns\TextColumn::make('jam_selesai')
                    ->time('H:i')
                    ->label('Selesai'),

                Tables\Columns\TextColumn::make('durasi_jp')
                    ->label('JP')
                    ->badge(),

                Tables\Columns\TextColumn::make('urutan')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJamPelajarans::route('/'),
            'create' => Pages\CreateJamPelajaran::route('/create'),
            'edit' => Pages\EditJamPelajaran::route('/{record}/edit'),
        ];
    }
}