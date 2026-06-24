<?php

namespace App\Filament\Widgets;

use App\Models\PelanggaranSiswa;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Widgets\TableWidget as BaseWidget;

class SantriMelanggarTable extends BaseWidget
{
    protected static ?int $sort = 9;

    protected static ?string $heading = '10 Santri Melanggar';

    protected int|string|array $columnSpan = 1;

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_pelanggaran')
            || auth()->user()->can('view_any_pelanggaran::siswa')
            || auth()->user()->can('view_any_laporan::pelanggaran');
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
                PelanggaranSiswa::query()
                    ->with([
                        'siswa',
                        'pelanggaran',
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
                | PELANGGARAN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('pelanggaran.nama')
                    ->label('Pelanggaran')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                /*
                |--------------------------------------------------------------------------
                | POINT
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('point')
                    ->label('Point')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        default => 'success',
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