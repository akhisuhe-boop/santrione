<?php

namespace App\Filament\Resources\SettingNominalTagihanResource\Pages;
namespace App\Filament\Resources\SettingNominalTagihanResource\Pages;

use App\Filament\Resources\SettingNominalTagihanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; // 🔥 INI WAJIB
use Filament\Actions;

class CreateSettingNominalTagihan extends CreateRecord
{
    protected static string $resource = SettingNominalTagihanResource::class;
    protected array $selectedSiswa = [];
    protected function mutateFormDataBeforeCreate(array $data): array
{
    $this->selectedSiswa = $data['siswa_ids'] ?? [];
    unset($data['siswa_ids']);

    return $data;
}

protected function handleRecordCreation(array $data): Model
{
    if (!empty($this->selectedSiswa)) {

        $lastRecord = null;

        foreach ($this->selectedSiswa as $siswaId) {
            $lastRecord = \App\Models\SettingNominalTagihan::create([
                ...$data,
                'siswa_id' => $siswaId,
            ]);
        }

        return $lastRecord;
    }

    return \App\Models\SettingNominalTagihan::create($data);
}
}
