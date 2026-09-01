<?php

namespace App\Filament\Widgets;

use App\Models\KantinTransaksi;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TransaksiTerbaruKantin extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Transaksi Terbaru';

    public function table(Table $table): Table
    {
        $yayasanId = Filament::getTenant()?->id;

        return $table
            ->query(
                // yayasan_id (bukan lembaga_id) supaya transaksi
                // pengunjung/tunai (lembaga_id kosong) tetap ikut tampil.
                KantinTransaksi::withoutGlobalScopes()
                    ->where('yayasan_id', $yayasanId)
                    ->with(['siswa', 'pegawai', 'items'])
            )
            ->columns([

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Waktu')
                    ->dateTime('d M, H:i'),

                Tables\Columns\TextColumn::make('pembeli')
                    ->label('Pembeli')
                    ->state(fn ($record) => $record->siswa?->nama_lengkap
                        ?? $record->pegawai?->nama
                        ?? 'Umum (Pengunjung)'),

                Tables\Columns\TextColumn::make('items')
                    ->label('Item')
                    ->formatStateUsing(fn ($record) => $record->items->pluck('nama_produk')->implode(', '))
                    ->limit(30)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('metode')
                    ->colors([
                        'success' => 'wallet',
                        'gray' => 'tunai',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignEnd(),

            ])
            ->defaultSort('tanggal', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
