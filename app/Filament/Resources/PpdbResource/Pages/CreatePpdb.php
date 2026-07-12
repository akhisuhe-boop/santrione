<?php

namespace App\Filament\Resources\PpdbResource\Pages;

use App\Filament\Resources\PpdbResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreatePpdb extends CreateRecord
{
    protected static string $resource = PpdbResource::class;
    protected static ?string $title = 'Pendaftaran Siswa Baru';
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tahunId = \App\Models\TahunAjaran::aktif()?->id;
    
        if (! $tahunId) {
            throw new \Exception('Tahun ajaran aktif belum diset!');
        }
    
        $data['tahun_ajaran_id'] = $tahunId;
    
        // Password awal = NISN
        $data['password'] = Hash::make($data['nisn']);
    
        return $data;
    }
}
