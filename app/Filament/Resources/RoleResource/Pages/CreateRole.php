<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Resources\RoleResource\Pages\CreateRole as BaseCreateRole;

class CreateRole extends BaseCreateRole
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // PENTING: panggil dulu logic asli Shield (bersihkan field
        // semacam "select_all"/checkbox permission yang bukan kolom
        // tabel roles) sebelum nambahin yayasan_id sendiri. Kalau
        // tidak, data mentah ke-kirim ke database dan bikin 500.
        $data = parent::mutateFormDataBeforeCreate($data);

        // Role baru yang dibuat dari dalam konteks 1 yayasan otomatis
        // ditandai milik yayasan itu saja (tidak ikut nongol/kepakai
        // di yayasan lain). Platform Admin yang bikin role TANPA
        // sedang berada di tenant manapun -> tetap jadi role global
        // (yayasan_id null), sama seperti "Admin Yayasan"/"super_admin".
        $tenant = \Filament\Facades\Filament::getTenant();

        if ($tenant && ! auth()->user()?->is_platform_admin) {
            $data['yayasan_id'] = $tenant->id;
        }

        return $data;
    }
}
