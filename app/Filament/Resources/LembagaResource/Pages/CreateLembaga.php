<?php

namespace App\Filament\Resources\LembagaResource\Pages;

use App\Filament\Resources\LembagaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLembaga extends CreateRecord
{
    protected static string $resource = LembagaResource::class;

    /**
     * Setelah tambah Lembaga baru, langsung arahkan ke halaman Edit
     * (bukan balik ke List) -- supaya tab "Modul Aktif" langsung
     * kelihatan, mendorong Yayasan langsung pilih modul untuk
     * Lembaga barunya, bukan harus cari-cari sendiri.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Lembaga berhasil dibuat')
            ->body('Lanjutkan ke tab "Modul Aktif" di bawah untuk pilih modul yang mau dipakai Lembaga ini.')
            ->persistent();
    }
}
