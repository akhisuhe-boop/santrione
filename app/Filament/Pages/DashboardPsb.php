<?php

namespace App\Filament\Pages;

use App\Models\Ppdb;
use App\Models\Lembaga;
use App\Models\TahunAjaran;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;

class DashboardPsb extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.dashboard-psb';

    protected static ?string $navigationGroup = 'PSB';

    protected static ?string $title = 'Dashboard PSB';

    protected static ?string $navigationLabel = 'Dashboard PSB';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_DashboardPsb');
    }

    public ?array $data = [];

    public ?string $tahunAjaranNama = null;

    public function mount(): void
    {
        $this->tahunAjaranNama = TahunAjaran::aktif()?->nama;

        $this->form->fill([
            'tahun_ajaran_nama' => $this->tahunAjaranNama,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tahun_ajaran_nama')
                    ->label('Tahun Ajaran')
                    ->options(
                        TahunAjaran::query()
                            ->select('nama')
                            ->distinct()
                            ->orderByDesc('nama')
                            ->pluck('nama', 'nama')
                    )
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->tahunAjaranNama = $state)
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function baseQueryFull()
    {
        return Ppdb::query()
            ->when(
                $this->tahunAjaranNama,
                fn ($q) => $q->whereHas('tahunAjaran', fn ($sub) => $sub->where('nama', $this->tahunAjaranNama))
            );
    }

    public function getTotalPendaftarProperty(): int
    {
        return $this->baseQueryFull()->count();
    }

    public function getStatusBreakdownProperty(): array
    {
        $labels = [
            'draft' => 'Draft',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'formulir' => 'Isi Formulir',
            'upload_berkas' => 'Upload Berkas',
            'verifikasi_berkas' => 'Verifikasi Berkas',
            'tes' => 'Tes Seleksi',
            'lulus' => 'Lulus',
            'daftar_ulang' => 'Daftar Ulang',
            'aktif' => 'Aktif (Jadi Siswa)',
        ];

        $labelTidakLulus = 'Tidak Lulus';

        // Urutan tahapan jalur SUKSES (dipakai untuk hitung kumulatif:
        // "berapa yang PERNAH mencapai tahap ini", bukan cuma yang
        // SEDANG persis di situ sekarang -- karena status cuma nyimpen
        // posisi TERAKHIR, begitu maju ke tahap berikutnya jejak tahap
        // sebelumnya jangan sampai hilang dari laporan ini).
        $urutan = [
            'draft' => 0,
            'menunggu_pembayaran' => 1,
            'formulir' => 2,
            'upload_berkas' => 3,
            'verifikasi_berkas' => 4,
            'tes' => 5,
            'lulus' => 6,
            'daftar_ulang' => 7,
            'aktif' => 8,
        ];

        $counts = $this->baseQueryFull()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tidakLulusCount = $counts['tidak_lulus'] ?? 0;

        $result = [];

        foreach ($labels as $key => $label) {

            $ambangBatas = $urutan[$key];

            $total = 0;

            foreach ($urutan as $statusLain => $urutanLain) {
                if ($urutanLain >= $ambangBatas) {
                    $total += $counts[$statusLain] ?? 0;
                }
            }

            // "Tidak Lulus" tetap sempat melalui Draft s.d Tes Seleksi
            // sebelum akhirnya gagal -- tapi TIDAK ikut dihitung di
            // Lulus/Daftar Ulang/Aktif (karena memang tidak lanjut).
            if ($ambangBatas <= $urutan['tes']) {
                $total += $tidakLulusCount;
            }

            $result[] = [
                'key' => $key,
                'label' => $label,
                'total' => $total,
            ];

            if ($key === 'lulus') {
                $result[] = [
                    'key' => 'tidak_lulus',
                    'label' => $labelTidakLulus,
                    'total' => $tidakLulusCount,
                ];
            }
        }

        return $result;
    }

    public function getPerLembagaProperty(): array
    {
        $rows = $this->baseQueryFull()
            ->join('lembagas', 'lembagas.id', '=', 'ppdbs.lembaga_id')
            ->selectRaw("lembagas.nama as lembaga, count(*) as total,
                sum(case when ppdbs.jenis_kelamin = 'L' then 1 else 0 end) as laki_laki,
                sum(case when ppdbs.jenis_kelamin = 'P' then 1 else 0 end) as perempuan")
            ->groupBy('lembagas.nama')
            ->orderByDesc('total')
            ->get();

        return $rows->toArray();
    }

    /**
     * Untuk field kategori (bukan angka) seperti pekerjaan, pendidikan,
     * penghasilan, dan daerah — "rata-rata" tidak berlaku secara matematis.
     * Yang ditampilkan adalah nilai yang PALING BANYAK muncul (modus).
     */
    protected function terbanyakPerLembaga(string $kolom): array
    {
        $lembagas = Lembaga::orderBy('nama')->get();

        $result = [];

        foreach ($lembagas as $lembaga) {

            $top = $this->baseQueryFull()
                ->where('lembaga_id', $lembaga->id)
                ->whereNotNull($kolom)
                ->where($kolom, '!=', '')
                ->selectRaw("{$kolom} as val, count(*) as total")
                ->groupBy($kolom)
                ->orderByDesc('total')
                ->first();

            if (!$top) {
                continue;
            }

            $result[] = [
                'lembaga' => $lembaga->nama,
                'nilai' => $top->val,
                'total' => $top->total,
            ];
        }

        return $result;
    }

    public function getAsalSekolahTerbanyakProperty(): array
    {
        return $this->terbanyakPerLembaga('asal_sekolah');
    }

    public function getPenghasilanTerbanyakProperty(): array
    {
        return $this->terbanyakPerLembaga('penghasilan_ayah');
    }

    public function getPekerjaanTerbanyakProperty(): array
    {
        return $this->terbanyakPerLembaga('pekerjaan_ayah');
    }

    public function getPendidikanTerbanyakProperty(): array
    {
        return $this->terbanyakPerLembaga('pendidikan_ayah');
    }

    public function getDaerahTerbanyakProperty(): array
    {
        return $this->terbanyakPerLembaga('kecamatan');
    }

    public function getPembayaranBreakdownProperty(): array
    {
        $ppdbs = $this->baseQueryFull()->get(['id', 'siswa_id']);

        $jenisTagihanPendaftaran = \App\Models\JenisTagihan::where('tipe_sistem', 'pendaftaran_ppdb')->first();

        $ppdbIds = $ppdbs->pluck('id');
        $siswaIds = $ppdbs->pluck('siswa_id')->filter()->values();

        // Tagihan yang MASIH nyangkut ke ppdb_id (belum/belum sempat diaktifkan jadi siswa)
        $tagihanViaPpdb = \App\Models\Tagihan::whereIn('ppdb_id', $ppdbIds)
            ->whereNotNull('ppdb_id')
            ->get()
            ->groupBy('ppdb_id')
            ->map(fn ($group) => $group->sortByDesc('created_at')->first());

        // Tagihan yang SUDAH pindah ke siswa_id (setelah "Aktifkan Siswa"),
        // dicocokkan balik ke ppdb_id lewat siswa_id -- supaya siswa yang
        // sudah aktif tetap kelihatan riwayat pembayaran pendaftarannya,
        // bukan malah nongol "Belum Ada Tagihan".
        $tagihanViaSiswa = collect();

        if ($siswaIds->isNotEmpty() && $jenisTagihanPendaftaran) {
            $tagihanViaSiswa = \App\Models\Tagihan::whereIn('siswa_id', $siswaIds)
                ->where('jenis_tagihan_id', $jenisTagihanPendaftaran->id)
                ->get()
                ->keyBy('siswa_id');
        }

        $belum = 0;
        $sebagian = 0;
        $lunas = 0;
        $belumAdaTagihan = 0;

        foreach ($ppdbs as $ppdb) {

            $t = $tagihanViaPpdb->get($ppdb->id)
                ?? ($ppdb->siswa_id ? $tagihanViaSiswa->get($ppdb->siswa_id) : null);

            if (!$t) {
                $belumAdaTagihan++;
                continue;
            }

            match ($t->status) {
                'lunas' => $lunas++,
                'sebagian' => $sebagian++,
                default => $belum++,
            };
        }

        return [
            'lunas' => $lunas,
            'sebagian' => $sebagian,
            'belum' => $belum,
            'belum_ada_tagihan' => $belumAdaTagihan,
        ];
    }

    public function getPendaftarTerbaruProperty()
    {
        return $this->baseQueryFull()
            ->with('lembaga')
            ->latest('created_at')
            ->limit(8)
            ->get();
    }
}
