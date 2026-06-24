<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrestasiSiswaResource\Pages;
use App\Filament\Resources\PrestasiSiswaResource\RelationManagers;
use App\Models\PrestasiSiswa;
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
use Filament\Forms\Components\Textarea;

class PrestasiSiswaResource extends Resource
{
    protected static ?string $model = PrestasiSiswa::class;
    protected static ?string $navigationGroup = 'Konseling';
    protected static ?string $navigationLabel = 'Input Prestasi';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
        Forms\Components\Section::make('Input Prestasi Siswa')
        ->description('Silakan input data prestasi siswa')
        ->schema([
            Forms\Components\Select::make('siswa_id')
                ->label('Nama Siswa')
                ->relationship('siswa', 'nama_lengkap')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('prestasi_id')
                ->label('Jenis Prestasi')
                ->relationship('prestasi', 'nama')
                ->preload()
                ->createOptionForm([
            Forms\Components\TextInput::make('nama')->label('Nama Prestasi')->required(),
            Forms\Components\TextInput::make('point')->label('Point')->numeric()->required(),
                ])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $data = \App\Models\Prestasi::find($state);
                    $set('point', $data?->point ?? 0);
                })
                ->required(),

            Forms\Components\TextInput::make('point')
                ->label('Point')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('tingkat')
                ->label('Tingkat')
                ->placeholder('Sekolah / Kecamatan / Kabupaten / Provinsi / Nasional'),

            Forms\Components\TextInput::make('peringkat')
                ->label('Peringkat / Penghargaan')
                ->placeholder('Juara 1, Juara 2, dll'),

            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->displayFormat('d/m/Y')
                ->native(false)
                ->required(),

            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),
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

            Tables\Columns\TextColumn::make('prestasi.nama')
                ->label('Prestasi')
                ->searchable(),

            Tables\Columns\TextColumn::make('tingkat')
                ->label('Tingkat'),

            Tables\Columns\TextColumn::make('peringkat')
                ->label('Peringkat'),

            Tables\Columns\TextColumn::make('point')
                ->label('Point')
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
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
            'index' => Pages\ListPrestasiSiswas::route('/'),
            'create' => Pages\CreatePrestasiSiswa::route('/create'),
            'edit' => Pages\EditPrestasiSiswa::route('/{record}/edit'),
        ];
    }
}
