<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Tagihan;
use App\Models\TahunAjaran;

class FixJatuhTempoTagihanBulanan extends Command
{
    protected $signature = 'tagihan:fix-jatuh-tempo
        {--dry-run : Cuma tampilkan yang AKAN diubah, tanpa benar-benar mengubah}
        {--lembaga= : Filter nama lembaga (opsional, contoh: --lembaga=MTs)}';

    protected $description = 'Benarkan jatuh_tempo tagihan bulanan (SPP) bulan Jan-Jun yang salah tahun kalender akibat bug lama. Hanya menyentuh tagihan yang BELUM ADA PEMBAYARAN SAMA SEKALI. Nominal tidak diubah.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $filterLembaga = $this->option('lembaga');

        $query = Tagihan::query()
            ->whereHas('jenisTagihan', fn ($q) => $q->where('is_bulanan', true))
            ->whereIn('bulan', ['01', '02', '03', '04', '05', '06'])
            ->where('status', 'Belum')
            ->where('nominal_terbayar', 0)
            ->with(['tahunAjaran', 'siswa.lembaga']);

        if ($filterLembaga) {
            $query->whereHas('siswa.lembaga', fn ($q) => $q->where('nama', 'like', "%{$filterLembaga}%"));
        }

        $tagihans = $query->get();

        $rencana = [];
        $dilewati = 0;

        foreach ($tagihans as $t) {

            $tahunAjaran = $t->tahunAjaran;
            if (!$tahunAjaran) {
                $dilewati++;
                continue;
            }

            $tahunAwal = (int) substr($tahunAjaran->nama, 0, 4);
            $tahunSeharusnya = $tahunAwal + 1; // Jan-Jun selalu tahun kedua

            $tanggalSeharusnya = Carbon::createFromDate(
                $tahunSeharusnya,
                (int) $t->bulan,
                Carbon::parse($t->jatuh_tempo)->day
            );

            // Skip kalau tanggalnya sudah benar
            if (Carbon::parse($t->jatuh_tempo)->isSameDay($tanggalSeharusnya)) {
                $dilewati++;
                continue;
            }

            $rencana[] = [
                'id' => $t->id,
                'lembaga' => $t->siswa?->lembaga?->nama ?? '-',
                'judul' => $t->judul,
                'dari' => Carbon::parse($t->jatuh_tempo)->format('d-m-Y'),
                'ke' => $tanggalSeharusnya->format('d-m-Y'),
                'baru' => $tanggalSeharusnya,
                'model' => $t,
            ];
        }

        if (empty($rencana)) {
            $this->info("Tidak ada tagihan yang perlu dibenarkan. (Dilewati: {$dilewati})");
            return self::SUCCESS;
        }

        // Rekap per lembaga
        $perLembaga = collect($rencana)->groupBy('lembaga')->map->count();

        $this->table(['Lembaga', 'Jumlah Tagihan Akan Dibenarkan'], $perLembaga->map(fn ($v, $k) => [$k, $v])->values());

        $this->info('Total: ' . count($rencana) . ' tagihan akan dibenarkan. Dilewati (sudah benar/tanpa tahun ajaran): ' . $dilewati);

        if ($dryRun) {
            $this->warn('Mode DRY-RUN — tidak ada yang diubah. Jalankan tanpa --dry-run untuk benar-benar mengeksekusi.');

            // Tampilkan 20 contoh biar bisa diperiksa
            $this->table(
                ['ID', 'Lembaga', 'Judul', 'Dari', 'Ke'],
                collect($rencana)->take(20)->map(fn ($r) => [$r['id'], $r['lembaga'], $r['judul'], $r['dari'], $r['ke']])
            );

            return self::SUCCESS;
        }

        if (!$this->confirm('Lanjutkan mengubah ' . count($rencana) . ' tagihan di atas?', true)) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        $diubah = 0;

        foreach ($rencana as $r) {
            $r['model']->update(['jatuh_tempo' => $r['baru']]);
            $diubah++;
        }

        $this->info("Selesai. {$diubah} tagihan berhasil dibenarkan jatuh_tempo-nya.");

        return self::SUCCESS;
    }
}
