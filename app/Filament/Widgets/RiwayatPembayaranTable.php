<?php

namespace App\Filament\Widgets;

use App\Models\Pembayaran;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Widgets\TableWidget as BaseWidget;

class RiwayatPembayaranTable extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Riwayat Pembayaran';

    protected int|string|array $columnSpan = 'full';

    public ?int $siswaId = null;

    public ?int $ppdbId = null;

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_pembayaran')
            || auth()->user()->can('page_LaporanPembayaran');
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table

            ->query($this->getTableQuery())

            ->paginated([10, 25, 50])

            ->defaultPaginationPageOption(10)

            ->defaultSort('created_at', 'desc')

            ->striped()

            ->columns([

                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | JENIS TAGIHAN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('tagihan.judul')
                    ->label('Jenis Tagihan')
                    ->searchable()
                    ->wrap()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | NOMINAL
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('nominal')
                ->label('Nominal Dibayar')
                ->formatStateUsing(
                    fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')
                )
                ->sortable()
                ->weight('bold')
                ->color('success'),

                /*
                |--------------------------------------------------------------------------
                | METODE PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('metode')
                ->label('Metode')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'admin' => 'primary',
                    'transfer' => 'success',
                    'ewallet' => 'warning',
                    'gateway' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn ($state) => match ($state) {
                    'admin' => 'Tunai',
                    'transfer' => 'Transfer',
                    'ewallet' => 'Saldo',
                    'gateway' => 'Gateway',
                    default => $state,
                }),

                /*
                |--------------------------------------------------------------------------
                | STATUS PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'sukses' => 'success',
                        'gagal' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'sukses' => 'Berhasil',
                        'gagal' => 'Gagal',
                        default => $state,
                    }),

            ])

            ->emptyStateHeading('Belum ada pembayaran')

            ->emptyStateDescription('Data riwayat pembayaran akan tampil di sini.')

            ->emptyStateIcon('heroicon-o-credit-card');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY
    |--------------------------------------------------------------------------
    */

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = Pembayaran::query()
            ->with([
                'tagihan',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | MODE SISWA
        |--------------------------------------------------------------------------
        */

        if ($this->siswaId) {

            $query->whereHas('tagihan', function ($q) {

                $q->where('siswa_id', $this->siswaId);

            });
        }

        /*
        |--------------------------------------------------------------------------
        | MODE PPDB
        |--------------------------------------------------------------------------
        */

        elseif ($this->ppdbId) {

            $query->whereHas('tagihan', function ($q) {

                $q->where('ppdb_id', $this->ppdbId);

            });
        }

        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        else {

            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}