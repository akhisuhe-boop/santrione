<?php

namespace App\Filament\Widgets;

use App\Models\KantinTransaksiItem;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProdukTerlarisKantin extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Produk Terlaris Bulan Ini';

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        $lembagaIds = $tenant
            ? \App\Models\Lembaga::where('yayasan_id', $tenant->id)->pluck('id')
            : collect();

        return $table
            ->query(
                KantinTransaksiItem::query()
                    ->whereHas('transaksi', fn ($q) => $q->withoutGlobalScopes()->whereIn('lembaga_id', $lembagaIds))
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->selectRaw('MIN(id) as id, nama_produk, SUM(qty) as total_qty, SUM(subtotal) as total_omzet')
                    ->groupBy('nama_produk')
                    ->orderByDesc('total_qty')
            )
            ->columns([

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Produk'),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Terjual')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_omzet')
                    ->label('Omzet')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignEnd(),

            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
