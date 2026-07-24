<?php

namespace App\Filament\Resources\WalletResource\Pages;

use App\Filament\Resources\WalletResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWallet extends EditRecord
{
    protected static string $resource = WalletResource::class;

    protected ?int $saldoBaru = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Tangkap nilai saldo yang diinput di form, TAPI jangan biarkan
        // Filament langsung menyimpannya — supaya perubahan saldo
        // SELALU lewat WalletService::applyAdjustment() yang atomic
        // (saldo + jejak pencatatan Kas/WalletTransaction jadi 1
        // transaksi database, tidak bisa nyangkut di tengah lagi
        // seperti kasus sebelumnya).
        if (array_key_exists('saldo', $data)) {
            $this->saldoBaru = (int) $data['saldo'];
            unset($data['saldo']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->saldoBaru === null) {
            return;
        }

        try {

            app(\App\Services\WalletService::class)
                ->applyAdjustment($this->record, $this->saldoBaru);

        } catch (\Throwable $e) {

            report($e);

            Notification::make()
                ->title('Gagal menyimpan penyesuaian saldo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
