<?php

namespace App\Filament\Resources\TahfidzSetoranResource\Pages;

use App\Filament\Resources\TahfidzSetoranResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTahfidzSetorans extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = TahfidzSetoranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
