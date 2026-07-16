<?php

namespace App\Filament\Resources\WalletResource\Pages;

use App\Filament\Resources\WalletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWallet extends EditRecord
{
    protected static string $resource = WalletResource::class;

    protected $oldSaldo;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldSaldo = $this->record->saldo;
        return $data;
    }

    protected function afterSave(): void
    {
        $diff = $this->record->saldo - $this->oldSaldo;

        if ($diff != 0) {
            app(\App\Services\WalletService::class)
                ->logAdjustment($this->record, $diff);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
