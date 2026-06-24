<?php

namespace App\Filament\Resources\PegawaiResource\Pages;

use App\Filament\Resources\PegawaiResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPegawai extends EditRecord
{
    protected static string $resource = PegawaiResource::class;

    // 🔥 TAMBAHKAN INI (WAJIB)
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['pegawaiLembaga'] = DB::table('pegawai_lembaga')
            ->where('pegawai_id', $data['id'])
            ->get()
            ->map(function ($item) {
                return [
                    'lembaga_id' => $item->lembaga_id,
                    'jabatan' => $item->jabatan,
                    'status' => $item->status,
                ];
            })
            ->toArray();

        return $data;
    }

    // 🔥 INI SUDAH BENAR (JANGAN DIUBAH)
    protected function afterSave(): void
    {
        $data = $this->form->getState();

        DB::table('pegawai_lembaga')
            ->where('pegawai_id', $this->record->id)
            ->delete();

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