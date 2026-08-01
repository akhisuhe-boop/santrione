<?php

namespace App\Jobs;

use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ResizeAndUploadFotoSiswa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public string $nis,
        public string $tempPath
    ) {}

    public function handle(): void
    {
        $siswa = Siswa::where('nis', $this->nis)->first();

        if (!$siswa) {
            $this->fail("NIS {$this->nis} tidak ditemukan di database siswa");
            return;
        }

        $image = Image::decode(file_get_contents($this->tempPath))
        ->cover(800, 1000)
        ->encodeUsingFileExtension('webp', quality: 80);

        $filename = "siswa-photos/{$this->nis}.webp";

        Storage::disk('r2-public')->put($filename, (string) $image);

        $siswa->update(['foto' => $filename]);

        @unlink($this->tempPath);
    }
}
