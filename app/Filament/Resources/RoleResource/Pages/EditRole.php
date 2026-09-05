<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Sebelumnya halaman ini pakai bawaan Filament Shield (belum pernah
 * dibuat versi custom-nya) -- sekarang WAJIB dibuat sendiri supaya
 * konsisten dengan form toggle-per-menu yang baru (lihat
 * RoleResource::form() & CreateRole untuk penjelasan lengkap kenapa
 * penyimpanan permission dikerjakan manual lewat Spatie langsung,
 * bukan lewat logic internal Shield).
 */
class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $toggleStateSementara = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->toggleStateSementara = $data['menu_toggle'] ?? [];
        unset($data['menu_toggle']);

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->update($data);

        $permissions = RoleResource::extractPermissionsFromToggleState($this->toggleStateSementara);

        $record->syncPermissions($permissions);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->visible(fn ($record) => RoleResource::canDelete($record)),
        ];
    }
}
