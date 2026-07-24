<?php

namespace App\Http\Controllers;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiController extends Controller
{
    /**
     * Ambil data pembayaran lengkap
     */
    protected function pembayaran(Pembayaran $pembayaran): Pembayaran
    {
        abort_if($pembayaran->status !== 'sukses', 404);
    
        return $pembayaran->load([
            'tagihan.jenisTagihan',
            'tagihan.rekening',
    
            'siswa.kelas',
            'siswa.lembaga.yayasan',
    
            'ppdb.lembaga.yayasan',
        ]);
    }

    /**
     * Preview
     */
    public function show(Pembayaran $pembayaran)
    {
        return view('kwitansi.show', [
            'pembayaran' => $this->pembayaran($pembayaran),
        ]);
    }

    /**
     * Thermal 58 mm
     */
    public function thermal58(Pembayaran $pembayaran)
    {
        return view('kwitansi.thermal58', [
            'pembayaran' => $this->pembayaran($pembayaran),
        ]);
    }

    /**
     * Thermal 80 mm
     */
    public function thermal80(Pembayaran $pembayaran)
    {
        return view('kwitansi.thermal80', [
            'pembayaran' => $this->pembayaran($pembayaran),
        ]);
    }

    /**
     * Dot Matrix 3 Ply
     */
    public function dotmatrix(Pembayaran $pembayaran)
    {
        return view('kwitansi.dotmatrix', [
            'pembayaran' => $this->pembayaran($pembayaran),
        ]);
    }
    
    /**
     * Download PDF
     */
    public function pdf(Pembayaran $pembayaran)
    {
        $pembayaran = $this->pembayaran($pembayaran);
    
        $pdf = Pdf::loadView('kwitansi.pdf', [
            'pembayaran' => $pembayaran,
        ]);

        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $fontMetrics->registerFont(
            ['family' => 'Plus Jakarta Sans', 'style' => 'normal', 'weight' => 'normal'],
            storage_path('fonts/PlusJakartaSans-Regular.ttf')
        );
        $fontMetrics->registerFont(
            ['family' => 'Plus Jakarta Sans', 'style' => 'normal', 'weight' => 'bold'],
            storage_path('fonts/PlusJakartaSans-Bold.ttf')
        );

        return $pdf
        ->setPaper([0, 0, 226.77, 420])
        ->stream('Kwitansi-'.$pembayaran->kode.'.pdf');
    
        // Kalau ingin tampil di browser, ganti menjadi:
        // ->stream('Kwitansi-'.$pembayaran->kode.'.pdf');
    }
    
    /**
     * Cetak Printer
     */
    
    public function cetak(Pembayaran $pembayaran)
    {
        $pembayaran = $this->pembayaran($pembayaran);
    
        // Ambil lembaga dari siswa atau PPDB
        $lembaga = $pembayaran->siswa?->lembaga
            ?? $pembayaran->ppdb?->lembaga;
    
        $printer = $lembaga?->printer_kwitansi ?? 'thermal80';
    
        return match ($printer) {
    
            'thermal58' => view('kwitansi.thermal58', [
                'pembayaran' => $pembayaran,
            ]),
    
            'dotmatrix' => view('kwitansi.dotmatrix', [
                'pembayaran' => $pembayaran,
            ]),
    
            default => view('kwitansi.thermal80', [
                'pembayaran' => $pembayaran,
            ]),
        };
    }
    
}