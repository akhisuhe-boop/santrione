<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

class MigrateAlMubarokData extends Command
{
    protected $signature = 'migrate:almubarok {--dry-run}';
    protected $description = 'Migrate real Al-Mubarok data from staging_almubarok into production (yayasan_id=3)';

    protected int $yayasanId = 3;
    protected array $map = [
        'lembaga' => [], 'kategori_kas' => [], 'jenis_tagihan' => [],
        'rekening' => [], 'template_kegiatan' => [], 'whatsapp_setting' => [],
        'jadwal_kegiatan' => [], 'pegawai' => [], 'user' => [],
        'kelas' => [], 'siswa' => [], 'siswa_old_lembaga' => [],
        'tahun_ajaran' => [], 'tagihan' => [], 'pembayaran' => [],
    ];

    protected function src()
    {
        return DB::connection('staging_almubarok');
    }

    protected function copyRow($row, array $overrides): array
    {
        $data = (array) $row;
        unset($data['id']);
        foreach ($overrides as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }

    public function handle()
    {
        $dry = $this->option('dry-run');
        $this->info($dry ? '=== DRY RUN (no data will be saved) ===' : '=== LIVE RUN ===');

        DB::beginTransaction();
        try {
            $this->migrateLembagas();
            $this->migrateTahunAjarans();
            $this->migrateKategoriKas();
            $this->migrateJenisTagihans();
            $this->migrateRekenings();
            $this->migrateTemplateKegiatan();
            $this->migrateWhatsappSettings();
            $this->migrateJadwalKegiatan();
            $this->migratePegawais();
            $this->migratePegawaiLembaga();
            $this->migrateUsers();
            $this->patchLembagaBendahara();
            $this->migrateKelas();
            $this->migrateSiswas();
            $this->migrateWallets();
            $this->migrateTagihans();
            $this->migratePembayarans();
            $this->migrateKas();
            $this->migrateAbsensi();
            $this->migrateAnnouncements();

            if ($dry) {
                DB::rollBack();
                $this->info('DRY RUN selesai, semua di-rollback. Tidak ada data tersimpan.');
            } else {
                DB::commit();
                $this->info('MIGRASI SELESAI & DI-COMMIT.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('GAGAL, semua di-rollback: ' . $e->getMessage());
            $this->error($e->getFile() . ':' . $e->getLine());
            return 1;
        }
        return 0;
    }

    protected function migrateLembagas()
    {
        $rows = $this->src()->table('lembagas')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, [
                'yayasan_id' => $this->yayasanId,
                'bendahara_id' => null,
            ]);
            $newId = DB::table('lembagas')->insertGetId($data);
            $this->map['lembaga'][$row->id] = $newId;
        }
        $this->info('lembagas: ' . count($this->map['lembaga']) . ' baris');
    }

    protected function migrateTahunAjarans()
    {
        $rows = $this->src()->table('tahun_ajarans')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, ['yayasan_id' => $this->yayasanId]);
            $newId = DB::table('tahun_ajarans')->insertGetId($data);
            $this->map['tahun_ajaran'][$row->id] = $newId;
        }
        $this->info('tahun_ajarans: ' . count($this->map['tahun_ajaran']) . ' baris');
    }

    protected function migrateKategoriKas()
    {
        $rows = $this->src()->table('kategori_kas')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, ['yayasan_id' => $this->yayasanId]);
            $newId = DB::table('kategori_kas')->insertGetId($data);
            $this->map['kategori_kas'][$row->id] = $newId;
        }
        $this->info('kategori_kas: ' . count($this->map['kategori_kas']) . ' baris');
    }

    protected function migrateJenisTagihans()
    {
        $rows = $this->src()->table('jenis_tagihans')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, [
                'yayasan_id' => $this->yayasanId,
                'kategori_kas_id' => $row->kategori_kas_id
                    ? ($this->map['kategori_kas'][$row->kategori_kas_id] ?? null)
                    : null,
            ]);
            $newId = DB::table('jenis_tagihans')->insertGetId($data);
            $this->map['jenis_tagihan'][$row->id] = $newId;
        }
        $this->info('jenis_tagihans: ' . count($this->map['jenis_tagihan']) . ' baris');
    }

    protected function duplicatePerLembaga(string $table, string $mapKey)
    {
        $rows = $this->src()->table($table)->get();
        $count = 0;
        foreach ($rows as $row) {
            foreach ($this->map['lembaga'] as $oldLembagaId => $newLembagaId) {
                $data = $this->copyRow($row, ['lembaga_id' => $newLembagaId]);
                $newId = DB::table($table)->insertGetId($data);
                $this->map[$mapKey][$row->id][$newLembagaId] = $newId;
                $count++;
            }
        }
        $this->info("$table: $count baris (digandakan ke " . count($this->map['lembaga']) . " lembaga)");
    }

    protected function migrateRekenings() { $this->duplicatePerLembaga('rekenings', 'rekening'); }
    protected function migrateTemplateKegiatan() { $this->duplicatePerLembaga('template_kegiatan', 'template_kegiatan'); }
    protected function migrateWhatsappSettings() { $this->duplicatePerLembaga('whatsapp_settings', 'whatsapp_setting'); }

    protected function migrateJadwalKegiatan()
    {
        $rows = $this->src()->table('jadwal_kegiatan')->get();
        foreach ($rows as $row) {
            $tplCopies = $this->map['template_kegiatan'][$row->template_kegiatan_id] ?? [];
            $newTplId = $tplCopies ? array_values($tplCopies)[0] : null;
            $data = $this->copyRow($row, ['template_kegiatan_id' => $newTplId]);
            $newId = DB::table('jadwal_kegiatan')->insertGetId($data);
            $this->map['jadwal_kegiatan'][$row->id] = $newId;
        }
        $this->info('jadwal_kegiatan: ' . count($this->map['jadwal_kegiatan']) . ' baris');
    }

    protected function migratePegawais()
    {
        $rows = $this->src()->table('pegawais')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, []);
            $newId = DB::table('pegawais')->insertGetId($data);
            $this->map['pegawai'][$row->id] = $newId;
        }
        $this->info('pegawais: ' . count($this->map['pegawai']) . ' baris');
    }

    protected function migratePegawaiLembaga()
    {
        $rows = $this->src()->table('pegawai_lembaga')->get();
        $count = 0;
        foreach ($rows as $row) {
            if (!isset($this->map['pegawai'][$row->pegawai_id], $this->map['lembaga'][$row->lembaga_id])) continue;
            $data = $this->copyRow($row, [
                'pegawai_id' => $this->map['pegawai'][$row->pegawai_id],
                'lembaga_id' => $this->map['lembaga'][$row->lembaga_id],
            ]);
            DB::table('pegawai_lembaga')->insert($data);
            $count++;
        }
        $this->info("pegawai_lembaga: $count baris");
    }

    protected function migrateUsers()
    {
        $rows = $this->src()->table('users')->get();
        $rolesOld = $this->src()->table('roles')->pluck('name', 'id');
        $userRoles = $this->src()->table('model_has_roles')->get()->keyBy('model_id');

        $existingEmails = DB::table('users')->pluck('email')->flip();

        foreach ($rows as $row) {
            if ($existingEmails->has($row->email)) {
                $this->warn("users: email {$row->email} sudah ada di production, skip.");
                continue;
            }
            $data = $this->copyRow($row, [
                'yayasan_id' => $this->yayasanId,
                'is_platform_admin' => 0,
            ]);
            $newId = DB::table('users')->insertGetId($data);
            $this->map['user'][$row->id] = $newId;

            if (isset($userRoles[$row->id])) {
                $roleName = $rolesOld[$userRoles[$row->id]->role_id] ?? null;
                if ($roleName) {
                    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                    $user = User::find($newId);
                    $user->assignRole($role);
                }
            }
        }
        $this->info('users: ' . count($this->map['user']) . ' baris');
    }

    protected function patchLembagaBendahara()
    {
        $rows = $this->src()->table('lembagas')->get();
        foreach ($rows as $row) {
            if (!$row->bendahara_id) continue;
            if (!isset($this->map['lembaga'][$row->id], $this->map['pegawai'][$row->bendahara_id])) continue;
            DB::table('lembagas')
                ->where('id', $this->map['lembaga'][$row->id])
                ->update(['bendahara_id' => $this->map['pegawai'][$row->bendahara_id]]);
        }
        $this->info('lembagas.bendahara_id: di-patch');
    }

    protected function migrateKelas()
    {
        $rows = $this->src()->table('kelas')->get();
        foreach ($rows as $row) {
            $data = $this->copyRow($row, [
                'lembaga_id' => $this->map['lembaga'][$row->lembaga_id] ?? null,
                'wali_kelas_id' => $row->wali_kelas_id
                    ? ($this->map['pegawai'][$row->wali_kelas_id] ?? null)
                    : null,
            ]);
            $newId = DB::table('kelas')->insertGetId($data);
            $this->map['kelas'][$row->id] = $newId;
        }
        $this->info('kelas: ' . count($this->map['kelas']) . ' baris');
    }

    protected function migrateSiswas()
    {
        $rows = $this->src()->table('siswas')->get();
        foreach ($rows as $row) {
            $this->map['siswa_old_lembaga'][$row->id] = $row->lembaga_id;
            $data = $this->copyRow($row, [
                'lembaga_id' => $this->map['lembaga'][$row->lembaga_id] ?? null,
                'kelas_id' => $this->map['kelas'][$row->kelas_id] ?? null,
                'asrama_id' => null,
                'kartu_template_id' => null,
            ]);
            $newId = DB::table('siswas')->insertGetId($data);
            $this->map['siswa'][$row->id] = $newId;
        }
        $this->info('siswas: ' . count($this->map['siswa']) . ' baris');
    }

    protected function migrateWallets()
    {
        $rows = $this->src()->table('wallets')->get();
        $count = 0;
        foreach ($rows as $row) {
            if (!isset($this->map['siswa'][$row->siswa_id])) continue;
            $data = $this->copyRow($row, ['siswa_id' => $this->map['siswa'][$row->siswa_id]]);
            DB::table('wallets')->insert($data);
            $count++;
        }
        $this->info("wallets: $count baris");
    }

    protected function resolveRekening($oldRekeningId, $oldLembagaId)
    {
        if (!$oldRekeningId || !$oldLembagaId) return null;
        $newLembagaId = $this->map['lembaga'][$oldLembagaId] ?? null;
        if (!$newLembagaId) return null;
        return $this->map['rekening'][$oldRekeningId][$newLembagaId] ?? null;
    }

    protected function migrateTagihans()
    {
        $rows = $this->src()->table('tagihans')->get();
        foreach ($rows as $row) {
            $oldLembagaId = $row->siswa_id ? ($this->map['siswa_old_lembaga'][$row->siswa_id] ?? null) : null;
            $data = $this->copyRow($row, [
                'siswa_id' => $row->siswa_id ? ($this->map['siswa'][$row->siswa_id] ?? null) : null,
                'jenis_tagihan_id' => $this->map['jenis_tagihan'][$row->jenis_tagihan_id] ?? null,
                'tahun_ajaran_id' => $row->tahun_ajaran_id ? ($this->map['tahun_ajaran'][$row->tahun_ajaran_id] ?? null) : null,
                'periode_tahun_ajaran_id' => $row->periode_tahun_ajaran_id ? ($this->map['tahun_ajaran'][$row->periode_tahun_ajaran_id] ?? null) : null,
                'rekening_id' => $this->resolveRekening($row->rekening_id, $oldLembagaId),
                'ppdb_id' => null,
            ]);
            $newId = DB::table('tagihans')->insertGetId($data);
            $this->map['tagihan'][$row->id] = $newId;
        }
        $this->info('tagihans: ' . count($this->map['tagihan']) . ' baris');
    }

    protected function migratePembayarans()
    {
        $rows = $this->src()->table('pembayarans')->get();
        foreach ($rows as $row) {
            if (!isset($this->map['tagihan'][$row->tagihan_id])) continue;
            $data = $this->copyRow($row, [
                'tagihan_id' => $this->map['tagihan'][$row->tagihan_id],
                'siswa_id' => $row->siswa_id ? ($this->map['siswa'][$row->siswa_id] ?? null) : null,
                'ppdb_id' => null,
            ]);
            $newId = DB::table('pembayarans')->insertGetId($data);
            $this->map['pembayaran'][$row->id] = $newId;
        }
        $this->info('pembayarans: ' . count($this->map['pembayaran']) . ' baris');
    }

    protected function migrateKas()
    {
        $rows = $this->src()->table('kas')->get();
        $count = 0;
        foreach ($rows as $row) {
            $newLembagaId = $row->lembaga_id ? ($this->map['lembaga'][$row->lembaga_id] ?? null) : null;
            $data = $this->copyRow($row, [
                'lembaga_id' => $newLembagaId,
                'rekening_id' => $this->resolveRekening($row->rekening_id, $row->lembaga_id),
                'kategori_id' => $row->kategori_id ? ($this->map['kategori_kas'][$row->kategori_id] ?? null) : null,
                'pembayaran_id' => $row->pembayaran_id ? ($this->map['pembayaran'][$row->pembayaran_id] ?? null) : null,
                'payroll_id' => null,
            ]);
            DB::table('kas')->insert($data);
            $count++;
        }
        $this->info("kas: $count baris");
    }

    protected function migrateAbsensi()
    {
        $rows = $this->src()->table('absensi')->get();
        $count = 0;
        $skipped = 0;
        foreach ($rows as $row) {
            $newJadwalKegiatanId = $this->map['jadwal_kegiatan'][$row->jadwal_kegiatan_id] ?? null;
            if (!$newJadwalKegiatanId) {
                $skipped++;
                continue;
            }
            $data = $this->copyRow($row, [
                'jadwal_kegiatan_id' => $newJadwalKegiatanId,
                'siswa_id' => $row->siswa_id ? ($this->map['siswa'][$row->siswa_id] ?? null) : null,
                'pegawai_id' => $row->pegawai_id ? ($this->map['pegawai'][$row->pegawai_id] ?? null) : null,
            ]);
            DB::table('absensi')->insert($data);
            $count++;
        }
        $this->info("absensi: $count baris ($skipped di-skip karena jadwal_kegiatan_id tidak valid)");
    }

    protected function migrateAnnouncements()
    {
        $rows = $this->src()->table('announcements')->get();
        $count = 0;
        foreach ($rows as $row) {
            $data = $this->copyRow($row, [
                'kelas_id' => $row->kelas_id ? ($this->map['kelas'][$row->kelas_id] ?? null) : null,
                'created_by' => $row->created_by ? ($this->map['user'][$row->created_by] ?? null) : null,
            ]);
            DB::table('announcements')->insert($data);
            $count++;
        }
        $this->info("announcements: $count baris");
    }
}
