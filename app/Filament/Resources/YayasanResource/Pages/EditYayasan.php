<?php

namespace App\Filament\Resources\YayasanResource\Pages;

use App\Filament\Resources\YayasanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYayasan extends EditRecord
{
    protected static string $resource = YayasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading('Hapus Yayasan Permanen?')
                ->modalDescription('SEMUA data yayasan ini akan terhapus PERMANEN dan TIDAK BISA dikembalikan: lembaga, kelas, siswa, pegawai, akun admin, kas, tagihan, jadwal, jurnal, payroll, riwayat langganan — semuanya. Kalau cuma mau menonaktifkan sementara (bisa diaktifkan lagi nanti), gunakan status "Suspended" lewat menu Verifikasi Pembayaran / edit langsung, JANGAN hapus.')
                ->modalSubmitActionLabel('Ya, Hapus Permanen')
                ->requiresConfirmation(),
        ];
    }
}
