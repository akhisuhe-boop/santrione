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
     * Kecilkan ukuran font bertahap sampai muat di $maxWidthPx; kalau
     * di ukuran minimum pun masih kepanjangan, potong dengan "...".
     * Mengembalikan [teks_final, ukuran_font_final].
     */
    private function fitText(string $text, string $fontFile, int $maxFontSize, int $minFontSize, float $maxWidthPx): array
    {
        $size = $maxFontSize;
        while ($size > $minFontSize && $this->measureTextWidth($text, $fontFile, $size) > $maxWidthPx) {
            $size--;
        }
        if ($this->measureTextWidth($text, $fontFile, $size) > $maxWidthPx) {
            while (mb_strlen($text) > 3 && $this->measureTextWidth($text . '...', $fontFile, $size) > $maxWidthPx) {
                $text = mb_substr($text, 0, -1);
            }
            $text .= '...';
        }
        return [$text, $size];
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

            // Judul.
            $canvas->text('KARTU TANDA PELAJAR', $marginX, 34, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                    $font->size(26);
                    $font->color('#111111');
                    $font->align('left', 'top');
            });

            // Foto siswa.
            $fotoW = 150;
            $fotoH = 190;
            $fotoY = 96;
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
            // labelW dilebarkan (120 -> 140) supaya label terpanjang
            // ("NIS/NISN") tidak pernah menabrak kolom nilai, dan
            // setiap baris punya jarak vertikal lebih lega (32 -> 36px)
            // supaya tidak terlihat padat seperti sebelumnya.
            $dataX = $marginX + $fotoW + 24;
            $labelW = 140;
            $valueMaxWidth = $safeRight - ($dataX + $labelW) - 8;
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

            $rowY = $fotoY + 4;
            foreach ($rows as [$label, $value]) {
                // Nilai (bukan label) yang dipas-kan ukuran fontnya --
                // ini bagian yang sebelumnya tidak ada sama sekali,
                // jadi nama/alamat panjang menabrak/menumpuk elemen
                // lain di sisi kanan kartu.
                [$fitValue, $fitSize] = $this->fitText((string) $value, $fontRegular, 16, 11, $valueMaxWidth);

                $canvas->text($label, $dataX, $rowY, function ($font) use ($fontBold) {
                    $font->filename($fontBold);
                    $font->size(14);
                    $font->color('#444444');
                    $font->align('left', 'top');
                });
                $canvas->text(': ' . $fitValue, $dataX + $labelW, $rowY, function ($font) use ($fontRegular, $fitSize) {
                    $font->filename($fontRegular);
                    $font->size($fitSize);
                    $font->color('#111111');
                    $font->align('left', 'top');
                });
                $rowY += 36;
            }

            // Barcode, di bawah foto -- diturunkan sedikit (18 -> 26px
            // jarak) supaya tidak menempel ke bingkai foto baru.
            try {
                $barcodeBase64 = \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($siswa->nis, 'C128', 2, 2);
                $barcodeRaw = base64_decode($barcodeBase64);
                $barcode = $manager->decodeBinary($barcodeRaw);
                $barcodeMaxW = $safeRight - $marginX;
                $barcode->resize(width: min(380, $barcodeMaxW), height: null);
                $canvas->insert($barcode, $marginX, $fotoY + $fotoH + 26, 'top-left');
            } catch (\Throwable $e) {
                Log::warning('Kartu belakang: gagal membuat barcode', ['error' => $e->getMessage()]);
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
