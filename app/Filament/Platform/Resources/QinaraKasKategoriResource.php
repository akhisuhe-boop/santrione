<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\QinaraKasKategoriResource\Pages;
use App\Models\QinaraKasKategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- kategori pemasukan/pengeluaran untuk pembukuan
 * internal bisnis Qinara sendiri (bukan kas sekolah/Yayasan client).
 */
class QinaraKasKategoriResource extends BaseResource
{
    protected static ?string $model = QinaraKasKategori::class;
    protected static ?string $navigationLabel = 'Kategori Kas';
    protected static ?string $navigationGroup = 'Keuangan Qinara';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Kategori Kas';
    protected static ?string $pluralModelLabel = 'Kategori Kas';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kategori Kas')
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('tipe')
                        ->label('Tipe')
                        ->options([
                            'masuk' => 'Kas Masuk (Pemasukan)',
                            'keluar' => 'Kas Keluar (Pengeluaran)',
                        ])
                        ->required(),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Nonaktifkan kategori yang sudah tidak dipakai lagi (tanpa menghapus riwayat transaksi lama).'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->badge()
                    ->color(fn (string $state) => $state === 'masuk' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => $state === 'masuk' ? 'Kas Masuk' : 'Kas Keluar')
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('transaksis_count')
                    ->label('Jumlah Transaksi')
                    ->counts('transaksis'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'masuk' => 'Kas Masuk',
                        'keluar' => 'Kas Keluar',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQinaraKasKategoris::route('/'),
            'create' => Pages\CreateQinaraKasKategori::route('/create'),
            'edit' => Pages\EditQinaraKasKategori::route('/{record}/edit'),
        ];
    }
}
