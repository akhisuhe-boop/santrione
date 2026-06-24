<?php

namespace App\Filament\Resources\PerizinanResource\Pages;

use App\Filament\Resources\PerizinanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerizinan extends EditRecord
{
    protected static string $resource = PerizinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
{
    // 🔥 WAJIB: set jam 17:00
    if (!empty($data['tanggal_selesai'])) {
        $data['tanggal_selesai'] = \Carbon\Carbon::parse($data['tanggal_selesai'])
            ->setTime(17, 0, 0);
    }

    // 🔥 hitung keterlambatan
    if (!empty($data['waktu_kembali']) && !empty($data['tanggal_selesai'])) {
        $jadwal = \Carbon\Carbon::parse($data['tanggal_selesai']);
        $kembali = \Carbon\Carbon::parse($data['waktu_kembali']);

        if ($kembali->lte($jadwal)) {
            $data['keterangan_waktu'] = 'tepat_waktu';
        } elseif ($kembali->diffInHours($jadwal) <= 3) {
            $data['keterangan_waktu'] = 'terlambat';
        } else {
            $data['keterangan_waktu'] = 'sangat_terlambat';
        }

        $data['status'] = 'selesai';
    }

    return $data;
}
}
