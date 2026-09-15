<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Filament\Resources\KelasResource\RelationManagers;
use App\Models\Kelas;
use App\Models\Pegawai;
use App\Models\Lembaga;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KelasResource extends BaseResource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 3;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('Data Kelas')
                ->description('Informasi dasar tentang kelas')
                ->schema([

                    Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->relationship('lembaga', 'nama')
                        ->preload()
                        ->required(),

                    TextInput::make('nama')
                        ->label('Nama Kelas')
                        ->required(),

                    Select::make('wali_kelas_id')
                        ->label('Wali Kelas')
                        ->relationship('waliKelas', 'nama')
                        ->searchable()
                        ->preload(),

                ])
                ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                ->label('Nama Kelas')
                ->sortable()
                ->searchable(),
                
                Tables\Columns\TextColumn::make('lembaga.nama')
                ->label('Lembaga')
                ->badge()
                ->color(fn ($record) => match ($record->lembaga_id) {
                1 => 'success',   // SDIT
                2 => 'warning',   // SMPIT
                3 => 'primary',    // SMAIT
                4 => 'info',       // SMK
                default => 'gray',
                })
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('waliKelas.nama')
                ->label('Wali Kelas')
                ->searchable(),

                Tables\Columns\TextColumn::make('siswa_count')
                ->label('Jumlah Siswa')
                ->counts([
                    'siswa' => fn ($q) => $q->where('status_siswa', 'Aktif'),
                ])
                ->badge()
                ->color('primary')
                ->suffix(' Siswa'),

            ])
            ->actions([
            Tables\Actions\Action::make('lihat_siswa')
                ->label('Siswa')
                ->icon('heroicon-o-users')
                ->color('info')
                ->url(fn ($record) =>
                    route('filament.admin.resources.siswas.index', [
                        'tenant' => \Filament\Facades\Filament::getTenant()?->slug,
                        'tableFilters[kelas_id][value]' => $record->id,
                    ])
                ),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),

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
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
