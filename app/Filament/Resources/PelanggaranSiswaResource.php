<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelanggaranSiswaResource\Pages;
use App\Filament\Resources\PelanggaranSiswaResource\RelationManagers;
use App\Models\PelanggaranSiswa;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;

class PelanggaranSiswaResource extends BaseResource
{
    protected static ?string $model = PelanggaranSiswa::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?string $navigationLabel = 'Input Pelanggaran';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Input Pelanggaran Siswa')
                ->description('Silakan input data pelanggaran siswa secara lengkap')
                ->schema([

            Forms\Components\Select::make('siswa_id')
                ->label('Nama Siswa')
                ->relationship('siswa', 'nama_lengkap')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('pelanggaran_id')
                ->label('Jenis Pelanggaran')
                ->relationship('pelanggaran', 'nama')
                ->preload()

                ->createOptionForm([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Pelanggaran')
                        ->required(),

                    Forms\Components\Select::make('kategori')
                        ->options([
                            'Ringan' => 'Ringan',
                            'Sedang' => 'Sedang',
                            'Berat' => 'Berat',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('point')
                        ->numeric()
                        ->required(),
                ])

                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $data = \App\Models\Pelanggaran::find($state);
                    $set('point', $data?->point ?? 0);
                })
                ->required(),

            Forms\Components\TextInput::make('point')
                ->numeric()
                ->readonly(),

            Forms\Components\DatePicker::make('tanggal')
                ->required(),

            Forms\Components\FileUpload::make('bukti')
                ->image(),

            Forms\Components\Textarea::make('catatan'),

        ])
        ->columns(3)
]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('siswa.lembaga.nama')
                ->label('Lembaga')
                ->sortable(),

            Tables\Columns\TextColumn::make('siswa.kelas.nama')
                ->label('Kelas')
                ->sortable(),

            Tables\Columns\TextColumn::make('pelanggaran.nama')
                ->label('Pelanggaran')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('pelanggaran.kategori')
                ->label('Kategori')
                ->colors([
                    'success' => 'Ringan',
                    'warning' => 'Sedang',
                    'danger' => 'Berat',
                ]),

            Tables\Columns\TextColumn::make('point')
                ->label('Point')
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal')
                ->date('d M Y')
                ->label('Tanggal'),
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
            'index' => Pages\ListPelanggaranSiswas::route('/'),
            'create' => Pages\CreatePelanggaranSiswa::route('/create'),
            'edit' => Pages\EditPelanggaranSiswa::route('/{record}/edit'),
        ];
    }
}
