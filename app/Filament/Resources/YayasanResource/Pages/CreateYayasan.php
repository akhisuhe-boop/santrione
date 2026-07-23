<?php

namespace App\Filament\Resources\YayasanResource\Pages;

use App\Filament\Resources\YayasanResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateYayasan extends CreateRecord
{
    protected static string $resource = YayasanResource::class;

    /**
     * Ditangkap sementara di sini (bukan disimpan ke tabel yayasans),
     * dipakai lagi di afterCreate() untuk bikin akun admin awal.
     */
    protected ?string $pendingAdminNama = null;
    protected ?string $pendingAdminEmail = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingAdminNama = $data['admin_nama'] ?? null;
        $this->pendingAdminEmail = $data['admin_email'] ?? null;

        // 2 field ini cuma dipakai buat bikin akun admin, bukan kolom
        // di tabel yayasans — jangan sampai ikut ke-simpan/error.
        unset($data['admin_nama'], $data['admin_email']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (blank($this->pendingAdminEmail)) {
            return;
        }

        $yayasan = $this->record;

        $password = Str::password(12);

        $admin = User::create([
            'name' => $this->pendingAdminNama ?: ('Admin ' . $yayasan->nama),
            'email' => $this->pendingAdminEmail,
            'password' => $password, // model cast 'hashed' otomatis hash ini
            'yayasan_id' => $yayasan->id,
        ]);

        // Role "Admin Sekolah" dipakai ulang lintas yayasan (bukan
        // dibuat per-yayasan). Kalau belum pernah ada sama sekali,
        // buat sekali + sync semua permission yang sudah di-generate
        // Shield. Kalau sudah ada, JANGAN di-sync ulang di sini —
        // supaya penyesuaian permission yang mungkin sudah dilakukan
        // manual sebelumnya untuk role ini tidak ke-reset tiap kali
        // ada yayasan baru dibuat.
        $role = Role::firstOrCreate([
            'name' => 'Admin Sekolah',
            'guard_name' => 'web',
        ]);

        if ($role->wasRecentlyCreated) {
            $role->syncPermissions(Permission::all());
        }

        $admin->assignRole($role);

        if (Permission::count() === 0) {

            Notification::make()
                ->title('Yayasan & akun admin dibuat, tapi permission belum pernah di-generate')
                ->body("Login: {$this->pendingAdminEmail} / {$password}\n\nJalankan \"php artisan shield:generate --all --panel=admin\" dulu di server, lalu buka menu Pengguna > edit akun ini > pilih ulang role \"Admin Sekolah\" supaya permission-nya benar-benar aktif.")
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Akun admin untuk ' . $yayasan->nama . ' berhasil dibuat')
            ->body("Email: {$this->pendingAdminEmail}\nPassword: {$password}\n\nCatat sekarang — password ini tidak akan ditampilkan lagi setelah ini.")
            ->success()
            ->persistent()
            ->send();
    }
}
