<?php

namespace App\Filament\Widgets;

use App\Models\TahfidzSetoran;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Widgets\TableWidget as BaseWidget;

class SantriTahfidzTable extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = '10 Santri Tahfidz';

    protected int|string|array $columnSpan = 1;

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_tahfidz::setoran')
            || auth()->user()->can('view_any_tahfidz::target')
            || auth()->user()->can('view_any_laporan::tahfidz');
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
                TahfidzSetoran::query()
                    ->with([
                        'siswa',
                        'surah',
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
                | JENIS TAHFIDZ
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis Tahfidz')
                    ->badge()
                    ->color('primary'),

                /*
                |--------------------------------------------------------------------------
                | SURAH
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('surah.nama')
                    ->label('Surah')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | JUMLAH AYAT
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('jumlah_ayat')
                    ->label('Ayat')
                    ->badge()
                    ->color('warning'),

                /*
                |--------------------------------------------------------------------------
                | NILAI
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger',
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