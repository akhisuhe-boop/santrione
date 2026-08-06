<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tagihan;

class HapusSppJuliSdit extends Command
{
    protected $signature = 'tagihan:hapus-spp-juli-sdit
        {--dry-run : Cuma tampilkan yang AKAN dihapus, tanpa benar-benar menghapus}
        {--hapus-yang-lunas : Ikut hapus juga yang statusnya SUDAH LUNAS (default: dilewati, tidak dihapus)}';

    protected $description = 'Hapus tagihan SPP bulan Juli 2026 untuk semua siswa SDIT Al-Mubarok, karena sudah termasuk di biaya Daftar Ulang.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $hapusYangLunas = (bool) $this->option('hapus-yang-lunas');

        $query = Tagihan::query()
            ->whereHas('jenisTagihan', fn ($q) => $q->where('is_bulanan', true))
            ->whereHas('siswa.lembaga', fn ($q) => $q->where('nama', 'like', '%SDIT%Al-Mubarok%'))
            ->whereMonth('jatuh_tempo', 7)
            ->whereYear('jatuh_tempo', 2026)
            ->with(['siswa', 'jenisTagihan']);

        $semua = $query->get();

        if ($semua->isEmpty()) {
            $this->info('Tidak ada tagihan SPP Juli 2026 SDIT Al-Mubarok yang ditemukan.');
            return self::SUCCESS;
        }

        $sudahBayar = $semua->filter(fn ($t) => $t->nominal_terbayar > 0 || $t->status === 'lunas');
        $belumBayar = $semua->filter(fn ($t) => $t->nominal_terbayar == 0 && $t->status !== 'lunas');

        $this->info('=== RINGKASAN ===');
        $this->table(['Kategori', 'Jumlah', 'Total Nominal'], [
            ['Belum bayar sama sekali', $belumBayar->count(), 'Rp ' . number_format($belumBayar->sum('nominal'), 0, ',', '.')],
            ['Sudah ada pembayaran / lunas', $sudahBayar->count(), 'Rp ' . number_format($sudahBayar->sum('nominal_terbayar'), 0, ',', '.')],
            ['TOTAL', $semua->count(), 'Rp ' . number_format($semua->sum('nominal'), 0, ',', '.')],
        ]);

        if ($sudahBayar->isNotEmpty()) {
            $this->warn('⚠️  Ada ' . $sudahBayar->count() . ' tagihan yang SUDAH ADA PEMBAYARANNYA:');
            $this->table(
                ['ID', 'Siswa', 'Nominal', 'Terbayar', 'Status'],
                $sudahBayar->map(fn ($t) => [
                    $t->id,
                    $t->siswa?->nama_lengkap ?? '-',
                    number_format($t->nominal, 0, ',', '.'),
                    number_format($t->nominal_terbayar, 0, ',', '.'),
                    $t->status,
                ])
            );

            if (!$hapusYangLunas) {
                $this->warn('Baris di atas TIDAK akan dihapus (default aman). Pakai --hapus-yang-lunas kalau memang mau ikut menghapusnya juga.');
            }
        }

        $akanDihapus = $hapusYangLunas ? $semua : $belumBayar;

        if ($akanDihapus->isEmpty()) {
            $this->info('Tidak ada yang akan dihapus (semua sudah ada pembayaran, dan --hapus-yang-lunas tidak diaktifkan).');
            return self::SUCCESS;
        }

        $this->info('');
        $this->info('Total yang AKAN DIHAPUS: ' . $akanDihapus->count() . ' tagihan (Rp ' . number_format($akanDihapus->sum('nominal'), 0, ',', '.') . ')');

        if ($dryRun) {
            $this->warn('Mode DRY-RUN — tidak ada yang dihapus. Jalankan tanpa --dry-run untuk benar-benar menghapus.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Yakin mau hapus ' . $akanDihapus->count() . ' tagihan di atas? Ini TIDAK BISA dibatalkan.', false)) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        $dihapus = 0;

        foreach ($akanDihapus as $t) {
            $t->delete();
            $dihapus++;
        }

        $this->info("Selesai. {$dihapus} tagihan berhasil dihapus.");

        return self::SUCCESS;
    }
}
