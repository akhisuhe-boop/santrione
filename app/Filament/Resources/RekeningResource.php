<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekeningResource\Pages;
use App\Filament\Resources\RekeningResource\RelationManagers;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

class RekeningResource extends BaseResource
{
    protected static ?string $model = Rekening::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Input Rekening';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Rekening')
                ->icon('heroicon-o-clipboard-document-check')
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

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Rekening')
                        ->placeholder('Contoh: BSI Operasional / Mandiri PPDB')
                        ->required(),

                    Forms\Components\Select::make('tipe')
                        ->options([
                            'bank' => 'Bank',
                            'cash' => 'Cash (Bendahara)',
                            'ewallet' => 'E-Wallet (Internal)',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('bank')
                        ->label('Nama Bank')
                        ->placeholder('BSI / Mandiri')
                        ->visible(fn ($get) => $get('tipe') === 'bank'),

                    Forms\Components\TextInput::make('no_rekening')
                        ->label('No Rekening')
                        ->visible(fn ($get) => $get('tipe') === 'bank'),

                    Forms\Components\TextInput::make('atas_nama')
                        ->visible(fn ($get) => $get('tipe') === 'bank'),

                    Forms\Components\Select::make('keperluan')
                        ->label('Keperluan (untuk alur otomatis)')
                        ->helperText('Tandai kalau rekening ini yang dipakai sistem untuk alur PPDB otomatis milik lembaga ini. Kosongkan kalau rekening umum biasa.')
                        ->options([
                            'pendaftaran_ppdb' => 'Formulir Pendaftaran PPDB',
                            'daftar_ulang_ppdb' => 'Daftar Ulang PPDB',
                        ])
                        ->placeholder('Rekening umum (bukan bagian alur otomatis)')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_active')
                        ->default(true),

                ])
                ->columns(2),            
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pendaftaran_ppdb' => 'Formulir Pendaftaran PPDB',
                        'daftar_ulang_ppdb' => 'Daftar Ulang PPDB',
                        default => '-',
                    }),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->badge(),

                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('bank'),
                Tables\Columns\TextColumn::make('no_rekening'),
                Tables\Columns\TextColumn::make('atas_nama'),
                Tables\Columns\BadgeColumn::make('tipe'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRekenings::route('/'),
            'create' => Pages\CreateRekening::route('/create'),
            'edit' => Pages\EditRekening::route('/{record}/edit'),
        ];
    }
}
