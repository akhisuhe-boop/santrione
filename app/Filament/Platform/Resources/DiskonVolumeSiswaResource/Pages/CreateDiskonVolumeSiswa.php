<?php

namespace App\Filament\Platform\Resources\DiskonVolumeSiswaResource\Pages;

use App\Filament\Platform\Resources\DiskonVolumeSiswaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDiskonVolumeSiswa extends CreateRecord
{
    protected static string $resource = DiskonVolumeSiswaResource::class;

    /**
     * DITAMBAHKAN -- cegah tier baru tumpang tindih dengan tier lain
     * (lihat DiskonVolumeSiswaResource::validasiTidakBentrok()).
     */
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $pesan = DiskonVolumeSiswaResource::validasiTidakBentrok(
            idSaatIni: null,
            min: (int) $data['siswa_min'],
            max: $data['siswa_max'] !== null ? (int) $data['siswa_max'] : null,
        );

        if ($pesan) {
            Notification::make()
                ->title('Rentang siswa tumpang tindih')
                ->body($pesan)
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
