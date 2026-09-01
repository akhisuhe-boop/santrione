<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\LembagaKantinPengaturanResource\Pages;
use App\Models\Lembaga;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Batas jumlah transaksi TUNAI kantin per hari HANYA diatur oleh
 * PLATFORM (Qinara) lewat resource ini -- SENGAJA tidak ada di
 * LembagaResource versi tenant (panel Yayasan biasa).
 *
 * Alasannya bisnis, bukan teknis: Qinara menggratiskan fitur e-Kantin
 * dan mengambil pendapatan dari fee top-up wallet. Kalau tenant bisa
 * atur sendiri limit tunai-nya, mereka tinggal kosongkan (jadi tidak
 * dibatasi) dan seluruh insentif pakai wallet hilang -- siswa bisa
 * terus-menerus dilayani tunai, wali tidak perlu top up sama sekali.
 * Jadi kontrolnya harus di tangan Qinara, independen dari keinginan
 * tenant.
 */
class LembagaKantinPengaturanResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Pengaturan Kantin';
    protected static ?string $modelLabel = 'Pengaturan Kantin Lembaga';
    protected static ?string $pluralModelLabel = 'Pengaturan Kantin Lembaga';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('limit_tunai_kantin_harian')
                ->label('Limit Transaksi Tunai Kantin / Hari')
                ->numeric()
                ->minValue(0)
                ->suffix('transaksi')
                ->helperText('Batas jumlah transaksi TUNAI pengunjung di kasir kantin lembaga ini per hari. Kosongkan = tidak dibatasi. Setelah kuota habis, kasir wajib pakai kartu/wallet untuk siswa & guru.'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // withoutGlobalScopes() -- lintas SEMUA Yayasan, sama seperti
            // pola di YayasanOverviewResource, karena ini resource level
            // platform, bukan level tenant.
            ->query(Lembaga::withoutGlobalScopes()->with('yayasan'))
            ->columns([

                Tables\Columns\TextColumn::make('yayasan.nama')
                    ->label('Yayasan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('limit_tunai_kantin_harian')
                    ->label('Limit Tunai / Hari')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === null ? 'Tidak dibatasi' : $state . ' transaksi')
                    ->color(fn ($state) => $state === null ? 'gray' : 'warning'),

            ])
            ->defaultSort('yayasan.nama')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLembagaKantinPengaturans::route('/'),
            'edit' => Pages\EditLembagaKantinPengaturan::route('/{record}/edit'),
        ];
    }
}
