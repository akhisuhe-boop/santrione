<?php

namespace App\Filament\Resources\JurnalMengajarResource\Pages;

use App\Models\AbsensiMapel;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\JurnalMengajarResource;

class CreateJurnalMengajar extends CreateRecord
{
    protected static string $resource = JurnalMengajarResource::class;
    /*
    |--------------------------------------------------------------------------
    | VALIDASI & AUTO FILL
    |--------------------------------------------------------------------------
    */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // ambil jadwal
        $jadwal = JadwalPelajaran::find($data['jadwal_pelajaran_id']);
        if (!$jadwal) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jadwal_pelajaran_id' => 'Jadwal tidak ditemukan.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | AUTO FILL DARI JADWAL
        |--------------------------------------------------------------------------
        */
        $data['jam_ke'] = $jadwal->jam_ke;
        $data['durasi_jam'] = $jadwal->durasi_jam;
        $data['kelas_id'] = $jadwal->kelas_id;
        $data['mata_pelajaran_id'] = $jadwal->mata_pelajaran_id;
        /*
        |--------------------------------------------------------------------------
        | VALIDASI DUPLIKAT
        |--------------------------------------------------------------------------
        */
        $exists = JurnalMengajar::query()
            ->where('pegawai_id', $data['pegawai_id'])
            ->where('tanggal', $data['tanggal'])
            ->where('jadwal_pelajaran_id', $data['jadwal_pelajaran_id'])
            ->exists();
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jadwal_pelajaran_id' =>
                    'Guru sudah mengisi jurnal pada jadwal ini.',
            ]);
        }
        return $data;
    }

        /*
    |--------------------------------------------------------------------------
    | SAVE ABSENSI SISWA
    |--------------------------------------------------------------------------
    */

    protected function afterCreate(): void
    {
        // jika tidak ada absensi
        if (!isset($this->data['absensi_siswa'])) {
            return;
        }

        foreach ($this->data['absensi_siswa'] as $item) {
            AbsensiMapel::updateOrCreate([

                // pencarian unique
                'jadwal_pelajaran_id' => $this->record->jadwal_pelajaran_id,
                'siswa_id' => $item['siswa_id'],
                'tanggal' => $this->record->tanggal,
            ], [

                // data yg disimpan/update
                'jurnal_mengajar_id' => $this->record->id,
                'status' => $item['status'],
                'diabsen_oleh' => auth()->id(),
            ]);
        }
}
}