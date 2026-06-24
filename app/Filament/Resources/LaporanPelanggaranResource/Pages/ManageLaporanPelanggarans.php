<?php

namespace App\Filament\Resources\LaporanPelanggaranResource\Pages;

use App\Filament\Resources\LaporanPelanggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLaporanPelanggarans extends ManageRecords
{
    protected static string $resource = LaporanPelanggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
