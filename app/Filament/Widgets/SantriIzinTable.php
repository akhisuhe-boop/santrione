<?php

namespace App\Filament\Widgets;

use App\Models\Perizinan;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Widgets\TableWidget as BaseWidget;

class SantriIzinTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected static ?string $heading = '10 Santri Izin';

    protected int|string|array $columnSpan = 1;

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_perizinan')
            || auth()->user()->can('view_any_laporan::perizinan');
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
                Perizinan::query()
                    ->with([
                        'siswa',
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
                | JENIS IZIN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Jenis Izin')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | TANGGAL IZIN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Izin')
                    ->date('d M Y')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'disetujui' => 'success',
                        'pending' => 'warning',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),

            ]);
    }
}