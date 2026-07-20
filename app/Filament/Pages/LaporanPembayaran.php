<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Livewire\WithPagination;

use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPembayaranExport;

class LaporanPembayaran extends Page implements HasForms
{
    use WithPagination, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Laporan Pembayaran';
    protected static string $view = 'filament.pages.laporan-pembayaran';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanPembayaran');
    }

    protected $paginationTheme = 'tailwind';

    // 🔥 FILTER STATE
    public $tahun_ajaran_id;
    public $lembaga_id;
    public $kelas_id;
    public $siswa_id;
    public $jenis_tagihan_id;
    public $tampilkan_alumni = false;

    // 🔥 MODAL
    public $selectedTagihan = null;
    public $showModal = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleAlumni')
                ->label(fn () => $this->tampilkan_alumni ? 'Sembunyikan Alumni' : 'Tampilkan Alumni')
                ->icon(fn () => $this->tampilkan_alumni ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn () => $this->tampilkan_alumni ? 'gray' : 'warning')
                ->action(fn () => $this->tampilkan_alumni = ! $this->tampilkan_alumni),

            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new LaporanPembayaranExport([
                            'tahun_ajaran_id' => $this->tahun_ajaran_id,
                            'lembaga_id' => $this->lembaga_id,
                            'kelas_id' => $this->kelas_id,
                            'siswa_id' => $this->siswa_id,
                            'jenis_tagihan_id' => $this->jenis_tagihan_id,
                            'tampilkan_alumni' => $this->tampilkan_alumni,
                        ]),
                        'laporan-pembayaran.xlsx'
                    );
                }),
        ];
    }


    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(5)
                ->schema([

                    Forms\Components\Select::make('tahun_ajaran_id')
                        ->label('Tahun Ajaran')
                        ->placeholder('Semua Tahun Ajaran')
                        ->searchable()
                        ->options(\App\Models\TahunAjaran::pluck('nama', 'id'))
                        ->live(),

                    Forms\Components\Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->placeholder('Semua Lembaga')
                        ->searchable()
                        ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                        ->live(),

                    Forms\Components\Select::make('kelas_id')
                        ->label('Kelas')
                        ->placeholder('Semua Kelas')
                        ->searchable()
                        ->options(\App\Models\Kelas::pluck('nama', 'id'))
                        ->live(),

                    Forms\Components\Select::make('siswa_id')
                        ->label('Siswa')
                        ->placeholder('Semua Siswa')
                        ->searchable()
                        ->options(\App\Models\Siswa::pluck('nama_lengkap', 'id'))
                        ->live(),

                    Forms\Components\Select::make('jenis_tagihan_id')
                        ->label('Jenis Pembayaran')
                        ->placeholder('Semua Jenis')
                        ->searchable()
                        ->options(\App\Models\JenisTagihan::pluck('nama', 'id'))
                        ->live(),
                ])
                ->columnSpanFull(),
        ];
    }

    public function updating($field)
    {
        $this->resetPage();
    }

    /**
     * 🔹 DATA SPP (FIX PAGINATION)
     */
    public function getSppData()
    {
        return Siswa::query()
            ->when($this->tahun_ajaran_id, fn ($q) =>
                $q->whereHas('tagihans', fn ($q) =>
                    $q->where('tahun_ajaran_id', $this->tahun_ajaran_id)))

            ->when($this->lembaga_id, fn ($q) =>
                $q->where('lembaga_id', $this->lembaga_id))

            ->when($this->kelas_id, fn ($q) =>
                $q->where('kelas_id', $this->kelas_id))

            ->when($this->siswa_id, fn ($q) =>
                $q->where('id', $this->siswa_id))

            ->when(! $this->tampilkan_alumni, fn ($q) =>
                $q->where('status_siswa', 'Aktif'))

            ->with([
                'kelas',
                'tagihans.pembayarans',
                'tagihans.jenisTagihan',
            ])
            ->orderBy('nama_lengkap')

            // ✅ FIX DI SINI
            ->paginate(10, ['*'], 'sppPage');
    }

/**
 * 🔹 DATA UMUM (FINAL FIX - PAKAI TAGIHAN)
 */
public function getUmumData()
{
    return \App\Models\Tagihan::with([
            'siswa.kelas',
            'ppdb.lembaga',
            'jenisTagihan',
            'pembayarans'
        ])

        // NON SPP
        ->whereHas('jenisTagihan', fn ($q) =>
            $q->where('is_bulanan', false))

        ->when($this->jenis_tagihan_id, fn ($q) =>
            $q->where('jenis_tagihan_id', $this->jenis_tagihan_id)
        )

        // FILTER TAHUN AJARAN
        ->when($this->tahun_ajaran_id, fn ($q) =>
            $q->where('tahun_ajaran_id', $this->tahun_ajaran_id))

        // FILTER LEMBAGA
        ->when($this->lembaga_id, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('siswa', fn ($s) =>
                            $s->where('lembaga_id', $this->lembaga_id))
                        ->orWhereHas('ppdb', fn ($p) =>
                            $p->where('lembaga_id', $this->lembaga_id));
                });
            })

        // FILTER KELAS
        ->when($this->kelas_id, fn ($q) =>
            $q->whereHas('siswa', fn ($s) =>
                $s->where('kelas_id', $this->kelas_id)))

        // FILTER SISWA
        ->when($this->siswa_id, fn ($q) =>
            $q->where('siswa_id', $this->siswa_id))

        // Sembunyikan alumni secara default -- tagihan PPDB (belum
        // punya siswa) tetap selalu tampil, bukan bagian dari alumni.
        ->when(! $this->tampilkan_alumni, fn ($q) =>
            $q->where(function ($sub) {
                $sub->whereDoesntHave('siswa')
                    ->orWhereHas('siswa', fn ($s) =>
                        $s->where('status_siswa', 'Aktif'));
            })
        )

        ->latest()

        // 🔥 PENTING: pagination tidak bentrok
        ->paginate(10, ['*'], 'umumPage');
}

    /**
     * 🔥 SUMMARY (NON SPP)
     */
    public function getSummary()
    {
        $tagihanQuery = Tagihan::query()

            // ✅ TANPA FILTER SPP (SEMUA MASUK)

            ->when($this->tahun_ajaran_id, fn ($q) =>
                $q->where('tahun_ajaran_id', $this->tahun_ajaran_id))

            ->when($this->jenis_tagihan_id, fn ($q) =>
                    $q->where('jenis_tagihan_id', $this->jenis_tagihan_id)
                )

            ->where(function ($query) {
        $query->whereHas('siswa', function ($q) {
            $q->when($this->lembaga_id, fn ($q) =>
                    $q->where('lembaga_id', $this->lembaga_id))
            ->when($this->kelas_id, fn ($q) =>
                    $q->where('kelas_id', $this->kelas_id))
            ->when($this->siswa_id, fn ($q) =>
                    $q->where('id', $this->siswa_id));
        })

        ->orWhereHas('ppdb', function ($p) {
            $p->when($this->lembaga_id, fn ($q) =>
                    $q->where('lembaga_id', $this->lembaga_id));
            // PPDB tidak punya kelas & siswa_id → skip
        });
        });

    $pembayaranQuery = Pembayaran::query()

        ->when($this->tahun_ajaran_id, fn ($q) =>
            $q->whereHas('tagihan', fn ($q) =>
                $q->where('tahun_ajaran_id', $this->tahun_ajaran_id)))

        ->when($this->jenis_tagihan_id, fn ($q) =>
            $q->whereHas('tagihan', fn ($t) =>
                $t->where('jenis_tagihan_id', $this->jenis_tagihan_id)
            )
        )

        ->whereHas('tagihan', function ($t) {
    $t->where(function ($query) {
        $query->whereHas('siswa', function ($q) {
            $q->when($this->lembaga_id, fn ($q) =>
                    $q->where('lembaga_id', $this->lembaga_id))
              ->when($this->kelas_id, fn ($q) =>
                    $q->where('kelas_id', $this->kelas_id))
              ->when($this->siswa_id, fn ($q) =>
                    $q->where('id', $this->siswa_id));
        })

        ->orWhereHas('ppdb', function ($p) {
            $p->when($this->lembaga_id, fn ($q) =>
                    $q->where('lembaga_id', $this->lembaga_id));
        });
    });
});

    $totalTagihan = $tagihanQuery->sum('nominal');
    $totalDibayar = $pembayaranQuery->sum('nominal');
    $totalTunggakan = $totalTagihan - $totalDibayar;

    return [
        'tagihan' => $totalTagihan,
        'dibayar' => $totalDibayar,
        'tunggakan' => $totalTunggakan,
    ];
}

    public function lihatRiwayat($id)
    {
        $this->selectedTagihan = Tagihan::with('pembayarans')->find($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTagihan = null;
    }
}