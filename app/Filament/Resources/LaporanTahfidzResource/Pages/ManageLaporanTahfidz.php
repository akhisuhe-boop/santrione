<?php

namespace App\Filament\Resources\LaporanTahfidzResource\Pages;

use App\Filament\Resources\LaporanTahfidzResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLaporanTahfidz extends ManageRecords
{
    protected static string $resource = LaporanTahfidzResource::class;
    protected static ?string $title = 'Laporan Tahfidz';
}