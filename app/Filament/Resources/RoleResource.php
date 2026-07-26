<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Resources\RoleResource as BaseRoleResource;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends BaseRoleResource
{
    public static function getNavigationGroup(): ?string
    {
        return 'Master Setting';
    }

    /**
     * Peran (Role) sekarang campuran: sebagian GLOBAL (mis. "Admin
     * Yayasan", "super_admin" — yayasan_id NULL, dipakai bersama
     * semua tenant), sebagian CUSTOM milik 1 yayasan saja.
     *
     * Tenant biasa cuma boleh lihat: role global + role custom
     * miliknya sendiri (TIDAK lihat role custom yayasan lain).
     * Platform Admin tetap lihat semuanya (untuk keperluan support).
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->is_platform_admin) {
            return $query;
        }

        $tenant = \Filament\Facades\Filament::getTenant();

        return $query->where(function ($q) use ($tenant) {
            $q->whereNull('yayasan_id');

            if ($tenant) {
                $q->orWhere('yayasan_id', $tenant->id);
            }
        });
    }

    /**
     * Role GLOBAL (yayasan_id NULL, mis. "Admin Yayasan") cuma boleh
     * di-edit/dihapus oleh Platform Admin — supaya tenant biasa tidak
     * bisa ubah/hapus role yang dipakai bersama semua yayasan lain.
     */
    public static function canEdit($record): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return $record->yayasan_id !== null;
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    /**
     * Bulk "Delete selected" dihapus total — checkbox pilih-banyak itu
     * gampang kepencet tanpa sadar dan sebelumnya tetap kelihatan
     * aktif untuk role global (Admin Yayasan/Super Admin) walau
     * secara policy sebenarnya sudah ditolak. Daripada mengandalkan
     * itu, tombolnya langsung dihilangkan dari halaman ini.
     */
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return parent::table($table)
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        $pages = parent::getPages();

        $pages['create'] = \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create');

        return $pages;
    }
}
