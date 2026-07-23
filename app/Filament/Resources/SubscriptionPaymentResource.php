<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPaymentResource\Pages;
use App\Models\SubscriptionPayment;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPaymentResource extends BaseResource
{
    protected static ?string $model = SubscriptionPayment::class;
    protected static ?string $navigationGroup = 'Platform (SaaS)';
    protected static ?string $navigationLabel = 'Verifikasi Pembayaran';
    protected static ?string $modelLabel = 'Pembayaran Langganan';
    protected static ?string $pluralModelLabel = 'Pembayaran Langganan';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationBadgeColor = 'warning';

    // Lintas semua yayasan — cuma Platform Admin.
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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('metode', 'manual_transfer')
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getModel()::query()->with(['subscription.yayasan', 'subscription.plan'])
            )
            ->columns([

                Tables\Columns\TextColumn::make('subscription.yayasan.nama')
                    ->label('Yayasan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subscription.plan.nama')
                    ->label('Paket'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\BadgeColumn::make('metode')
                    ->colors([
                        'info' => 'duitku',
                        'primary' => 'midtrans',
                        'gray' => 'manual_transfer',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'berhasil',
                        'danger' => 'gagal',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'berhasil' => 'Berhasil', 'gagal' => 'Gagal']),
                Tables\Filters\SelectFilter::make('metode')
                    ->options(['midtrans' => 'Midtrans', 'manual_transfer' => 'Transfer Manual']),
            ])
            ->actions([

                Tables\Actions\Action::make('lihat_bukti')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->url(fn (SubscriptionPayment $record) => $record->bukti_transfer ? asset('storage/' . $record->bukti_transfer) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (SubscriptionPayment $record) => filled($record->bukti_transfer)),

                Tables\Actions\Action::make('verifikasi')
                    ->label('Verifikasi & Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Langganan yayasan ini akan langsung AKTIF 1 bulan ke depan sejak sekarang.')
                    ->visible(fn (SubscriptionPayment $record) => $record->status === 'pending')
                    ->action(function (SubscriptionPayment $record) {

                        $record->update([
                            'status' => 'berhasil',
                            'diverifikasi_oleh' => auth()->id(),
                            'diverifikasi_pada' => now(),
                        ]);

                        $subscription = $record->subscription;

                        $subscription->update([
                            'status' => 'active',
                            'mulai_pada' => now(),
                            'berakhir_pada' => now()->addMonth(),
                        ]);

                        $subscription->yayasan->update(['status' => 'active']);

                        Notification::make()
                            ->title('Langganan diaktifkan')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SubscriptionPayment $record) => $record->status === 'pending')
                    ->action(function (SubscriptionPayment $record) {

                        $record->update([
                            'status' => 'gagal',
                            'diverifikasi_oleh' => auth()->id(),
                            'diverifikasi_pada' => now(),
                        ]);

                        Notification::make()
                            ->title('Pembayaran ditolak')
                            ->warning()
                            ->send();
                    }),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPayments::route('/'),
        ];
    }
}
