<?php

namespace App\Filament\Resources\WithdrawRequestResource\Pages;

use App\Filament\Resources\WithdrawRequestResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawRequests extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = WithdrawRequestResource::class;

    // Relasi ke Siswa lewat wallet dulu (nested)
    protected ?string $alumniRelation = 'wallet.siswa';

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
