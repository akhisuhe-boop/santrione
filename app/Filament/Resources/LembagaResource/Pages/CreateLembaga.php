<?php

namespace App\Filament\Resources\LembagaResource\Pages;

use App\Filament\Resources\LembagaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLembaga extends CreateRecord
{
    protected static string $resource = LembagaResource::class;

    /**
     * Setelah tambah Lembaga baru, langsung arahkan ke halaman
     * Langganan (BUKAN Edit Lembaga lagi -- pengelolaan modul sudah
     * pindah ke sana) supaya tenant langsung bisa pilih modul untuk
     * Lembaga barunya, tidak perlu cari-cari sendiri.
     */
    protected function getRedirectUrl(): string
    {
        return \App\Filament\Pages\Langganan::getUrl();
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Lembaga berhasil dibuat')
            ->body('Buka menu "Langganan" di sidebar untuk pilih modul yang mau dipakai Lembaga ini.')
            ->persistent();
    }
}
