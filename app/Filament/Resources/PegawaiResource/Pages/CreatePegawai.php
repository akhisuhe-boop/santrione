<?php

namespace App\Filament\Resources\PegawaiResource\Pages;

use App\Filament\Resources\PegawaiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePegawai extends CreateRecord
{
    protected static string $resource = PegawaiResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if (isset($data['pegawaiLembaga'])) {
            foreach ($data['pegawaiLembaga'] as $item) {
                DB::table('pegawai_lembaga')->insert([
                    'pegawai_id' => $this->record->id,
                    'lembaga_id' => $item['lembaga_id'],
                    'jabatan' => $item['jabatan'] ?? null,
                    'status' => $item['status'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}