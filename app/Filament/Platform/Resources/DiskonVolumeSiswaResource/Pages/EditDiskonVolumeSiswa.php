<?php

namespace App\Filament\Platform\Resources\DiskonVolumeSiswaResource\Pages;

use App\Filament\Platform\Resources\DiskonVolumeSiswaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDiskonVolumeSiswa extends EditRecord
{
    protected static string $resource = DiskonVolumeSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * DITAMBAHKAN -- cegah hasil edit tumpang tindih dengan tier lain
     * (lihat DiskonVolumeSiswaResource::validasiTidakBentrok()).
     */
    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $pesan = DiskonVolumeSiswaResource::validasiTidakBentrok(
            idSaatIni: $this->record->id,
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
