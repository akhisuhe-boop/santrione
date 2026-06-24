<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Lembaga;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Yayasan;

class LaporanPembayaranExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [

            /**
             * =========================
             * SHEET 1: SPP
             * =========================
             */
            new class($this->filters) implements FromView, ShouldAutoSize, WithTitle {

                protected $filters;

                public function __construct($filters)
                {
                    $this->filters = $filters;
                }

                public function title(): string
                {
                    return 'Pembayaran SPP';
                }

                public function view(): View
                {
                    $f = $this->filters;

                    // 🔥 DATA SPP
                    $data = Siswa::query()
                        ->when($f['tahun_ajaran_id'] ?? null, fn ($q) =>
                            $q->whereHas('tagihans', fn ($q) =>
                                $q->where('tahun_ajaran_id', $f['tahun_ajaran_id'])))

                        ->when($f['lembaga_id'] ?? null, fn ($q) =>
                            $q->where('lembaga_id', $f['lembaga_id']))

                        ->when($f['kelas_id'] ?? null, fn ($q) =>
                            $q->where('kelas_id', $f['kelas_id']))

                        ->when($f['siswa_id'] ?? null, fn ($q) =>
                            $q->where('id', $f['siswa_id']))

                        ->with([
                            'kelas',
                            'lembaga.yayasan',
                            'tagihans.pembayarans',
                            'tagihans.jenisTagihan'
                        ])
                        ->orderBy('nama_lengkap')
                        ->get();

                    // 🔥 HEADER DATA (NO FALLBACK!)
                    $lembaga = null;
                    $yayasan = 'All';

                    // 🔥 YAYASAN (WAJIB SELALU ADA)
                    $yayasan = Yayasan::query()->value('nama') ?? '-';

                    // 🔥 LEMBAGA (HANYA JIKA FILTER)
                    $lembaga = null;
                    if (!empty($f['lembaga_id'])) {
                        $lembaga = \App\Models\Lembaga::find($f['lembaga_id']);
                    }

                    // 🔥 TAHUN AJARAN
                    $tahunAjaran = TahunAjaran::query()
                        ->where('id', $f['tahun_ajaran_id'] ?? null)
                        ->value('nama');

                    // 🔥 KELAS
                    $kelas = Kelas::query()
                        ->where('id', $f['kelas_id'] ?? null)
                        ->value('nama');

                    return view('exports.sheet-spp', [
                        'data' => $data,
                        'yayasan' => $yayasan,
                        'lembaga' => $lembaga ? $lembaga->nama : 'All',
                        'tahunAjaran' => $tahunAjaran ?? 'All',
                        'kelas' => $kelas ?? 'All',
                    ]);
                }
            },


            /**
             * =========================
             * SHEET 2: UMUM
             * =========================
             */
            new class($this->filters) implements FromView, ShouldAutoSize, WithTitle {

                protected $filters;

                public function __construct($filters)
                {
                    $this->filters = $filters;
                }

                public function title(): string
                {
                    return 'Pembayaran Umum';
                }

                public function view(): View
                {
                    $f = $this->filters;

                    // 🔥 DATA UMUM
                    $data = Tagihan::with([
                            'siswa.kelas',
                            'siswa.lembaga.yayasan',
                            'ppdb.lembaga',
                            'jenisTagihan',
                            'pembayarans'
                        ])
                        ->whereHas('jenisTagihan', fn ($q) =>
                            $q->where('nama', '!=', 'SPP'))

                        ->when($f['jenis_tagihan_id'] ?? null, fn ($q) =>
                            $q->where('jenis_tagihan_id', $f['jenis_tagihan_id']))

                        ->when($f['tahun_ajaran_id'] ?? null, fn ($q) =>
                            $q->where('tahun_ajaran_id', $f['tahun_ajaran_id']))

                        ->when($f['lembaga_id'] ?? null, function ($q) use ($f) {
                            $q->where(function ($query) use ($f) {
                                $query->whereHas('siswa', fn ($s) =>
                                    $s->where('lembaga_id', $f['lembaga_id']))
                                ->orWhereHas('ppdb', fn ($p) =>
                                    $p->where('lembaga_id', $f['lembaga_id']));
                            });
                        })

                        ->when($f['kelas_id'] ?? null, fn ($q) =>
                            $q->whereHas('siswa', fn ($s) =>
                                $s->where('kelas_id', $f['kelas_id'])))

                        ->when($f['siswa_id'] ?? null, fn ($q) =>
                            $q->where('siswa_id', $f['siswa_id']))

                        ->latest()
                        ->get();

                    // 🔥 HEADER DATA (NO FALLBACK!)
                    $lembaga = null;
                    $yayasan = 'All';

                    // 🔥 YAYASAN (WAJIB SELALU ADA)
                    $yayasan = Yayasan::query()->value('nama') ?? '-';

                    // 🔥 LEMBAGA (HANYA JIKA FILTER)
                    $lembaga = null;
                    if (!empty($f['lembaga_id'])) {
                        $lembaga = \App\Models\Lembaga::find($f['lembaga_id']);
                    }

                    // 🔥 TAHUN AJARAN
                    $tahunAjaran = TahunAjaran::query()
                        ->where('id', $f['tahun_ajaran_id'] ?? null)
                        ->value('nama');

                    // 🔥 KELAS
                    $kelas = Kelas::query()
                        ->where('id', $f['kelas_id'] ?? null)
                        ->value('nama');

                    return view('exports.sheet-umum', [
                        'data' => $data,
                        'yayasan' => $yayasan,
                        'lembaga' => $lembaga ? $lembaga->nama : 'All',
                        'tahunAjaran' => $tahunAjaran ?? 'All',
                        'kelas' => $kelas ?? 'All',
                    ]);
                }
            }

        ];
    }
}