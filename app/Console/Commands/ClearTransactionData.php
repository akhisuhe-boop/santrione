<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTransactionData extends Command
{
    /**
     * php artisan dev:clear-transactions
     *
     * HANYA untuk dipakai di environment DEV/TESTING. Sebagai pengaman,
     * command ini akan menampilkan nama database yang akan dikosongkan
     * dan minta kamu ketik ulang nama itu sebagai konfirmasi sebelum
     * benar-benar menghapus apapun — supaya tidak ke-run tanpa sadar
     * ke database production. Gunakan --force untuk lewati konfirmasi
     * ini (HANYA di script otomatis yang kamu yakin aman, mis. CI/CD
     * khusus dev).
     */
    protected $signature = 'dev:clear-transactions {--force : Jalankan tanpa konfirmasi interaktif}';

    protected $description = 'Kosongkan semua data TRANSAKSI (jurnal, absensi, payroll, kas, pembayaran, tagihan, ppdb, dll) di environment dev — data master (user, pegawai, siswa, kelas, kurikulum, jadwal, setting) tetap aman.';

    /**
     * Tabel data TRANSAKSI yang akan dikosongkan.
     *
     * Sengaja TIDAK termasuk: yayasans, lembagas, users, pegawais,
     * pegawai_lembaga, siswas, kelas, kurikulums, jadwal_pelajarans,
     * jam_pelajarans, mata_pelajarans, tahun_ajarans, payroll_adjustment_templates,
     * dan semua tabel master/setting lainnya — supaya kelas, jadwal,
     * kurikulum, dan akun yang sudah disiapkan untuk testing tidak
     * ikut hilang.
     */
    protected array $tables = [
        // Jurnal mengajar & absensi
        'absensi_mapels',
        'jurnal_mengajars',
        'absensi',

        // Payroll (kecuali payroll_adjustment_templates — itu setting, bukan transaksi)
        'payroll_items',
        'payroll_adjustments',
        'payrolls',

        // Keuangan
        'wallet_transactions',
        'wallets',
        'withdraw_requests',
        'kas',
        'pembayarans',
        'tagihans',

        // Kesiswaan
        'perizinans',
        'pelanggaran_siswas',
        'prestasi_siswas',
        'ppdbs',

        // Nilai & raport
        'nilais',
        'rekap_nilais',
        'raport_ekstrakurikulers',
        'raport_kepribadians',
        'raport_non_akademiks',

        // Tahfidz (progress hafalan — transaksional; target dibiarkan)
        'tahfidz_setorans',

        // Sampah sistem (aman dikosongkan kapan saja)
        'jobs',
        'failed_import_rows',
        'imports',
        'exports',
        'cache',
    ];

    public function handle(): int
    {
        $connection = DB::connection();
        $dbName = $connection->getDatabaseName();

        $this->warn('Command ini akan MENGOSONGKAN PERMANEN tabel-tabel berikut (semua tenant/yayasan):');
        $this->line('  - ' . implode("\n  - ", $this->tables));
        $this->newLine();
        $this->info('Data master (user, pegawai, siswa, kelas, kurikulum, jadwal pelajaran, setting) TIDAK disentuh.');
        $this->newLine();

        $this->error("Database yang akan dikosongkan: \"{$dbName}\"");
        $this->line('PASTIKAN ini benar database DEV, bukan production, sebelum lanjut.');
        $this->newLine();

        if (! $this->option('force')) {

            $typed = $this->ask('Ketik ulang nama database di atas untuk konfirmasi (atau kosongkan/Ctrl+C untuk batal)');

            if ($typed !== $dbName) {
                $this->info('Nama tidak cocok — dibatalkan, tidak ada yang dihapus.');
                return self::SUCCESS;
            }
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {

            if (! Schema::hasTable($table)) {
                $this->line("  (lewati, tabel tidak ada: {$table})");
                continue;
            }

            DB::table($table)->truncate();
            $this->line("  ✓ dikosongkan: {$table}");
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info('Selesai. Semua data transaksi sudah kosong.');

        return self::SUCCESS;
    }
}
