<?php

namespace App\Filament\Resources\PengaturanHonorPenggantiResource\Pages;

use App\Filament\Resources\PengaturanHonorPenggantiResource;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanHonorPengganti extends EditRecord
{
    protected static string $resource = PengaturanHonorPenggantiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
