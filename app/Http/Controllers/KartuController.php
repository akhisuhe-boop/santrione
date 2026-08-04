<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\KartuTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KartuController extends Controller
{
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

        $pdf = Pdf::loadView('kartu.siswa', [
            'siswas'   => $siswas,
            'template' => $template
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

        $pdf = Pdf::loadView('kartu.siswa', [
            'siswas'   => $siswas,
            'template' => $template
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