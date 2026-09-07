<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AbsensiHarian;
use App\Models\Lembaga;

/**
 * Membersihkan record Alpa yang salah tercatat untuk siswa/guru di
 * lembaga yang BELUM PERNAH mengatur Jam Absensi (jam_masuk_siswa /
 * jam_masuk_guru masih kosong). Record ini muncul karena bug lama di
 * TandaiAlpaAbsensiHarian yang menandai Alpa tanpa mengecek dulu
 * apakah lembaganya memang pakai fitur absensi ini.
 *
 * Yang dihapus HANYA yang:
 * - status_masuk = 'Alpa'
 * - metode_masuk = 'Auto (tidak scan)'  (bukan input manual admin)
 * - siswa/pegawai-nya ada di lembaga yang jam absensinya masih kosong
 *   SAMPAI SEKARANG (kalau lembaga sudah diisi jamnya lagi setelah
 *   sempat kosong, record lama lembaga itu TIDAK ikut dihapus --
 *   supaya tidak menghapus histori yang mungkin sudah benar).
 *
 * SELALU jalankan tanpa --force dulu (mode preview/dry-run) sebelum
 * pakai --force untuk benar-benar menghapus.
 */
class BersihkanAlpaInvalid extends Command
{
    protected $signature = 'absensi:bersihkan-alpa-invalid {--force : Benar-benar hapus, bukan cuma preview}';

    protected $description = 'Hapus record Alpa otomatis yang salah untuk lembaga yang belum atur Jam Absensi';

    public function handle(): int
    {
        $lembagaSiswaKosong = Lembaga::whereNull('jam_masuk_siswa')->pluck('id');

        // ===============================
        // SISWA
        // ===============================
        $querySiswa = AbsensiHarian::query()
            ->where('tipe', 'siswa')
            ->where('status_masuk', 'Alpa')
            ->where('metode_masuk', 'Auto (tidak scan)')
            ->whereHas('siswa', fn ($q) => $q->whereIn('lembaga_id', $lembagaSiswaKosong));

        $totalSiswa = (clone $querySiswa)->count();

        // ===============================
        // GURU/PEGAWAI
        // ===============================
        // Guru bisa terhubung ke LEBIH DARI 1 lembaga (pivot many-to-many).
        // Yang menentukan valid/tidaknya Alpa dia HARUS pakai lembaga utama
        // yang sama persis dengan yang dipakai TandaiAlpaAbsensiHarian
        // (lembagaUtama()), bukan "salah satu dari semua lembaganya" --
        // supaya guru yang lembaga utamanya SUDAH atur jam (mis. SDIT)
        // tidak ikut kehapus gara-gara dia juga kebetulan punya jadwal
        // di lembaga lain yang belum diatur.
        $idAlpaGuruDihapus = [];
        $rincianGuruMap = [];

        AbsensiHarian::query()
            ->where('tipe', 'guru')
            ->where('status_masuk', 'Alpa')
            ->where('metode_masuk', 'Auto (tidak scan)')
            ->with('pegawai')
            ->chunkById(500, function ($rows) use (&$idAlpaGuruDihapus, &$rincianGuruMap) {
                foreach ($rows as $row) {
                    $lembagaUtama = $row->pegawai?->lembagaUtama();

                    if (!$lembagaUtama || $lembagaUtama->jam_masuk_guru !== null) {
                        continue;
                    }

                    $idAlpaGuruDihapus[] = $row->id;
                    $nama = $lembagaUtama->nama;
                    $rincianGuruMap[$nama] = ($rincianGuruMap[$nama] ?? 0) + 1;
                }
            });

        $totalGuru = count($idAlpaGuruDihapus);

        $this->newLine();
        $this->info("Lembaga yang jam absensi siswanya belum diatur: " . $lembagaSiswaKosong->count());
        $this->info("Lembaga yang jam absensi gurunya belum diatur (berdasarkan lembaga utama guru): " . count($rincianGuruMap));
        $this->newLine();

        $this->table(
            ['Tipe', 'Jumlah record Alpa yang akan dihapus'],
            [
                ['Siswa', $totalSiswa],
                ['Guru', $totalGuru],
                ['TOTAL', $totalSiswa + $totalGuru],
            ]
        );

        // Rincian per lembaga biar kelihatan jelas punya siapa saja
        $rincianSiswa = (clone $querySiswa)
            ->with('siswa.lembaga')
            ->get()
            ->groupBy(fn ($a) => $a->siswa?->lembaga?->nama ?? '(tidak diketahui)')
            ->map->count();

        if ($rincianSiswa->isNotEmpty()) {
            $this->newLine();
            $this->info('Rincian Alpa siswa per lembaga:');
            foreach ($rincianSiswa as $namaLembaga => $jumlah) {
                $this->line("  - {$namaLembaga}: {$jumlah}");
            }
        }

        if (!empty($rincianGuruMap)) {
            $this->newLine();
            $this->info('Rincian Alpa guru per lembaga (lembaga utama guru):');
            foreach ($rincianGuruMap as $namaLembaga => $jumlah) {
                $this->line("  - {$namaLembaga}: {$jumlah}");
            }
        }

        if (!$this->option('force')) {
            $this->newLine();
            $this->warn('Ini baru PREVIEW, belum ada yang dihapus.');
            $this->warn('Kalau jumlah & rinciannya di atas sudah sesuai, jalankan lagi dengan tambahan --force untuk benar-benar menghapus:');
            $this->line('  php artisan absensi:bersihkan-alpa-invalid --force');

            return self::SUCCESS;
        }

        $hapusSiswa = (clone $querySiswa)->delete();
        $hapusGuru = AbsensiHarian::whereIn('id', $idAlpaGuruDihapus)->delete();

        $this->newLine();
        $this->info("Selesai. Terhapus -> Siswa: {$hapusSiswa}, Guru: {$hapusGuru}");

        return self::SUCCESS;
    }
}
