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
        $lembagaGuruKosong = Lembaga::whereNull('jam_masuk_guru')->pluck('id');

        // ===============================
        // SISWA
        // ===============================
        $querySiswa = AbsensiHarian::query()
            ->where('tipe', 'siswa')
            ->where('status_masuk', 'Alpa')
            ->where('metode_masuk', 'Auto (tidak scan)')
            ->whereHas('siswa', fn ($q) => $q->whereIn('lembaga_id', $lembagaSiswaKosong));

        // ===============================
        // GURU/PEGAWAI (lewat lembaga utama pegawai)
        // ===============================
        $queryGuru = AbsensiHarian::query()
            ->where('tipe', 'guru')
            ->where('status_masuk', 'Alpa')
            ->where('metode_masuk', 'Auto (tidak scan)')
            ->whereHas('pegawai.lembagas', fn ($q) => $q->whereIn('lembagas.id', $lembagaGuruKosong));

        $totalSiswa = (clone $querySiswa)->count();
        $totalGuru = (clone $queryGuru)->count();

        $this->newLine();
        $this->info("Lembaga yang jam absensi siswanya belum diatur: " . $lembagaSiswaKosong->count());
        $this->info("Lembaga yang jam absensi gurunya belum diatur: " . $lembagaGuruKosong->count());
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

        $rincianGuru = (clone $queryGuru)
            ->with('pegawai')
            ->get()
            ->groupBy(fn ($a) => $a->pegawai?->lembagaUtama()?->nama ?? '(tidak diketahui)')
            ->map->count();

        if ($rincianGuru->isNotEmpty()) {
            $this->newLine();
            $this->info('Rincian Alpa guru per lembaga:');
            foreach ($rincianGuru as $namaLembaga => $jumlah) {
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
        $hapusGuru = (clone $queryGuru)->delete();

        $this->newLine();
        $this->info("Selesai. Terhapus -> Siswa: {$hapusSiswa}, Guru: {$hapusGuru}");

        return self::SUCCESS;
    }
}
