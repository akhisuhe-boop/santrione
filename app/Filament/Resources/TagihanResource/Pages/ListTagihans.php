<?php

namespace App\Filament\Resources\TagihanResource\Pages;

use App\Filament\Resources\TagihanResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTagihans extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = TagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
        ];
    }
}
