<?php

namespace App\Filament\Widgets;

use App\Models\PrestasiSiswa;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Widgets\TableWidget as BaseWidget;

class SantriBerprestasiTable extends BaseWidget
{
    protected static ?int $sort = 10;
    protected static ?string $heading = '10 Santri Berprestasi';
    protected int|string|array $columnSpan = 1;

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_prestasi')
            || auth()->user()->can('view_any_prestasi::siswa')
            || auth()->user()->can('view_any_laporan::prestasi');
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table

            ->query(
                PrestasiSiswa::query()
                    ->with([
                        'siswa',
                        'prestasi',
                    ])
                    ->latest()
                    ->limit(10)
            )

            ->paginated(false)
            ->striped()
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | NAMA SANTRI
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Santri')
                    ->sortable()
                    ->weight('medium'),

                /*
                |--------------------------------------------------------------------------
                | PRESTASI
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('prestasi.nama')
                    ->label('Prestasi')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                /*
                |--------------------------------------------------------------------------
                | POINT
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('point')
                    ->label('Point')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    }),

                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

            ]);
    }
}