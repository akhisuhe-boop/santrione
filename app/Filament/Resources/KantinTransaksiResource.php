<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KantinTransaksiResource\Pages;
use App\Models\KantinTransaksi;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class KantinTransaksiResource extends BaseResource
{
    protected static ?string $model = KantinTransaksi::class;
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $modelLabel = 'Transaksi Kantin';
    protected static ?string $pluralModelLabel = 'Transaksi Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 2;

    // Riwayat saja — transaksi dibuat lewat halaman kasir (/kantin/kasir),
    // bukan lewat form Filament biasa.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getModel()::query()->with(['siswa', 'pegawai', 'lembaga', 'items']))
            ->columns([

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembeli')
                    ->label('Pembeli')
                    ->state(fn ($record) => $record->siswa?->nama_lengkap
                        ?? $record->pegawai?->nama
                        ?? 'Umum (Pengunjung)')
                    ->description(fn ($record) => $record->siswa
                        ? 'Siswa'
                        : ($record->pegawai ? 'Guru / Staf' : null))
                    ->searchable(query: function ($query, string $search) {
                        return $query
                            ->whereHas('siswa', fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                            ->orWhereHas('pegawai', fn ($q) => $q->where('nama', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('metode')
                    ->colors([
                        'success' => 'wallet',
                        'gray' => 'tunai',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('items')
                    ->label('Item')
                    ->formatStateUsing(fn ($record) => $record->items->pluck('nama_produk')->implode(', '))
                    ->limit(50)
                    ->wrap(),

            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('metode')
                    ->options(['wallet' => 'Wallet', 'tunai' => 'Tunai']),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari'),
                        \Filament\Forms\Components\DatePicker::make('sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
                            ->when($data['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('kode')->label('Kode'),
                        \Filament\Infolists\Components\TextEntry::make('pembeli')
                            ->label('Pembeli')
                            ->state(fn ($record) => $record->siswa?->nama_lengkap
                                ?? $record->pegawai?->nama
                                ?? 'Umum (Pengunjung)'),
                        \Filament\Infolists\Components\TextEntry::make('lembaga.nama')->label('Lembaga')->default('—'),
                        \Filament\Infolists\Components\TextEntry::make('metode')->label('Metode'),
                        \Filament\Infolists\Components\TextEntry::make('total')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                        \Filament\Infolists\Components\RepeatableEntry::make('items')
                            ->label('Item')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('nama_produk')->label('Produk'),
                                \Filament\Infolists\Components\TextEntry::make('qty')->label('Qty'),
                                \Filament\Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKantinTransaksis::route('/'),
        ];
    }
}
