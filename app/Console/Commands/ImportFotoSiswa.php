<?php

namespace App\Console\Commands;

use App\Jobs\ResizeAndUploadFotoSiswa;
use Illuminate\Console\Command;
use ZipArchive;

class ImportFotoSiswa extends Command
{
    protected $signature = 'siswa:import-foto {zip_path}';
    protected $description = 'Import foto siswa massal dari ZIP (nama file = NIS)';

    public function handle(): int
    {
        $zipPath = $this->argument('zip_path');

        if (!file_exists($zipPath)) {
            $this->error("File ZIP tidak ditemukan: {$zipPath}");
            return self::FAILURE;
        }

        $extractPath = storage_path('app/temp/foto-siswa-' . time());

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            $this->error('Gagal membuka file ZIP.');
            return self::FAILURE;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $allFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $validExt = ['jpg', 'jpeg', 'png', 'webp'];
        $skippedHeic = [];
        $count = 0;

        foreach ($allFiles as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            $nameWithoutExt = $file->getBasename('.' . $file->getExtension());
            $nis = $nameWithoutExt;

            if ($ext === 'heic') {
                $skippedHeic[] = $nis;
                continue;
            }

            if (!in_array($ext, $validExt)) {
                continue;
            }

            ResizeAndUploadFotoSiswa::dispatch($nis, $file->getPathname())->onQueue('photos');
            $count++;
        }

        $this->info("{$count} foto dimasukkan ke antrian (queue: photos).");

        if (count($skippedHeic) > 0) {
            $this->warn(count($skippedHeic) . ' file HEIC dilewati (format tidak didukung server). NIS berikut perlu foto dikonversi manual ke JPG:');
            $this->line(implode(', ', $skippedHeic));
        }

        @unlink($zipPath);

        return self::SUCCESS;
    }
}
