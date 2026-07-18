<?php

namespace App\Filament\Resources\WalletResource\Pages;

use App\Filament\Resources\WalletResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWallets extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
