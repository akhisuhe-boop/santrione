<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Carbon\Carbon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayrollResource extends Resource
{
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Penggajian';
    protected static ?string $modelLabel = 'Penggajian';
    protected static ?string $pluralModelLabel = 'Penggajian';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            /*
            |--------------------------------------------------------------------------
            | INFORMASI PENGGAJIAN
            |--------------------------------------------------------------------------
            */
            Forms\Components\Section::make('Informasi Penggajian')
                ->schema([
                    Forms\Components\Placeholder::make('pegawai')
                        ->label(' ')
                        ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                            <div>
                                <div class="text-sm text-gray-500">
                                    Pegawai
                                </div>
                                <div class="text-lg font-semibold">
                                    ' . ($record?->pegawai?->nama ?? '-') . '
                                </div>
                            </div>
                        ')),

                    Forms\Components\Placeholder::make('periode')
                        ->label(' ')
                        ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                            <div>
                                <div class="text-sm text-gray-500">
                                    Periode
                                </div>
                                <div class="text-lg font-semibold">
                                    ' . ($record?->bulan ?? '-') . ' / ' . ($record?->tahun ?? '-') . '
                                </div>
                            </div>
                        ')),

                    Forms\Components\Placeholder::make('status')
                        ->label(' ')
                        ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                            <div>
                                <div class="text-sm text-gray-500">
                                    Status
                                </div>
                                <div class="text-lg font-semibold">
                                    ' . strtoupper($record?->status ?? '-') . '
                                </div>
                            </div>
                        ')),
                ])
                ->columns(3),

            /*
            |--------------------------------------------------------------------------
            | TOTAL PAYROLL
            |--------------------------------------------------------------------------
            */
            Forms\Components\Section::make('Total Payroll')
                ->schema([
                    Forms\Components\Placeholder::make('subtotal')
                        ->label('Subtotal')
                        ->content(fn ($record) =>
                            'Rp ' . number_format(
                                $record?->subtotal ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        ),

                    Forms\Components\Placeholder::make('total_gaji')
                        ->label('Total Gaji')
                        ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                            <div class="text-2xl font-bold text-primary-600">
                                Rp ' . number_format(
                                    $record?->total_gaji ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) . '
                            </div>
                        ')),
                ])
                ->columns(2),

        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([

            /*
            |--------------------------------------------------------------------------
            | PEGAWAI
            |--------------------------------------------------------------------------
            */
            Tables\Columns\TextColumn::make('pegawai.nama')
                ->label('Pegawai')
                ->searchable()
                ->sortable()
                ->description(fn ($record) =>
                    $record->pegawai?->niy
                ),

            /*
            |--------------------------------------------------------------------------
            | PERIODE
            |--------------------------------------------------------------------------
            */
            Tables\Columns\TextColumn::make('periode')
            ->label('Periode')
            ->badge()
            ->color('info')
            ->getStateUsing(function ($record) {

                $bulan = match ((int) $record->bulan) {
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                    default => '-',
                };

                return $bulan . ' / ' . $record->tahun;
            }),

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */
            Tables\Columns\TextColumn::make('subtotal')
                ->label('Subtotal')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format(
                        $state,
                        0,
                        ',',
                        '.'
                    )
                ),

            /*
            |--------------------------------------------------------------------------
            | BONUS
            |--------------------------------------------------------------------------
            */
            Tables\Columns\TextColumn::make('bonus')
                ->label('Bonus')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format(
                        $state,
                        0,
                        ',',
                        '.'
                    )
                )

                ->color('success'),

            /*
            |--------------------------------------------------------------------------
            | POTONGAN
            |--------------------------------------------------------------------------
            */

            Tables\Columns\TextColumn::make('potongan')
                ->label('Potongan')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format(
                        $state,
                        0,
                        ',',
                        '.'
                    )
                )
                ->color('danger'),
            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */
            Tables\Columns\TextColumn::make('total_gaji')
                ->label('Total Gaji')
                ->weight('bold')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format(
                        $state,
                        0,
                        ',',
                        '.'
                    )
                ),

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'gray' => 'draft',
                    'warning' => 'disetujui',
                    'success' => 'dibayar',
                    'danger' => 'cancelled',
                ]),

        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'disetujui' => 'Disetujui',
                    'dibayar' => 'Dibayar',
                    'cancelled' => 'Cancelled',
                ]),
        ])

        ->actions([

    /*
    |--------------------------------------------------------------------------
    | RINCIAN
    |--------------------------------------------------------------------------
    */
    Tables\Actions\ViewAction::make()
        ->label('Rincian')
        ->icon('heroicon-o-eye'),
        
    Tables\Actions\Action::make('cetak')
    ->label('Cetak Slip Gaji')
    ->icon('heroicon-o-printer')
    ->color('success')
    ->visible(fn ($record) =>
        $record->status === 'dibayar'
    )

    ->url(fn ($record) =>
        route('slip-gaji.cetak', $record)
    )

    ->openUrlInNewTab(),

    /*
    |--------------------------------------------------------------------------
    | REGENERATE
    |--------------------------------------------------------------------------
    */
    Tables\Actions\Action::make('regenerate')
        ->label('Regenerate')
        ->icon('heroicon-o-arrow-path')
        ->color('warning')
        ->visible(fn ($record) =>
            $record->status === 'draft'
        )
        ->requiresConfirmation()
        ->action(function ($record) {
            app(\App\Services\PayrollService::class)
                ->regenerate($record);

        }),

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    Tables\Actions\Action::make('approve')
        ->label('Approve')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->visible(fn ($record) =>
            $record->status === 'draft'
        )
        ->requiresConfirmation()
        ->action(function ($record) {

            $record->update([
                'status' => 'disetujui',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        
        }),

    /*
    |--------------------------------------------------------------------------
    | MARK AS PAID
    |--------------------------------------------------------------------------
    */
    Tables\Actions\Action::make('pay')
    ->label('Mark Paid')
    ->icon('heroicon-o-banknotes')
    ->color('primary')

    ->visible(fn ($record) =>
        $record->status === 'disetujui'
    )
    ->form([

        Forms\Components\Select::make('rekening_id')
            ->label('Rekening Pembayaran')

            ->options(
                \App\Models\Rekening::where('is_active', true)
                    ->get()
                    ->pluck('label', 'id')
            )

            ->searchable()

            ->required(),

    ])
    ->requiresConfirmation()
    ->action(function ($record, array $data) {

        /*
        |--------------------------------------------------------------------------
        | CEGAH DOUBLE PAYMENT
        |--------------------------------------------------------------------------
        */

        if ($record->status === 'dibayar') {
            return;
        }
        /*
        |--------------------------------------------------------------------------
        | CEGAH DOUBLE KAS
        |--------------------------------------------------------------------------
        */

        $sudahAdaKas = \App\Models\Kas::where(
            'payroll_id',
            $record->id
        )->exists();

        if ($sudahAdaKas) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS PAYROLL
        |--------------------------------------------------------------------------
        */

        $record->update([
            'status'         => 'dibayar',
            'tanggal_bayar'  => now(),
            'paid_at'        => now(),
            'paid_by'        => auth()->id(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL KATEGORI KAS
        |--------------------------------------------------------------------------
        */

        $kategori = \App\Models\KategoriKas::where('nama', 'Gaji Pegawai')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | AUTO KAS KELUAR
        |--------------------------------------------------------------------------
        */

        \App\Models\Kas::create([
            'payroll_id' => $record->id,
            'tipe' => 'keluar',
            'kategori_id' => $kategori?->id,

            /*
            |--------------------------------------------------------------------------
            | REKENING DEFAULT
            |--------------------------------------------------------------------------
            */

            'rekening_id' => $data['rekening_id'],
            'nominal' => $record->total_gaji,
            'sumber' => 'payroll',
            'tanggal' => now(),
            'keterangan' => sprintf(
                'Gaji Pegawai : %s periode %s',
                $record->pegawai?->nama,
                Carbon::create(
                    $record->tahun,
                    $record->bulan
                )
                ->locale('id')
                ->translatedFormat('F Y')
            ),

            'penanggung_jawab' => auth()->user()?->name,
            'lembaga_id' => $record->pegawai?->lembagas?->first()?->id,

        ]);

    }),

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    Tables\Actions\Action::make('cancel')
        ->label('Cancel')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->visible(fn ($record) =>
            in_array($record->status, [
                'draft',
                'disetujui',
            ])
        )

        ->requiresConfirmation()
        ->action(function ($record) {
            $record->update([
                'status' => 'cancelled',
            ]);
        }),

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    Tables\Actions\EditAction::make()
        ->visible(fn ($record) =>
            $record->status === 'draft'
        ),

])
        ->bulkActions([
        
    /*
    |--------------------------------------------------------------------------
    | BULK APPROVE
    |--------------------------------------------------------------------------
    */
    Tables\Actions\BulkAction::make('bulk_approve')
        ->label('Approve Selected')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->requiresConfirmation()
        ->action(function ($records) {

            foreach ($records as $record) {

                if ($record->status !== 'draft') {
                    continue;
                }

                $record->update([
                    'status' => 'disetujui',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
            }

        }),

    /*
    |--------------------------------------------------------------------------
    | BULK PAID
    |--------------------------------------------------------------------------
    */
    Tables\Actions\BulkAction::make('bulk_paid')
    ->label('Mark Paid')
    ->icon('heroicon-o-banknotes')
    ->color('primary')
    ->form([

        Forms\Components\Select::make('rekening_id')
            ->label('Rekening Pembayaran')

            ->options(
                \App\Models\Rekening::where('is_active', true)
                    ->get()
                    ->pluck('label', 'id')
            )

            ->searchable()
            ->required(),

    ])
    ->requiresConfirmation()
    ->action(function ($records, array $data) {

        foreach ($records as $record) {

            /*
            |--------------------------------------------------------------------------
            | HANYA APPROVED YANG BISA DIBAYAR
            |--------------------------------------------------------------------------
            */

            if ($record->status !== 'disetujui') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CEGAH DOUBLE KAS
            |--------------------------------------------------------------------------
            */

            $sudahAdaKas = \App\Models\Kas::where(
                'payroll_id',
                $record->id
            )->exists();

            if ($sudahAdaKas) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS PAYROLL
            |--------------------------------------------------------------------------
            */

            $record->update([
                'status'         => 'dibayar',
                'tanggal_bayar'  => now(),
                'paid_at'        => now(),
                'paid_by'        => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | AMBIL KATEGORI KAS
            |--------------------------------------------------------------------------
            */

            $kategori = \App\Models\KategoriKas::where(
                'nama',
                'Gaji Pegawai'
            )->first();

            /*
            |--------------------------------------------------------------------------
            | AUTO KAS KELUAR
            |--------------------------------------------------------------------------
            */

            \App\Models\Kas::create([

                'payroll_id' => $record->id,
                'tipe' => 'keluar',
                'kategori_id' => $kategori?->id,

                /*
                |--------------------------------------------------------------------------
                | REKENING DEFAULT
                |--------------------------------------------------------------------------
                */

                'rekening_id' => $data['rekening_id'],
                'nominal' => $record->total_gaji,
                'sumber' => 'payroll',
                'tanggal' => now(),
                'keterangan' => sprintf(
                    'Gaji Pegawai : %s periode %s',
                    $record->pegawai?->nama,
                    Carbon::create(
                        $record->tahun,
                        $record->bulan
                    )
                    ->locale('id')
                    ->translatedFormat('F Y')
                ),

                'penanggung_jawab' =>
                    auth()->user()?->name,

                'lembaga_id' =>
                    $record->pegawai?->lembagas?->first()?->id,

            ]);

        }

    })
        ]);
}

public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
{
    return $infolist
        ->schema([

            /*
|--------------------------------------------------------------------------
| TOP GRID
|--------------------------------------------------------------------------
*/

\Filament\Infolists\Components\Grid::make(2)
    ->extraAttributes([
        'class' => 'modern-payroll-grid',
    ])
    ->schema([

        /*
        |--------------------------------------------------------------------------
        | INFORMASI PAYROLL
        |--------------------------------------------------------------------------
        */

        \Filament\Infolists\Components\Section::make('Informasi Payroll')
            ->extraAttributes([
                'class' => 'modern-payroll-section info'
            ])
            ->compact()
            ->icon('heroicon-o-information-circle')
            ->schema([

                \Filament\Infolists\Components\Grid::make(2)
                    ->schema([

                        \Filament\Infolists\Components\TextEntry::make('pegawai.nama')
                            ->label('Pegawai'),

                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge(),

                        \Filament\Infolists\Components\TextEntry::make('bulan')
                            ->label('Bulan')
                            ->formatStateUsing(fn ($state) => match ((int) $state) {
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                                default => '-',
                            }),

                        \Filament\Infolists\Components\TextEntry::make('tahun')
                            ->label('Tahun'),

                    ]),

            ])
            ->columnSpan(1),

        /*
        |--------------------------------------------------------------------------
        | TOTAL PAYROLL
        |--------------------------------------------------------------------------
        */

        \Filament\Infolists\Components\Section::make('Total Payroll')
            ->extraAttributes([
                'class' => 'modern-payroll-section total'
            ])
            ->compact()
            ->icon('heroicon-o-wallet')
            ->schema([

                \Filament\Infolists\Components\Grid::make(2)
                    ->schema([

                        \Filament\Infolists\Components\TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->formatStateUsing(fn ($state) =>
                                'Rp ' . number_format($state, 0, ',', '.')
                            ),

                        \Filament\Infolists\Components\TextEntry::make('bonus')
                            ->label('Tambahan')
                            ->color('success')
                            ->formatStateUsing(fn ($state) =>
                                'Rp ' . number_format($state, 0, ',', '.')
                            ),

                        \Filament\Infolists\Components\TextEntry::make('potongan')
                            ->label('Potongan')
                            ->color('danger')
                            ->formatStateUsing(fn ($state) =>
                                'Rp ' . number_format($state, 0, ',', '.')
                            ),

                        \Filament\Infolists\Components\TextEntry::make('total_gaji')
                            ->label('Total Gaji')
                            ->weight('bold')
                            ->color('primary')
                            ->formatStateUsing(fn ($state) =>
                                'Rp ' . number_format($state, 0, ',', '.')
                            ),

                    ]),

            ])
            ->columnSpan(1),

    ]),

            /*
            |--------------------------------------------------------------------------
            | RINCIAN GAJI
            |--------------------------------------------------------------------------
            */

            \Filament\Infolists\Components\Section::make('Rincian Gaji')
                ->extraAttributes([
                    'class' => 'modern-payroll-section rincian'
                ])
                ->compact()
                ->icon('heroicon-o-document-text')
                ->schema([

                    \Filament\Infolists\Components\ViewEntry::make('rincian_gaji')
                        ->view('filament.payroll.rincian-gaji'),

                ]),

            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT PAYROLL
            |--------------------------------------------------------------------------
            */

            \Filament\Infolists\Components\Section::make('Adjustment Payroll')
                ->extraAttributes([
                    'class' => 'modern-payroll-section adjustment'
                ])
                ->compact()
                ->icon('heroicon-o-arrow-path')
                ->schema([

                    \Filament\Infolists\Components\ViewEntry::make('adjustment_payroll')
                        ->view('filament.payroll.adjustment-payroll'),

                ]),

        ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AdjustmentsRelationManager::class,
        ];
    }
}
