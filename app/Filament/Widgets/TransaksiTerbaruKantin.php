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
        $tenant = Filament::getTenant();

        $lembagaIds = $tenant
            ? \App\Models\Lembaga::where('yayasan_id', $tenant->id)->pluck('id')
            : collect();

        return $table
            ->query(
                KantinTransaksi::withoutGlobalScopes()
                    ->whereIn('lembaga_id', $lembagaIds)
                    ->with(['siswa', 'items'])
            )
            ->columns([

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Waktu')
                    ->dateTime('d M, H:i'),

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->default('Umum'),

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
