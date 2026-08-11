<?php

namespace App\Filament\Resources\LembagaResource\Pages;

use App\Filament\Resources\LembagaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLembaga extends EditRecord
{
    protected static string $resource = LembagaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('daftarkanXendit')
                ->label(fn () => $this->record->xendit_account_holder_id ? 'Terdaftar di Xendit' : 'Daftarkan ke Xendit')
                ->icon('heroicon-o-building-library')
                ->color(fn () => $this->record->xendit_account_holder_id ? 'success' : 'primary')
                ->disabled(fn () => (bool) $this->record->xendit_account_holder_id)
                ->visible(fn () => (bool) auth()->user()?->is_platform_admin)
                ->requiresConfirmation()
                ->modalDescription('Lembaga ini akan didaftarkan sebagai sub-account Xendit untuk menerima split payment dari pembayaran wali murid. Pastikan email Lembaga sudah terisi dengan benar.')
                ->action(function () {
                    try {
                        app(\App\Services\XenditService::class)->daftarkanSubAccount($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil didaftarkan ke Xendit')
                            ->body('Status: menunggu verifikasi Xendit.')
                            ->success()
                            ->send();

                        $this->record->refresh();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal mendaftarkan ke Xendit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
