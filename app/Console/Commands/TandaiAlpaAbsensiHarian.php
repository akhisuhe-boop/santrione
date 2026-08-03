<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\AbsensiHarian;
use App\Models\JadwalPelajaran;

class TandaiAlpaAbsensiHarian extends Command
{
    protected $signature = 'absensi:tandai-alpa';

    protected $description = 'Menandai Alpa untuk siswa/guru yang tidak scan absensi dan tidak punya izin/sakit yang disetujui, pada hari sekolah aktif';

    protected static array $hariMap = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public function handle(): int
    {
        $today = Carbon::today();
        $hariIni = self::$hariMap[$today->dayOfWeek];
        $tanggal = $today->format('Y-m-d');

        $this->info("Memproses Alpa untuk {$hariIni}, {$tanggal}...");

        // ===============================
        // SISWA — hanya yang kelasnya ada jadwal pelajaran hari ini
        // ===============================

        $kelasIdsAdaJadwal = JadwalPelajaran::where('hari', $hariIni)
            ->distinct()
            ->pluck('kelas_id');

        $totalSiswaAlpa = 0;

        Siswa::whereIn('kelas_id', $kelasIdsAdaJadwal)
            ->where('status_siswa', 'Aktif')
            ->chunk(200, function ($siswas) use ($tanggal, &$totalSiswaAlpa) {

                foreach ($siswas as $siswa) {

                    $sudahAda = AbsensiHarian::where('tanggal', $tanggal)
                        ->where('siswa_id', $siswa->id)
                        ->exists();

                    if ($sudahAda) {
                        continue;
                    }

                    AbsensiHarian::create([
                        'tanggal' => $tanggal,
                        'siswa_id' => $siswa->id,
                        'tipe' => 'siswa',
                        'status_masuk' => 'Alpa',
                        'metode_masuk' => 'Auto (tidak scan)',
                    ]);

                    $totalSiswaAlpa++;
                }
            });

        // ===============================
        // GURU/PEGAWAI — hanya yang punya jadwal mengajar hari ini
        // ===============================

        $pegawaiIdsAdaJadwal = JadwalPelajaran::where('hari', $hariIni)
            ->distinct()
            ->pluck('pegawai_id');

        $totalGuruAlpa = 0;

        Pegawai::whereIn('id', $pegawaiIdsAdaJadwal)
            ->chunk(200, function ($pegawais) use ($tanggal, &$totalGuruAlpa) {

                foreach ($pegawais as $pegawai) {

                    $sudahAda = AbsensiHarian::where('tanggal', $tanggal)
                        ->where('pegawai_id', $pegawai->id)
                        ->exists();

                    if ($sudahAda) {
                        continue;
                    }

                    AbsensiHarian::create([
                        'tanggal' => $tanggal,
                        'pegawai_id' => $pegawai->id,
                        'tipe' => 'guru',
                        'status_masuk' => 'Alpa',
                        'metode_masuk' => 'Auto (tidak scan)',
                    ]);

                    $totalGuruAlpa++;
                }
            });

        $this->info("Selesai. Siswa Alpa: {$totalSiswaAlpa}, Guru Alpa: {$totalGuruAlpa}");

        return self::SUCCESS;
    }
}
