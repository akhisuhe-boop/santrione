<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\YayasanOverviewResource\Pages;
use App\Models\Yayasan;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Daftar SEMUA Yayasan lintas-platform, read-only (create/edit/delete
 * Yayasan tetap lewat panel Yayasan/route pendaftaran yang sudah ada
 * — resource ini murni untuk MEMANTAU + pintu masuk cepat ke panel
 * Yayasan mana pun untuk keperluan debug/bantu maintenance data,
 * tanpa perlu hafal/tebak URL tenant satu-satu.
 */
class YayasanOverviewResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Yayasan::class;
    protected static ?string $navigationGroup = 'Yayasan';
    protected static ?string $navigationLabel = 'Daftar Yayasan';
    protected static ?string $modelLabel = 'Yayasan';
    protected static ?string $pluralModelLabel = 'Yayasan';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
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
            ->query(Yayasan::withoutGlobalScopes()->withCount(['lembagas']))
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->label('Yayasan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'trial' => 'info',
                        'suspended' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('paket')
                    ->label('Paket')
                    ->state(function (Yayasan $record) {
                        $subscription = $record->subscriptions()
                            ->latest('berakhir_pada')
                            ->first();

                        return $subscription?->plan?->nama ?? '—';
                    }),

                Tables\Columns\TextColumn::make('lembagas_count')
                    ->label('Jml. Lembaga')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial Berakhir')
                    ->date('d M Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('langganan_berakhir')
                    ->label('Langganan Berakhir')
                    ->state(function (Yayasan $record) {
                        $subscription = $record->subscriptions()
                            ->latest('berakhir_pada')
                            ->first();

                        return $subscription?->berakhir_pada;
                    })
                    ->date('d M Y')
                    ->placeholder('—')
                    ->badge()
                    ->color(function (Yayasan $record) {
                        $subscription = $record->subscriptions()
                            ->latest('berakhir_pada')
                            ->first();

                        if (! $subscription?->berakhir_pada) {
                            return 'gray';
                        }

                        if ($subscription->berakhir_pada->isPast()) {
                            return 'danger';
                        }

                        if ($subscription->berakhir_pada->diffInDays(now()) <= 30) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            \App\Models\Subscription::select('berakhir_pada')
                                ->whereColumn('yayasan_id', 'yayasans.id')
                                ->latest('berakhir_pada')
                                ->limit(1),
                            $direction
                        );
                    }),

                Tables\Columns\TextColumn::make('estimasi_tagihan')
                    ->label('Tagihan')
                    ->state(function (Yayasan $record) {
                        $hasil = app(\App\Services\TenantBillingCalculator::class)->hitungYayasan($record);

                        return 'Rp ' . number_format($hasil['total'], 0, ',', '.');
                    }),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('lihatTagihanPending')
                    ->label('Link Tagihan')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->visible(function (Yayasan $record) {
                        return $record->subscriptions()
                            ->where('status', 'pending')
                            ->whereHas('payments', fn ($q) => $q->where('status', 'pending')->whereNotNull('gateway_order_id'))
                            ->exists();
                    })
                    ->modalHeading('Link Pembayaran Pending')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Yayasan $record) {
                        $payment = $record->subscriptions()
                            ->where('status', 'pending')
                            ->with('payments')
                            ->latest()
                            ->first()
                            ?->payments()
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

                        $url = $payment?->gateway_raw_response['paymentUrl'] ?? null;

                        return view('filament.platform.link-tagihan', ['url' => $url]);
                    }),

                Tables\Actions\Action::make('masukSebagaiYayasan')
                    ->label('Login')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->url(fn (Yayasan $record) => route('platform.impersonate.request', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYayasanOverviews::route('/'),
        ];
    }
}
