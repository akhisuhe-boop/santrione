<?php

namespace App\Filament\Resources\KasResource\Pages;

use App\Filament\Resources\KasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKas extends CreateRecord
{
    protected static string $resource = KasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Sebelumnya kolom ini tidak pernah diisi sama sekali dari
        // form Input Kas manual -> selalu tampil "-" di Laporan Kas.
        $data['diinput_oleh'] = auth()->user()?->name;

        return $data;
    }
}
