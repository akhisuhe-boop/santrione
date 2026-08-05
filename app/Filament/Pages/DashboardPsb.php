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

    public ?int $tahunAjaranId = null;

    public function mount(): void
    {
        $this->tahunAjaranId = TahunAjaran::aktif()?->id;

        $this->form->fill([
            'tahun_ajaran_id' => $this->tahunAjaranId,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::orderByDesc('id')->pluck('nama', 'id'))
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->tahunAjaranId = $state)
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function baseQueryFull()
    {
        return Ppdb::query()
            ->when($this->tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $this->tahunAjaranId));
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
            'verifikasi_berkas' => 'Verifikasi Berkas',
            'tes' => 'Tes Seleksi',
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'daftar_ulang' => 'Daftar Ulang',
            'aktif' => 'Aktif (Jadi Siswa)',
        ];

        $counts = $this->baseQueryFull()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];

        foreach ($labels as $key => $label) {
            $result[] = [
                'key' => $key,
                'label' => $label,
                'total' => $counts[$key] ?? 0,
            ];
        }

        return $result;
    }

    public function getPerLembagaProperty(): array
    {
        return $this->baseQueryFull()
            ->join('lembagas', 'lembagas.id', '=', 'ppdbs.lembaga_id')
            ->selectRaw('lembagas.nama as lembaga, count(*) as total')
            ->groupBy('lembagas.nama')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    public function getPembayaranBreakdownProperty(): array
    {
        $ppdbIds = $this->baseQueryFull()->pluck('id');

        $tagihans = \App\Models\Tagihan::whereIn('ppdb_id', $ppdbIds)
            ->whereNotNull('ppdb_id')
            ->get()
            ->groupBy('ppdb_id')
            ->map(fn ($group) => $group->sortByDesc('created_at')->first());

        $belum = 0;
        $sebagian = 0;
        $lunas = 0;
        $belumAdaTagihan = $ppdbIds->count() - $tagihans->count();

        foreach ($tagihans as $t) {
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
