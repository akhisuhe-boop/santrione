<?php

namespace App\Filament\Resources\PengaturanHonorPenggantiResource\Pages;

use App\Filament\Resources\PengaturanHonorPenggantiResource;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanHonorPenggantis extends ListRecords
{
    protected static string $resource = PengaturanHonorPenggantiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
