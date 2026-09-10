<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\KartuTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class KartuController extends Controller
{
    /**
     * DITAMBAHKAN -- ukur lebar teks (px) untuk font TTF tertentu,
     * dipakai supaya nilai field (nama/alamat/dll) tidak pernah
     * menabrak/menumpuk tepi kanan kartu.
     */
    private function measureTextWidth(string $text, string $fontFile, int $fontSize): float
    {
        $box = imagettfbbox($fontSize, 0, $fontFile, $text);
        return abs($box[2] - $box[0]);
    }

    /**
     * Coba tampilkan $text dalam maksimal $maxLines baris di ukuran
     * font tertentu, TANPA membuang kata apa pun. Return null kalau
     * tidak muat di ukuran itu (dipakai fitAndWrapText() untuk
     * mencoba ukuran font yang lebih kecil).
     */
    private function tryWrapExact(string $text, string $fontFile, int $fontSize, float $maxWidthPx, int $maxLines): ?array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [''];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($current === '' || $this->measureTextWidth($candidate, $fontFile, $fontSize) <= $maxWidthPx) {
                $current = $candidate;
            } else {
                $lines[] = $current;
                if (count($lines) >= $maxLines) {
                    return null;
                }
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return count($lines) <= $maxLines ? $lines : null;
    }

    /**
     * Cari ukuran font TERBESAR (dari $maxFontSize turun ke
     * $minFontSize) yang bisa menampilkan $text UTUH (tanpa kata
     * hilang) dalam $maxLines baris. Kalau bahkan di $minFontSize
     * masih tidak muat (misal alamat sangat panjang), baris terakhir
     * dipotong dengan "..." -- ini satu-satunya kondisi di mana teks
     * boleh terpotong, dan selalu ditandai jelas dengan "...".
     * Mengembalikan [baris[], ukuran_font_final].
     */
    private function fitAndWrapText(string $text, string $fontFile, int $maxFontSize, int $minFontSize, float $maxWidthPx, int $maxLines): array
    {
        for ($size = $maxFontSize; $size >= $minFontSize; $size--) {
            $lines = $this->tryWrapExact($text, $fontFile, $size, $maxWidthPx, $maxLines);
            if ($lines !== null) {
                return [$lines, $size];
            }
        }

        // Fallback -- paksa wrap di ukuran minimum, potong baris
        // terakhir dengan "..." kalau masih ada sisa yang tak muat.
        $words = preg_split('/\s+/', trim($text)) ?: [''];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($current === '' || $this->measureTextWidth($candidate, $fontFile, $minFontSize) <= $maxWidthPx) {
                $current = $candidate;
            } else {
                $lines[] = $current;
                if (count($lines) >= $maxLines) {
                    $current = '';
                    break;
                }
                $current = $word;
            }
        }
        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }
        if (empty($lines)) {
            $lines[] = $text;
        }

        $lastIdx = count($lines) - 1;
        $last = $lines[$lastIdx];
        while (mb_strlen($last) > 3 && $this->measureTextWidth($last . '...', $fontFile, $minFontSize) > $maxWidthPx) {
            $last = mb_substr($last, 0, -1);
        }
        $lines[$lastIdx] = $last . '...';

        return [$lines, $minFontSize];
    }

    /**
     * DITAMBAHKAN -- generator barcode Code128 (subset B) MURNI pakai
     * GD (imagecreatetruecolor/imagefilledrectangle/imagepng) --
     * TIDAK butuh extension Imagick dan TIDAK butuh package composer
     * tambahan apa pun. GD sudah pasti ada di server ini karena
     * Intervention Image sendiri jalan di atas GD driver.
     *
     * Kenapa ganti dari QR (simplesoftwareio/simple-qrcode) ke ini:
     * package itu butuh Imagick untuk output PNG (tanpa Imagick cuma
     * bisa SVG, dan Intervention Image tidak bisa decode SVG). Server
     * tidak boleh ditambah extension baru, jadi barcode-nya digambar
     * manual jadi PNG langsung.
     *
     * PENTING -- tabel pola & rumus checksum di bawah ini BUKAN
     * tebakan: diambil dari source code library `python-barcode`
     * (implementasi Code128 yang sudah lama dipakai luas), lalu
     * di-generate contoh & DI-SCAN ULANG pakai pembaca barcode (zbar)
     * untuk memastikan hasilnya valid dan cocok 100% sebelum dipasang
     * di sini.
     */
    private const CODE128_PATTERNS = [
        '11011001100','11001101100','11001100110','10010011000','10010001100','10001001100',
        '10011001000','10011000100','10001100100','11001001000','11001000100','11000100100',
        '10110011100','10011011100','10011001110','10111001100','10011101100','10011100110',
        '11001110010','11001011100','11001001110','11011100100','11001110100','11101101110',
        '11101001100','11100101100','11100100110','11101100100','11100110100','11100110010',
        '11011011000','11011000110','11000110110','10100011000','10001011000','10001000110',
        '10110001000','10001101000','10001100010','11010001000','11000101000','11000100010',
        '10110111000','10110001110','10001101110','10111011000','10111000110','10001110110',
        '11101110110','11010001110','11000101110','11011101000','11011100010','11011101110',
        '11101011000','11101000110','11100010110','11101101000','11101100010','11100011010',
        '11101111010','11001000010','11110001010','10100110000','10100001100','10010110000',
        '10010000110','10000101100','10000100110','10110010000','10110000100','10011010000',
        '10011000010','10000110100','10000110010','11000010010','11001010000','11110111010',
        '11000010100','10001111010','10100111100','10010111100','10010011110','10111100100',
        '10011110100','10011110010','11110100100','11110010100','11110010010','11011011110',
        '11011110110','11110110110','10101111000','10100011110','10001011110','10111101000',
        '10111100010','11110101000','11110100010','10111011110','10111101110','11101011110',
        '11110101110','11010000100','11010010000','11010011100',
    ];

    // STOP pattern (11 modul) + 2 modul penutup akhir = 13 modul total,
    // sesuai spesifikasi resmi Code128.
    private const CODE128_STOP = '1100011101011';

    /**
     * Encode $text (charset B: ASCII 32 spasi s/d 126 ~) jadi string
     * pola bit "1"/"0". Karakter di luar rentang itu diganti "?"
     * supaya tidak merusak pola (NIS normalnya cuma angka, jadi ini
     * selalu aman dipakai).
     */
    private function encodeCode128B(string $text): string
    {
        $startB = 104;
        $values = [$startB];

        foreach (str_split($text) as $ch) {
            $ord = ord($ch);
            if ($ord < 32 || $ord > 126) {
                $ord = 63; // '?'
            }
            $values[] = $ord - 32;
        }

        $checksum = 0;
        foreach ($values as $i => $v) {
            $checksum += $i === 0 ? $v : $i * $v;
        }
        $values[] = $checksum % 103;

        $bits = '';
        foreach ($values as $v) {
            $bits .= self::CODE128_PATTERNS[$v];
        }

        return $bits . self::CODE128_STOP;
    }

    /**
     * Gambar pola bit ("1"/"0") jadi PNG hitam-putih pakai GD murni,
     * kembalikan sebagai binary PNG siap di-decode Intervention Image.
     */
    private function renderBarcodePng(string $bits, int $moduleWidth, int $heightPx): string
    {
        $width = max(1, strlen($bits) * $moduleWidth);
        $im = imagecreatetruecolor($width, $heightPx);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $width - 1, $heightPx - 1, $white);

        for ($i = 0, $len = strlen($bits); $i < $len; $i++) {
            if ($bits[$i] === '1') {
                $x0 = $i * $moduleWidth;
                imagefilledrectangle($im, $x0, 0, $x0 + $moduleWidth - 1, $heightPx - 1, $black);
            }
        }

        ob_start();
        imagepng($im);
        $data = (string) ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    /**
     * DITAMBAHKAN -- compose kartu BELAKANG (landscape) sebagai SATU
     * gambar raster, baru gambar itu yang diputar 90 derajat.
     *
     * Kenapa begini: CSS transform:rotate() di DomPDF terbukti 3x
     * gagal dengan cara berbeda-beda (konten anak selalu salah
     * hitung ukuran/posisi begitu parent-nya di-rotate) -- padahal
     * layout landscape TANPA rotasi sudah terbukti rapi sempurna.
     * Rotasi gambar RASTER (bukan CSS) tidak punya masalah itu sama
     * sekali.
     *
     * PENTING -- nama method Intervention Image di bawah ini sudah
     * diverifikasi LANGSUNG ke source code Intervention\Image v4
     * (tag 4.1.1): createImage(), insert(), font->filename()/size()/
     * color()/align(), encodeUsingMediaType()->toDataUri().
     *
     * Mengembalikan data URI base64 PNG, atau null kalau gagal
     * (caller WAJIB siapkan fallback kalau null).
     */
    private function buildKartuBelakangImage(Siswa $siswa, ?KartuTemplate $template): ?string
    {
        try {
            // Kanvas landscape (sebelum diputar): 1000 x 630 px, setara
            // kartu 8.56cm x 5.4cm (~117 px/cm).
            $W = 1000;
            $H = 630;

            $manager = new ImageManager(new Driver());
            $canvas = $manager->createImage($W, $H)->fill('#ffffff');

            // Background (kalau ada) -- isi penuh kanvas.
            if ($template?->background_belakang) {
                try {
                    $bgRaw = Storage::disk('r2-public')->get($template->background_belakang);
                    $bg = $manager->decodeBinary($bgRaw)->cover($W, $H);
                    $canvas->insert($bg, 0, 0, 'top-left');
                } catch (\Throwable $e) {
                    Log::warning('Kartu belakang: gagal memuat background', ['error' => $e->getMessage()]);
                }
            }

            $fontBold = storage_path('fonts/PlusJakartaSans-Bold.ttf');
            $fontRegular = storage_path('fonts/PlusJakartaSans-Regular.ttf');

            // Zona aman konten: 0 - 590px dari 1000px (~59%) -- sisanya
            // sengaja dikosongkan untuk panel desain/logo template
            // yang dibuat sekolah.
            $marginX = 40;
            $safeRight = 590;

            // Judul.
            $canvas->text('KARTU TANDA PELAJAR', $marginX, 30, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                $font->size(44);
                $font->color('#111111');
                $font->align('left', 'top');
            });

            // Foto siswa.
            $fotoW = 160;
            $fotoH = 205;
            $fotoY = 100;
            if ($siswa->foto) {
                try {
                    $fotoRaw = Storage::disk('r2-public')->get($siswa->foto);
                    $foto = $manager->decodeBinary($fotoRaw)->cover($fotoW, $fotoH);
                    $canvas->insert($foto, $marginX, $fotoY, 'top-left');
                } catch (\Throwable $e) {
                    Log::warning('Kartu belakang: gagal memuat foto siswa', ['error' => $e->getMessage()]);
                }
            }

            // Data siswa, di sebelah kanan foto -- muat sampai $safeRight.
            // Value yang panjang (nama/alamat) dicoba ditampilkan UTUH
            // dulu di font besar (sampai 3 baris); kalau masih tidak
            // muat, font dikecilkan bertahap; hanya kalau di font
            // minimum pun masih kepanjangan baru dipotong "...".
            $dataX = $marginX + $fotoW + 24;
            $labelFontSize = 18;
            $labelW = (int) ceil($this->measureTextWidth('NIS / NISN', $fontBold, $labelFontSize)) + 14;
            $valueMaxWidth = $safeRight - ($dataX + $labelW) - 8;
            $lineHeight = 24;
            $ttl = trim(($siswa->tempat_lahir ?? '-') . ', ' . ($siswa->tanggal_lahir
                ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d M Y')
                : '-'));

            $rows = [
                ['NAMA', strtoupper($siswa->nama_lengkap)],
                ['NIS / NISN', $siswa->nis . ' / ' . $siswa->nisn],
                ['T.T.L', $ttl],
                ['LEMBAGA', strtoupper($siswa->lembaga->nama ?? '-')],
                ['ALAMAT', strtoupper($siswa->desa ?? $siswa->kecamatan ?? '-')],
            ];

            $rowY = $fotoY + 6;
            foreach ($rows as [$label, $value]) {
                [$lines, $valueFontSize] = $this->fitAndWrapText((string) $value, $fontRegular, 20, 13, $valueMaxWidth, 3);

                $canvas->text($label, $dataX, $rowY, function ($font) use ($fontBold, $labelFontSize) {
                    $font->filename($fontBold);
                    $font->size($labelFontSize);
                    $font->color('#444444');
                    $font->align('left', 'top');
                });

                foreach ($lines as $li => $line) {
                    $prefix = $li === 0 ? ': ' : '  ';
                    $canvas->text($prefix . $line, $dataX + $labelW, $rowY + ($li * $lineHeight), function ($font) use ($fontRegular, $valueFontSize) {
                        $font->filename($fontRegular);
                        $font->size($valueFontSize);
                        $font->color('#111111');
                        $font->align('left', 'top');
                    });
                }

                $rowY += $lineHeight * max(count($lines), 1) + 10;
            }

            // Barcode -- generator GD murni di atas (encodeCode128B +
            // renderBarcodePng), TIDAK butuh Imagick / package apa pun.
            try {
                $bits = $this->encodeCode128B((string) $siswa->nis);
                $moduleWidth = 3;
                $maxBarcodeWidth = $safeRight - $marginX;
                while ($moduleWidth > 2 && strlen($bits) * $moduleWidth > $maxBarcodeWidth) {
                    $moduleWidth--;
                }
                $barcodeRaw = $this->renderBarcodePng($bits, $moduleWidth, 110);
                $barcodeImg = $manager->decodeBinary($barcodeRaw);
                $qrY = min($rowY + 10, $H - 120);
                $canvas->insert($barcodeImg, $marginX, $qrY, 'top-left');
            } catch (\Throwable $e) {
                Log::warning('Kartu belakang: gagal membuat barcode', ['error' => $e->getMessage()]);
            }

            // Putar 90 derajat -- ini SATU-SATUNYA rotasi, dilakukan
            // di level gambar raster, bukan CSS.
            $canvas->rotate(-90);

            return (string) $canvas->encodeUsingMediaType('image/png')->toDataUri();
        } catch (\Throwable $e) {
            Log::error('Kartu belakang: gagal compose gambar', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * DITAMBAHKAN SEMENTARA -- untuk debugging, tampilkan gambar
     * kartu belakang MENTAH (hasil compose Intervention Image)
     * langsung sebagai response gambar, TANPA lewat DomPDF sama
     * sekali. Buka /kartu/debug-belakang/{id} di browser.
     * BOLEH DIHAPUS setelah selesai debugging.
     */
    public function debugKartuBelakang($id)
    {
        $siswa = Siswa::with('lembaga')->findOrFail($id);

        $template = KartuTemplate::where('jenis', 'siswa')
            ->where('lembaga_id', $siswa->lembaga_id)
            ->first()
            ?? KartuTemplate::where('jenis', 'siswa')->first();

        $dataUri = $this->buildKartuBelakangImage($siswa, $template);

        if (!$dataUri) {
            return response('Gagal compose gambar -- cek storage/logs/laravel.log', 500);
        }

        $base64 = explode(',', $dataUri, 2)[1] ?? '';
        $binary = base64_decode($base64);

        return response($binary, 200)->header('Content-Type', 'image/png');
    }

    // ======================
    // CETAK 1 SISWA
    // ======================
    public function cetakSatu($id)
    {
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $siswas = Siswa::with('lembaga')
                    ->where('id', $id)
                    ->get();

        $template = KartuTemplate::where('jenis', 'siswa')
            ->where('lembaga_id', $siswas->first()?->lembaga_id)
            ->first()
            ?? KartuTemplate::where('jenis', 'siswa')->first();

        $kartuBelakangImages = $siswas->mapWithKeys(fn ($s) => [
            $s->id => $this->buildKartuBelakangImage($s, $template),
        ]);

        $pdf = Pdf::loadView('kartu.siswa', [
            'siswas'   => $siswas,
            'template' => $template,
            'kartuBelakangImages' => $kartuBelakangImages,
        ]);

        return $pdf->stream('kartu-siswa.pdf');
    }

    // ======================
    // CETAK MASSAL
    // ======================
    public function cetakMassal(Request $request)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        $ids = explode(',', $request->ids);

        $siswas = Siswa::with('lembaga')
            ->whereIn('id', $ids)
            ->orderBy('nama_lengkap') // optional biar rapi
            ->get();

        $template = KartuTemplate::where('jenis', 'siswa')
            ->where('lembaga_id', $siswas->first()?->lembaga_id)
            ->first()
            ?? KartuTemplate::where('jenis', 'siswa')->first();

        $kartuBelakangImages = $siswas->mapWithKeys(fn ($s) => [
            $s->id => $this->buildKartuBelakangImage($s, $template),
        ]);

        $pdf = Pdf::loadView('kartu.siswa', [
            'siswas'   => $siswas,
            'template' => $template,
            'kartuBelakangImages' => $kartuBelakangImages,
        ]);

        return $pdf->stream('kartu-massal.pdf');
    }

    // ======================
    // CETAK PEGAWAI
    // ======================
    public function cetakPegawai(Request $request)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        $ids = explode(',', $request->ids);

        $pegawais = Pegawai::with('lembagas')
            ->whereIn('id', $ids)
            ->orderBy('nama')
            ->get();

        $lembagaId = $pegawais->first()?->lembagas?->first()?->id;

        $template = \App\Models\KartuTemplate::where('jenis', 'pegawai')
            ->where('lembaga_id', $lembagaId)
            ->first()
            ?? \App\Models\KartuTemplate::where('jenis', 'pegawai')->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kartu.pegawai', [
            'pegawais' => $pegawais,
            'template' => $template
        ])->setPaper([0, 0, 850, 567], 'landscape'); // 20x30 cm

        return $pdf->stream('kartu-pegawai.pdf');
    }
}
