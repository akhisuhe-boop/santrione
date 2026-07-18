<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Yayasan;

/**
 * Dipakai oleh controller publik (Wali, Guru, PPDB) yang berjalan
 * di luar konteks tenancy Filament. Yayasan aktif ditentukan dari
 * session, diisi lewat pintu masuk /y/{slug} (lihat routes/web.php).
 */
trait ResolvesPublicTenant
{
    protected function currentYayasanId(): ?int
    {
        return session('active_public_yayasan_id');
    }

    protected function currentYayasan(): ?Yayasan
    {
        $id = $this->currentYayasanId();

        return $id ? Yayasan::withoutGlobalScopes()->find($id) : null;
    }
}
