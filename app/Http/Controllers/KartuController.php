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
     * menabrak/menumpuk tepi kanan kartu seperti yang terjadi
     * sebelumnya (root cause kartu belakang terlihat berantakan:
     * teks panjang ditulis apa adanya di ukuran font tetap 17px
     * tanpa pernah dicek muat atau tidak sampai ke $safeRight).
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
     * PENTING -- nama method di bawah ini sudah diverifikasi LANGSUNG
     * ke source code Intervention\Image v4 (github.com/Intervention/image,
     * tag 4.1.1), bukan tebakan lagi -- percobaan pertama gagal karena
     * beberapa nama method meleset (create() -> createImage(),
     * place() -> insert() dengan urutan parameter beda, font
     * filename()/size()/color()/align()/valign() -> setFilepath()/
     * setSize()/setColor()/setAlignmentHorizontal()/
     * setAlignmentVertical(), toPng() -> encodeUsingMediaType()
     * ->toDataUri()).
     *
     * PERBAIKAN (rapikan kartu belakang) -- root cause tampilan
     * berantakan: (1) nilai field (nama/alamat/dll) ditulis di ukuran
     * font TETAP tanpa pernah dicek muat atau tidak, jadi teks
     * panjang menabrak/menumpuk elemen lain -> sekarang dipas-kan via
     * fitText(); (2) label terpendek ("Nama") vs terpanjang ("NIS/
     * NISN") berbagi $labelW yang sama sehingga titik dua tidak rata
     * dan nilai NIS/NISN nyaris menempel labelnya -> $labelW
     * dilebarkan; (3) jarak antar baris & antara foto-barcode
     * terlalu rapat -> dilebarkan sedikit. (drawLine/drawRectangle
     * sempat dicoba untuk garis pemisah & bingkai foto tapi DIHAPUS
     * lagi karena bikin exception di compose -- lihat catatan di
     * bawah kalau mau dicoba lagi, verifikasi dulu versi Intervention
     * Image yang ter-install.)
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

            // Zona aman konten: 0 - 590px dari 1000px (~59%, menyamai
            // table.back-layout{width:60%} di versi CSS) -- sisanya
            // (590 - 1000px) sengaja dikosongkan untuk panel
            // desain/logo template yang dibuat sekolah.
            $marginX = 40;
            $safeRight = 590;

            // Judul -- dibesarkan (26 -> 44) karena sebelumnya
            // terlalu kecil setelah kartu di-scale-down ke ukuran
            // cetak sebenarnya (~1/3.1x).
            $canvas->text('KARTU TANDA PELAJAR', $marginX, 30, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                    $font->size(44);
                    $font->color('#111111');
                    $font->align('left', 'top');
            });

            // Foto siswa -- dibesarkan (150x190 -> 160x205).
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

            // Barcode -- DIGANTI dari milon/barcode (paket ini TIDAK
            // ter-install di composer.json project, itu sebabnya
            // sebelumnya selalu gagal diam-diam dan tidak pernah
            // tampil) ke QR code pakai simplesoftwareio/simple-qrcode,
            // paket yang sudah dipakai & terbukti jalan di kartu depan.
            $qrY = min($rowY + 10, $H - 160);
            try {
                $qrRaw = \QrCode::format('png')->size(150)->generate($siswa->nis);
                $qr = $manager->decodeBinary($qrRaw);
                $canvas->insert($qr, $marginX, $qrY, 'top-left');
            } catch (\Throwable $e) {
                Log::warning('Kartu belakang: gagal membuat QR/barcode', ['error' => $e->getMessage()]);
            }

            // Putar 90 derajat -- ini SATU-SATUNYA rotasi, dilakukan
            // di level gambar raster, bukan CSS. Arah (-90) dipilih
            // supaya sama dengan arah rotate(90deg) CSS yang sudah
            // dikonfirmasi benar strukturnya di percobaan sebelumnya.
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

        // Ambil bagian base64 setelah koma, decode jadi binary PNG asli.
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
