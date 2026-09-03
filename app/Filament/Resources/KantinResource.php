<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KantinResource\Pages;
use App\Models\Kantin;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class KantinResource extends BaseResource
{
    protected static ?string $model = Kantin::class;
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Kantin';
    protected static ?string $modelLabel = 'Kantin';
    protected static ?string $pluralModelLabel = 'Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Data Kantin')
                    ->description('Satu tenant bisa punya lebih dari satu kantin (mis. beberapa outlet, atau kantin bersama lintas lembaga). Kantin tidak wajib terikat ke 1 lembaga.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kantin')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('mis. Kantin Utama, Kantin SD, Kantin Putri'),

                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga (opsional)')
                            ->helperText('Kosongkan kalau kantin ini melayani lintas lembaga / tidak dinisbatkan ke lembaga tertentu.')
                            ->relationship('lembaga', 'nama', fn ($query) => $query->where(
                                'yayasan_id',
                                Filament::getTenant()?->id
                            ))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Kantin nonaktif tidak akan muncul buat dipilih di halaman Kasir.'),

                        Forms\Components\TextInput::make('pin')
                            ->label('PIN Kasir (opsional)')
                            ->password()
                            ->revealable()
                            ->maxLength(20)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Kalau diisi, kasir wajib masukkan PIN ini dulu sebelum bisa mengoperasikan kantin ini. Kosongkan untuk hapus PIN (kantin bisa langsung dipilih tanpa verifikasi). PIN lama tidak ditampilkan lagi di sini demi keamanan -- isi ulang kalau mau ganti.'),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kantin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->default('Lintas lembaga'),

                Tables\Columns\TextColumn::make('produk_count')
                    ->label('Jumlah Produk')
                    ->counts('produk'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\IconColumn::make('pin')
                    ->label('Ada PIN')
                    ->getStateUsing(fn ($record) => filled($record->pin))
                    ->boolean(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKantins::route('/'),
            'create' => Pages\CreateKantin::route('/create'),
            'edit' => Pages\EditKantin::route('/{record}/edit'),
        ];
    }
}
