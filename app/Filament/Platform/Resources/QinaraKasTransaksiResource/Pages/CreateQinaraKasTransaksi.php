<?php

namespace App\Filament\Platform\Resources\QinaraKasTransaksiResource\Pages;

use App\Filament\Platform\Resources\QinaraKasTransaksiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQinaraKasTransaksi extends CreateRecord
{
    protected static string $resource = QinaraKasTransaksiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['diinput_oleh_id'] = auth()->id();

        return $data;
    }
}
