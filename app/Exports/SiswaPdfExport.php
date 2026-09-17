<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;

class SiswaPdfExport
{
    protected $lembaga_id;
    protected $kelas_id;

    public function __construct($lembaga_id = null, $kelas_id = null)
    {
        $this->lembaga_id = $lembaga_id;
        $this->kelas_id   = $kelas_id;
    }

    public function download()
    {
        // DITAMBAHKAN (16 Sep 2026) -- sama seperti SiswaExport.php,
        // sebelumnya export PDF ini juga tidak ada filter status,
        // siswa Lulus/Pindah ikut terbawa.
        $query = Siswa::query()->where('status_siswa', 'Aktif');

        if ($this->lembaga_id) {
            $query->where('lembaga_id', $this->lembaga_id);
        }

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        $siswas = $query
        ->with(['kelas'])
        ->get();

        $lembaga = $this->lembaga_id
            ? Lembaga::find($this->lembaga_id)
            : null;

        $kelas = $this->kelas_id
            ? Kelas::find($this->kelas_id)
            : null;

        $pdf = Pdf::loadView('exports.siswa-pdf', [
            'siswas'  => $siswas,
            'lembaga' => $lembaga,
            'kelas'   => $kelas,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('data-siswa.pdf');
    }
}