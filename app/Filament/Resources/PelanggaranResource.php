<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelanggaranResource\Pages;
use App\Filament\Resources\PelanggaranResource\RelationManagers;
use App\Models\Pelanggaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PelanggaranResource extends BaseResource
{
    protected static ?string $model = Pelanggaran::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?string $navigationLabel = 'Master Pelanggaran';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pelanggaran')
        ->description('Silakan isi jenis pelanggaran dan bobot poinnya')
        ->schema([

            Forms\Components\TextInput::make('nama')
                ->label('Nama Pelanggaran')
                ->placeholder('Contoh: Tidak memakai seragam')
                ->required(),

            Forms\Components\Select::make('kategori')
                ->label('Kategori')
                ->options([
                    'Ringan' => 'Ringan',
                    'Sedang' => 'Sedang',
                    'Berat' => 'Berat',
                ])
                ->required(),

            Forms\Components\TextInput::make('point')
                ->label('Point')
                ->numeric()
                ->required(),

            ])
            ->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
            ->label('Pelanggaran')
            ->searchable()
            ->sortable(),

            BadgeColumn::make('kategori')
            ->colors([
                'success' => 'Ringan',
                'warning' => 'Sedang',
                'danger' => 'Berat',
            ]),

            TextColumn::make('point')
            ->label('Point')
            ->sortable(),

            TextColumn::make('created_at')
            ->label('Dibuat')
            ->date('d M Y'),
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
            'index' => Pages\ListPelanggarans::route('/'),
            'create' => Pages\CreatePelanggaran::route('/create'),
            'edit' => Pages\EditPelanggaran::route('/{record}/edit'),
        ];
    }
}
