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
 *
 * Langkah 3 (ditambahkan setelah kejadian "Laporan Tahfidz hilang" 6
 * Sep 2026): deteksi otomatis Resource-Resource yang SENGAJA/TIDAK
 * SENGAJA berbagi model Eloquent yang sama -- Laravel cuma bisa punya
 * 1 Policy per model, jadi kalau ada 2+ Resource pakai model sama,
 * Shield cuma bisa generate Policy yang benar untuk SALAH SATU-nya
 * (yang lain jadi salah sasaran permission, dan bisa hilang dari
 * sidebar tanpa pesan error apa pun). Ini murni PERINGATAN -- tidak
 * memperbaiki apa pun otomatis, karena perbaikannya (override
 * canViewAny() manual per Resource) butuh keputusan/permission mana
 * yang benar, tidak bisa ditebak sistem.
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
        } else {
            $sebelum = $role->permissions()->count();

            $role->syncPermissions(Permission::all());

            $sesudah = $role->permissions()->count();

            $this->info("Selesai -- role \"Admin Yayasan\" sekarang punya {$sesudah} permission (sebelumnya {$sebelum}).");
        }

        $this->newLine();
        $this->info('Langkah 3/3: Cek Resource yang berbagi model Eloquent yang sama...');

        $this->cekResourceBerbagiModel();

        return self::SUCCESS;
    }

    /**
     * Kelompokkan semua Resource terdaftar di panel "admin" menurut
     * getModel()-nya, lalu tandai grup yang isinya lebih dari 1 --
     * itu KANDIDAT rawan kena masalah "Policy salah sasaran" seperti
     * kasus LembagaResource/LaporanTahfidzResource dkk.
     */
    protected function cekResourceBerbagiModel(): void
    {
        $panel = \Filament\Facades\Filament::getPanel('admin');

        $perModel = [];

        foreach ($panel->getResources() as $resourceClass) {
            if (! class_exists($resourceClass)) {
                continue;
            }

            $model = $resourceClass::getModel();
            $perModel[$model][] = $resourceClass;
        }

        $bermasalah = array_filter($perModel, fn ($daftar) => count($daftar) > 1);

        if (empty($bermasalah)) {
            $this->info('Aman -- tidak ada Resource yang berbagi model Eloquent yang sama.');

            return;
        }

        $this->warn('PERHATIAN -- ditemukan Resource yang berbagi model Eloquent yang sama. Pastikan SEMUA Resource di bawah ini (kecuali yang memang jadi "pemilik utama" model itu) punya override canViewAny() eksplisit -- lihat contoh di LembagaResource/LaporanTahfidzResource untuk polanya:');
        $this->newLine();

        foreach ($bermasalah as $model => $daftarResource) {
            $this->line("  Model <fg=yellow>{$model}</> dipakai bersama oleh:");

            foreach ($daftarResource as $resourceClass) {
                $this->line('    - ' . class_basename($resourceClass));
            }

            $this->newLine();
        }
    }
}
