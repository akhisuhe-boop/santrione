<?php

namespace App\Console\Commands;

use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Lembaga;
use App\Models\MataPelajaran;
use App\Models\Pegawai;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\Yayasan;
use App\Services\JadwalGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedDemoData extends Command
{
    /**
     * php artisan dev:seed-demo
     *
     * Bikin 1 set data contoh siap pakai buat testing di dev — 1
     * yayasan, 1 lembaga, 2 kelas, jam pelajaran, mata pelajaran, 3
     * guru (2 punya jadwal tetap, 1 sengaja dikosongkan supaya bisa
     * dipakai testing alur "guru pengganti"), kurikulum, 1 akun staf
     * admin panel (buat re-test bug login), dan langsung digenerate
     * jadwal pelajarannya.
     *
     * Aman dijalankan berkali-kali — kalau datanya sudah ada
     * (dicek dari slug yayasan), command akan berhenti dan bilang
     * supaya dev:clear-transactions / migrate:fresh dulu.
     */
    protected $signature = 'dev:seed-demo';

    protected $description = 'Seed 1 set data demo (yayasan, lembaga, kelas, guru, kurikulum, jadwal) buat testing di dev';

    protected string $slug = 'testing-dev';

    public function handle(): int
    {
        if (Yayasan::where('slug', $this->slug)->exists()) {
            $this->error("Yayasan dengan slug \"{$this->slug}\" sudah ada. Kalau mau seed ulang dari bersih, jalankan dulu \"php artisan migrate:fresh --seed\" atau hapus yayasan itu manual.");
            return self::FAILURE;
        }

        $this->info('Membuat data demo...');

        /*
        |--------------------------------------------------------------------------
        | YAYASAN & LEMBAGA
        |--------------------------------------------------------------------------
        */

        $yayasan = Yayasan::create([
            'nama' => 'Yayasan Testing Dev',
            'slug' => $this->slug,
            'ketua' => 'Ketua Testing',
            'email' => 'yayasan@testing-dev.local',
        ]);

        $lembaga = Lembaga::create([
            'yayasan_id' => $yayasan->id,
            'nama' => 'SDIT Testing',
            'jenis' => 'SD/MI',
            'kepala_sekolah' => 'Kepala Sekolah Testing',
            'tarif_pengganti_per_jp' => 15000,
        ]);

        $this->line("  ✓ Yayasan: {$yayasan->nama} (slug: {$yayasan->slug})");
        $this->line("  ✓ Lembaga: {$lembaga->nama} (tarif honor pengganti: Rp 15.000/JP)");

        /*
        |--------------------------------------------------------------------------
        | TAHUN AJARAN
        |--------------------------------------------------------------------------
        */

        TahunAjaran::create([
            'yayasan_id' => $yayasan->id,
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'aktif' => true,
        ]);

        $this->line('  ✓ Tahun Ajaran: 2026/2027 Ganjil (aktif)');

        /*
        |--------------------------------------------------------------------------
        | KELAS
        |--------------------------------------------------------------------------
        */

        $kelas1 = Kelas::create(['lembaga_id' => $lembaga->id, 'nama' => 'Kelas Testing 1']);
        $kelas2 = Kelas::create(['lembaga_id' => $lembaga->id, 'nama' => 'Kelas Testing 2']);

        $this->line("  ✓ Kelas: {$kelas1->nama}, {$kelas2->nama}");

        /*
        |--------------------------------------------------------------------------
        | JAM PELAJARAN (6 slot, 07:15 - 13:15)
        |--------------------------------------------------------------------------
        */

        $jamList = [];
        $mulai = \Carbon\Carbon::createFromTime(7, 15);

        for ($i = 1; $i <= 6; $i++) {

            $selesai = $mulai->copy()->addHour();

            $jamList[] = JamPelajaran::create([
                'lembaga_id' => $lembaga->id,
                'nama' => "Jam ke {$i}",
                'jam_mulai' => $mulai->format('H:i'),
                'jam_selesai' => $selesai->format('H:i'),
                'durasi_jp' => 1,
                'urutan' => $i,
                'aktif' => true,
            ]);

            $mulai = $selesai;
        }

        $this->line('  ✓ Jam Pelajaran: 6 slot (Jam ke 1–6, 07:15–13:15)');

        /*
        |--------------------------------------------------------------------------
        | MATA PELAJARAN
        |--------------------------------------------------------------------------
        */

        $mapelNames = ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'Biologi'];
        $mapel = [];

        foreach ($mapelNames as $nama) {
            $mapel[$nama] = MataPelajaran::create([
                'yayasan_id' => $yayasan->id,
                'nama' => $nama,
            ]);
        }

        $this->line('  ✓ Mata Pelajaran: ' . implode(', ', $mapelNames));

        /*
        |--------------------------------------------------------------------------
        | GURU (Pegawai) — 2 punya jadwal tetap, 1 sengaja kosong
        | (supaya bisa jadi guru pengganti)
        |--------------------------------------------------------------------------
        */

        $guruBudi = Pegawai::create([
            'nama' => 'Budi Santoso',
            'niy' => 'GURU001',
            'jenis_kelamin' => 'L',
            'is_active' => true,
            'password' => Hash::make('guru123'),
        ]);

        $guruSiti = Pegawai::create([
            'nama' => 'Siti Aminah',
            'niy' => 'GURU002',
            'jenis_kelamin' => 'P',
            'is_active' => true,
            'password' => Hash::make('guru123'),
        ]);

        $guruAhmad = Pegawai::create([
            'nama' => 'Ahmad Fauzi',
            'niy' => 'GURU003',
            'jenis_kelamin' => 'L',
            'is_active' => true,
            'password' => Hash::make('guru123'),
        ]);

        foreach ([$guruBudi, $guruSiti, $guruAhmad] as $guru) {

            $guru->lembagas()->attach($lembaga->id, [
                'jabatan' => 'Guru',
                'status' => 'aktif',
                'metode_penggajian' => 'per_jp',
                'tarif_per_jp' => 25000,
            ]);
        }

        $this->line('  ✓ Guru: Budi Santoso (GURU001), Siti Aminah (GURU002), Ahmad Fauzi (GURU003) — password semua: guru123');
        $this->line('    (Ahmad Fauzi sengaja tidak dikasih kurikulum, jadi kosong jadwalnya — dipakai buat testing isi jurnal pengganti)');

        /*
        |--------------------------------------------------------------------------
        | KURIKULUM
        |--------------------------------------------------------------------------
        */

        Kurikulum::create([
            'kelas_id' => $kelas1->id,
            'pegawai_id' => $guruBudi->id,
            'mata_pelajaran_id' => $mapel['Matematika']->id,
            'jumlah_jam_per_minggu' => 4,
            'jp_per_pertemuan' => 2,
        ]);

        Kurikulum::create([
            'kelas_id' => $kelas1->id,
            'pegawai_id' => $guruSiti->id,
            'mata_pelajaran_id' => $mapel['Bahasa Indonesia']->id,
            'jumlah_jam_per_minggu' => 4,
            'jp_per_pertemuan' => 2,
        ]);

        Kurikulum::create([
            'kelas_id' => $kelas2->id,
            'pegawai_id' => $guruBudi->id,
            'mata_pelajaran_id' => $mapel['IPA']->id,
            'jumlah_jam_per_minggu' => 2,
            'jp_per_pertemuan' => 2,
        ]);

        Kurikulum::create([
            'kelas_id' => $kelas2->id,
            'pegawai_id' => $guruSiti->id,
            'mata_pelajaran_id' => $mapel['IPS']->id,
            'jumlah_jam_per_minggu' => 2,
            'jp_per_pertemuan' => 2,
        ]);

        $this->line('  ✓ Kurikulum: Matematika & B. Indonesia (Kelas Testing 1), IPA & IPS (Kelas Testing 2)');

        /*
        |--------------------------------------------------------------------------
        | ROLE "ADMIN SEKOLAH" (role global, dipakai ulang di tenant manapun —
        | akses penuh KECUALI hal-hal khusus platform admin, tetap terkunci
        | ke 1 yayasan lewat yayasan_id akunnya, bukan lewat role ini)
        |--------------------------------------------------------------------------
        */

        // Permission di-generate lewat Shield (per Resource/Page/Widget).
        // Kalau belum pernah di-generate sama sekali (tabel permissions
        // masih kosong), generate dulu di sini supaya command ini aman
        // dipakai langsung setelah migrate:fresh tanpa langkah manual lain.
        if (\Spatie\Permission\Models\Permission::count() === 0) {

            $this->line('  (permission belum ada, menjalankan shield:generate dulu...)');

            $this->call('shield:generate', [
                '--all' => true,
                '--panel' => 'admin',
            ]);
        }

        $roleAdminSekolah = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'Admin Sekolah',
            'guard_name' => 'web',
        ]);

        $roleAdminSekolah->syncPermissions(
            \Spatie\Permission\Models\Permission::all()
        );

        $this->line('  ✓ Role "Admin Sekolah" siap (akses penuh, tapi tetap terkunci ke 1 yayasan — bukan platform admin)');

        /*
        |--------------------------------------------------------------------------
        | AKUN ADMIN TENANT (buat re-test bug login & testing dari sudut
        | pandang admin sekolah biasa, bukan platform admin)
        |--------------------------------------------------------------------------
        */

        $adminTenant = User::create([
            'name' => 'Admin Testing',
            'email' => 'admin@testing.com',
            'password' => Hash::make('staf12345'),
            'yayasan_id' => $yayasan->id,
        ]);

        $adminTenant->assignRole($roleAdminSekolah);

        $this->line('  ✓ Akun admin tenant: admin@testing.com / staf12345 (role: Admin Sekolah)');

        /*
        |--------------------------------------------------------------------------
        | GENERATE JADWAL PELAJARAN
        |--------------------------------------------------------------------------
        */

        $generator = app(JadwalGeneratorService::class);

        foreach ([$kelas1, $kelas2] as $kelas) {

            $ok = $generator->generateKelas($kelas->id);

            $this->line(
                $ok
                    ? "  ✓ Jadwal Pelajaran ter-generate untuk {$kelas->nama}"
                    : "  ✗ Gagal generate jadwal untuk {$kelas->nama} — cek manual di menu Jadwal Pelajaran"
            );
        }

        $this->newLine();
        $this->info('Selesai! Data demo siap dipakai.');
        $this->newLine();
        $this->line('Login admin tenant (1 yayasan saja): admin@testing.com / staf12345');
        $this->line('Login platform admin (semua yayasan): admin@admin.com / 123456 (kalau baru migrate:fresh)');
        $this->line('Login guru (portal guru, pakai NIY): GURU001 / guru123, GURU002 / guru123, GURU003 / guru123');

        return self::SUCCESS;
    }
}
