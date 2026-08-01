<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KartuTemplateResource\Pages;
use App\Filament\Resources\KartuTemplateResource\RelationManagers;
use App\Models\KartuTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Storage;

class KartuTemplateResource extends BaseResource
{
    protected static ?string $model = KartuTemplate::class;
    protected static ?string $navigationLabel = 'Template Kartu';
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Template Kartu Siswa')
                ->icon('heroicon-o-credit-card')
                ->schema([
                Forms\Components\Select::make('lembaga_id')
                ->label('Lembaga')
                ->relationship(
                    'lembaga',
                    'nama',
                    modifyQueryUsing: fn ($query) => $query->where(
                        'yayasan_id',
                        \Filament\Facades\Filament::getTenant()?->id
                    ),
                )
                ->required()
                ->preload()
                ->searchable(),

                Forms\Components\Select::make('jenis')
                ->label('Jenis Kartu')
                ->options([
                    'siswa' => 'Kartu Siswa',
                    'pegawai' => 'Kartu Guru / Pegawai',
                ])

                ->required(),
                FileUpload::make('background_depan')
                ->label('Background Kartu Depan')
                ->image()
                ->disk('r2-public')
                ->directory('kartu-template')
                ->required(),

                FileUpload::make('background_belakang')
                ->label('Background Kartu Belakang')
                ->image()
                ->disk('r2-public')
                ->directory('kartu-template')
                ->required(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->badge(),

                Tables\Columns\TextColumn::make('jenis')
                ->label('Jenis')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'siswa' => 'Kartu Siswa',
                    'pegawai' => 'Kartu Pegawai',
                    default => '-',
                })
                ->badge()
                ->colors([
                    'success' => 'pegawai',
                    'primary' => 'siswa',
                ]),

                ImageColumn::make('background_depan')
                ->label('Depan')
                ->disk('r2-public'),

                ImageColumn::make('background_belakang')
                ->label('Belakang')
                ->disk('r2-public'),
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
            'index' => Pages\ListKartuTemplates::route('/'),
            'create' => Pages\CreateKartuTemplate::route('/create'),
            'edit' => Pages\EditKartuTemplate::route('/{record}/edit'),
        ];
    }
}
