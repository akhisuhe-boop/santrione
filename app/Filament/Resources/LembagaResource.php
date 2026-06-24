<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LembagaResource\Pages;
use App\Filament\Resources\LembagaResource\RelationManagers;
use App\Models\Lembaga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LembagaResource extends Resource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Lembaga';
    protected static ?string $pluralModelLabel = 'Lembaga';
    protected static ?string $navigationLabel = 'Lembaga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Lembaga')
                ->description('Informasi dasar tentang lembaga')
                ->schema([

                    TextInput::make('nama')
                        ->label('Nama Lembaga')
                        ->required(),

                    Select::make('jenis')
                        ->label('Jenis Lembaga')
                        ->options([
                            'tk' => 'TK',
                            'sd' => 'SD',
                            'smp' => 'SMP',
                            'sma' => 'SMA',                            
                        ])
                        ->required(),

                            TextInput::make('kepala_sekolah')
                                ->label('Kepala Sekolah'),   
                    Forms\Components\Toggle::make('is_tes')
                        ->label('Menggunakan Tes Masuk?')
                        ->helperText('Jika aktif, siswa harus mengikuti tes sebelum dinyatakan lulus')
                        ->default(true)
                        ])
                        ->columns(3),
                    ]);
                    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Lembaga')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('jenis')
            ->badge()
            ->color('success')
            ->label('jenis'),

            Tables\Columns\TextColumn::make('kepala_sekolah')
                ->label('Kepala Sekolah'),

            Tables\Columns\TextColumn::make('is_tes')
            ->label('Tes Masuk')
            ->formatStateUsing(fn ($state) => $state ? 'Ya (Tes)' : 'Tidak')
            ->badge()
            ->color(fn ($state) => $state ? 'success' : 'danger')
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
            ])
            ->filters([
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
            'index' => Pages\ListLembagas::route('/'),
            'create' => Pages\CreateLembaga::route('/create'),
            'edit' => Pages\EditLembaga::route('/{record}/edit'),
        ];
    }
}
