<?php

namespace App\Services;

use App\Data\TeachingSession;
use App\Models\Kurikulum;
use Illuminate\Support\Collection;

class SessionBuilder
{
    /**
     * Mengubah data Kurikulum menjadi daftar TeachingSession.
     *
     * Setiap TeachingSession merepresentasikan
     * satu kali pertemuan mengajar.
     *
     * Contoh:
     * JP/Minggu = 6
     * JP/Pertemuan = 2
     *
     * Akan menghasilkan:
     * - Pertemuan 1 (2 JP)
     * - Pertemuan 2 (2 JP)
     * - Pertemuan 3 (2 JP)
     */
        public function build(int $kelasId): Collection
{
    $kurikulums = Kurikulum::with([
            'pegawai',
            'mataPelajaran',
        ])
        ->where('kelas_id', $kelasId)
        ->orderBy('jumlah_jam_per_minggu', 'desc')
        ->orderBy('mata_pelajaran_id')
        ->get();

    $waves = [];

    foreach ($kurikulums as $kurikulum) {

        if (
            $kurikulum->jumlah_jam_per_minggu <= 0 ||
            $kurikulum->jp_per_pertemuan <= 0
        ) {
            continue;
        }

        $jumlahPertemuan = (int) ceil(
            $kurikulum->jumlah_jam_per_minggu /
            $kurikulum->jp_per_pertemuan
        );

        for ($pertemuan = 1; $pertemuan <= $jumlahPertemuan; $pertemuan++) {

            $waves[$pertemuan] ??= [];

            $waves[$pertemuan][] = new TeachingSession(
                kelasId: $kurikulum->kelas_id,
                pegawaiId: $kurikulum->pegawai_id,
                mataPelajaranId: $kurikulum->mata_pelajaran_id,
                durasi: $kurikulum->jp_per_pertemuan,
                pertemuanKe: $pertemuan,
                totalPertemuan: $jumlahPertemuan,
            );
        }
    }

    $sessions = collect();

    ksort($waves);

    foreach ($waves as $wave) {

        usort($wave, function (TeachingSession $a, TeachingSession $b) {

            if ($a->durasi !== $b->durasi) {
                return $b->durasi <=> $a->durasi;
            }

            return $a->mataPelajaranId <=> $b->mataPelajaranId;
        });

        foreach ($wave as $session) {
            $sessions->push($session);
        }
    }

    return $sessions;
}
}