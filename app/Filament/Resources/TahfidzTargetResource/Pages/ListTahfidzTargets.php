<?php

namespace App\Filament\Resources\TahfidzTargetResource\Pages;

use App\Filament\Resources\TahfidzTargetResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTahfidzTargets extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = TahfidzTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
