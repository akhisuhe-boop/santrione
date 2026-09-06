<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Command GABUNGAN -- dibuat setelah kejadian 6 Sep 2026: fitur baru
 * (KantinResource dkk) sempat "hilang" dari sidebar tenant lama
 * karena langkah "shield:generate" dan "sync role Admin Yayasan"
 * adalah 2 command TERPISAH, dan langkah keduanya lupa dijalankan
 * (mudah kelewat kalau lagi baca output shield:generate yang panjang).
 *
 * Mulai sekarang, PAKAI COMMAND INI setiap kali ada Resource/Page/
 * Widget baru ditambahkan -- BUKAN lagi "shield:generate" polos --
 * supaya sync role tidak mungkin lagi kelewat karena sudah jadi
 * 1 paket, bukan 2 langkah manual terpisah.
 */
class GenerateShieldDanSyncRole extends Command
{
    protected $signature = 'app:shield-generate-dan-sync';

    protected $description = 'Generate permission Shield untuk panel admin + langsung sync ke role Admin Yayasan dalam 1 langkah (pengganti shield:generate polos)';

    public function handle(): int
    {
        $this->info('Langkah 1/2: Generate permission & policy lewat Filament Shield...');
        $this->newLine();

        $exitCode = $this->call('shield:generate', [
            '--panel' => 'admin',
            '--all' => true,
        ]);

        if ($exitCode !== 0) {
            $this->error('shield:generate gagal -- sync role DIBATALKAN. Cek pesan error di atas.');

            return $exitCode;
        }

        $this->newLine();
        $this->info('Langkah 2/2: Sync semua permission terkini ke role "Admin Yayasan"...');

        $role = Role::where('name', 'Admin Yayasan')
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            $this->warn('Role "Admin Yayasan" tidak ditemukan -- lewati langkah sync (mungkin belum ada yayasan yang pernah daftar).');

            return self::SUCCESS;
        }

        $sebelum = $role->permissions()->count();

        $role->syncPermissions(Permission::all());

        $sesudah = $role->permissions()->count();

        $this->info("Selesai -- role \"Admin Yayasan\" sekarang punya {$sesudah} permission (sebelumnya {$sebelum}).");

        return self::SUCCESS;
    }
}
