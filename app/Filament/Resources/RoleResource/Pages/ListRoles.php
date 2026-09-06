<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * WAJIB dibuat sendiri -- Filament Shield bawaan (Pages\ListRoles milik
 * BezhanSalleh\FilamentShield\Resources\RoleResource\Pages) meng-hardcode
 * $resource ke class RoleResource BAWAAN SHIELD SENDIRI (bukan late-static-
 * bound ke resource app ini), sehingga getTableQuery() jalan lewat
 * getEloquentQuery() versi Shield yang TIDAK ter-scope tenant sama sekali.
 * Akibatnya halaman List menampilkan role custom SEMUA yayasan, walau badge
 * navigasi (yang benar lewat static::getEloquentQuery() versi app ini)
 * sudah menghitung dengan benar. Cukup override 'index' di getPages() milik
 * RoleResource app ini, sama seperti create/edit.
 */
class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
