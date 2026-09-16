<?php

namespace App\Filament\Resources\JurnalMengajarResource\Pages;

use App\Models\AbsensiMapel;
use App\Filament\Resources\JurnalMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJurnalMengajar extends EditRecord
{
    protected static string $resource = JurnalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * DITAMBAHKAN (16 Sep 2026) -- 'absensi_siswa' BUKAN kolom asli di
     * tabel jurnal_mengajars (cuma field bantu di form, datanya
     * sebenarnya ada di tabel AbsensiMapel terpisah). Tanpa ini, form
     * Edit SELALU reset ke "Hadir" semua siswa (lihat ->default() di
     * JurnalMengajarResource::form(), yang generate ulang dari kelas
     * tanpa tahu status yang sudah tersimpan) -- jadi guru buka jurnal
     * yang sudah ada, status Izin/Sakit/Alpha yang sudah pernah
     * disimpan malah kelihatan "Hadir" lagi. Sekarang dimuat dari data
     * AbsensiMapel yang sungguhan.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['absensi_siswa'] = \App\Models\AbsensiMapel::where('jurnal_mengajar_id', $this->record->id)
            ->get()
            ->map(fn ($item) => [
                'siswa_id' => $item->siswa_id,
                'status' => $item->status,
            ])
            ->toArray();

        return $data;
    }

    /**
     * DITAMBAHKAN (16 Sep 2026) -- sebelumnya halaman Edit ini SAMA
     * SEKALI tidak punya logic buat simpan perubahan status siswa
     * (Hadir/Izin/Sakit/Alpha) ke tabel AbsensiMapel -- beda dari
     * CreateJurnalMengajar.php yang punya afterCreate() buat itu.
     * Akibatnya: guru buka jurnal yang sudah ada, ubah status
     * beberapa siswa, klik Save -- FORM-nya kelihatan berhasil, tapi
     * perubahan absensinya TIDAK PERNAH benar-benar tersimpan ke
     * database, diam-diam terbuang. Sekarang disamakan persis dengan
     * logic afterCreate().
     */
    protected function afterSave(): void
    {
        if (! isset($this->data['absensi_siswa'])) {
            return;
        }

        foreach ($this->data['absensi_siswa'] as $item) {
            AbsensiMapel::updateOrCreate([
                'jadwal_pelajaran_id' => $this->record->jadwal_pelajaran_id,
                'siswa_id' => $item['siswa_id'],
                'tanggal' => $this->record->tanggal,
            ], [
                'jurnal_mengajar_id' => $this->record->id,
                'status' => $item['status'],
                'diabsen_oleh' => auth()->id(),
            ]);
        }
    }
}
