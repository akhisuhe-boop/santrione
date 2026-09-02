<?php

namespace App\Console\Commands;

use App\Models\JurnalMengajar;
use Illuminate\Console\Command;

class FixJurnalPegawaiLembaga extends Command
{
    protected $signature = 'jurnal:fix-pegawai-lembaga {--dry-run : Cuma tampilkan yang akan diubah, tidak benar-benar disimpan}';

    protected $description = 'Perbaiki pegawai_lembaga_id di jurnal_mengajars lama yang salah tertandai (bug: selalu ambil jabatan pertama guru, bukan yang sesuai lembaga kelasnya)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $jurnals = JurnalMengajar::query()
            ->with(['kelas', 'pegawai.pegawaiLembagas', 'pegawaiLembaga'])
            ->get();

        $diperbaiki = 0;
        $sudahBenar = 0;
        $tidakBisaDiresolusi = 0;

        $this->info('Memeriksa ' . $jurnals->count() . ' baris jurnal mengajar...');

        foreach ($jurnals as $jurnal) {

            if (! $jurnal->pegawai) {
                $tidakBisaDiresolusi++;
                continue;
            }

            $lembagaId = $jurnal->kelas?->lembaga_id;
            $guru = $jurnal->pegawai;

            $pegawaiLembagaBenar = null;

            if ($lembagaId) {

                $pegawaiLembagaBenar = $guru->pegawaiLembagas
                    ->where('lembaga_id', $lembagaId)
                    ->where('metode_penggajian', 'per_jp')
                    ->first()

                    ?? $guru->pegawaiLembagas
                    ->where('lembaga_id', $lembagaId)
                    ->first();
            }

            if (! $pegawaiLembagaBenar) {
                $tidakBisaDiresolusi++;
                continue;
            }

            if ($jurnal->pegawai_lembaga_id === $pegawaiLembagaBenar->id) {
                $sudahBenar++;
                continue;
            }

            $this->line(sprintf(
                'Jurnal #%d (%s, %s) : pegawai_lembaga_id %s -> %s',
                $jurnal->id,
                $guru->nama,
                $jurnal->tanggal,
                $jurnal->pegawai_lembaga_id ?? 'kosong',
                $pegawaiLembagaBenar->id
            ));

            if (! $dryRun) {
                $jurnal->update(['pegawai_lembaga_id' => $pegawaiLembagaBenar->id]);
            }

            $diperbaiki++;
        }

        $this->newLine();
        $this->info("Selesai. Diperbaiki: {$diperbaiki} | Sudah benar: {$sudahBenar} | Tidak bisa diresolusi (lembaga kelas kosong/tidak cocok): {$tidakBisaDiresolusi}");

        if ($dryRun) {
            $this->warn('Ini masih --dry-run, TIDAK ADA perubahan yang disimpan. Jalankan ulang tanpa --dry-run untuk benar-benar memperbaiki.');
        }

        return self::SUCCESS;
    }
}
