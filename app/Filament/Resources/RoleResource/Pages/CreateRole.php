<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * DITULIS ULANG TOTAL (sebelumnya extends CreateRole bawaan Filament
 * Shield) -- sekarang tidak lagi bergantung ke cara Shield membaca
 * form checkbox granular-nya sendiri, karena form()-nya sudah diganti
 * total jadi toggle per-menu (lihat RoleResource::form()). Penyimpanan
 * permission dikerjakan sendiri lewat Spatie langsung (syncPermissions)
 * -- lebih pasti benar daripada menebak struktur data yang diharapkan
 * kode internal Shield.
 */
class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $toggleStateSementara = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan dulu isi toggle-nya sebelum dibuang -- "menu_toggle"
        // bukan kolom tabel roles, kalau tidak dibuang akan error waktu
        // Role::create() jalan.
        $this->toggleStateSementara = $data['menu_toggle'] ?? [];
        unset($data['menu_toggle']);

        $tenant = \Filament\Facades\Filament::getTenant();

        if ($tenant && ! auth()->user()?->is_platform_admin) {
            $data['yayasan_id'] = $tenant->id;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        /** @var \Spatie\Permission\Models\Role $role */
        $role = static::getModel()::create($data);

        $permissions = RoleResource::extractPermissionsFromToggleState($this->toggleStateSementara);

        $role->syncPermissions($permissions);

        return $role;
    }
}
