<?php

namespace App\Filament\Platform\Resources\QinaraKasTransaksiResource\Pages;

use App\Filament\Platform\Resources\QinaraKasTransaksiResource;
use App\Models\QinaraKasTransaksi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQinaraKasTransaksis extends ListRecords
{
    protected static string $resource = QinaraKasTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * DITAMBAHKAN -- ringkasan saldo kas Qinara (semua waktu) di atas
     * tabel, biar langsung kelihatan tanpa perlu buka Laporan Laba
     * Rugi.
     */
    public function getSubheading(): ?string
    {
        $masuk = QinaraKasTransaksi::where('tipe', 'masuk')->sum('nominal');
        $keluar = QinaraKasTransaksi::where('tipe', 'keluar')->sum('nominal');
        $saldo = $masuk - $keluar;

        return 'Saldo kas saat ini: Rp '.number_format($saldo, 0, ',', '.')
            .' (Total masuk: Rp '.number_format($masuk, 0, ',', '.')
            .' — Total keluar: Rp '.number_format($keluar, 0, ',', '.').')';
    }
}
