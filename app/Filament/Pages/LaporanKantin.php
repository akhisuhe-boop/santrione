<?php

namespace App\Filament\Pages;

use App\Exports\LaporanKantinExport;
use App\Models\Kantin;
use App\Models\KantinTransaksi;
use App\Models\Lembaga;
use App\Services\LaporanKantinPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKantin extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Laporan Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.laporan-kantin';

    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        if (! Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN)) {
            return false;
        }

        return parent::canAccess();
    }

    public $dari;
    public $sampai;
    public $kantin_id;
    public $lembaga_id;
    public $metode;
    public $diinput_oleh;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([

                    Forms\Components\DatePicker::make('dari')
                        ->label('Dari Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->live(debounce: 400),

                    Forms\Components\DatePicker::make('sampai')
                        ->label('Sampai Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->live(debounce: 400),

                    Forms\Components\Select::make('kantin_id')
                        ->label('Kantin')
                        ->options(Kantin::pluck('nama', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua')
                        ->selectablePlaceholder()
                        ->native(false)
                        ->live(debounce: 400),

                    Forms\Components\Select::make('lembaga_id')
                        ->label('Lembaga (atribusi kas)')
                        ->options(Lembaga::pluck('nama', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua')
                        ->selectablePlaceholder()
                        ->native(false)
                        ->live(debounce: 400),

                    Forms\Components\Select::make('metode')
                        ->label('Metode')
                        ->options([
                            'wallet' => 'Wallet',
                            'tunai' => 'Tunai',
                        ])
                        ->placeholder('Semua')
                        ->selectablePlaceholder()
                        ->native(false)
                        ->live(debounce: 400),

                    Forms\Components\Select::make('diinput_oleh')
                        ->label('Kasir')
                        ->options(fn () => KantinTransaksi::query()
                            ->whereNotNull('diinput_oleh')
                            ->distinct()
                            ->pluck('diinput_oleh', 'diinput_oleh'))
                        ->searchable()
                        ->placeholder('Semua')
                        ->selectablePlaceholder()
                        ->native(false)
                        ->live(debounce: 400),

                ]),
        ];
    }

    public function updated(): void
    {
        $this->resetTable();
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return 'table';
    }

    protected function baseQuery()
    {
        return KantinTransaksi::query()
            ->with(['siswa', 'pegawai', 'lembaga', 'kantin', 'items'])
            ->when($this->dari, fn ($q) => $q->whereDate('tanggal', '>=', $this->dari))
            ->when($this->sampai, fn ($q) => $q->whereDate('tanggal', '<=', $this->sampai))
            ->when($this->kantin_id, fn ($q) => $q->where('kantin_id', $this->kantin_id))
            ->when($this->lembaga_id, fn ($q) => $q->where('lembaga_id', $this->lembaga_id))
            ->when($this->metode, fn ($q) => $q->where('metode', $this->metode))
            ->when($this->diinput_oleh, fn ($q) => $q->where('diinput_oleh', $this->diinput_oleh));
    }

    /**
     * Ringkasan termasuk RASIO TUNAI vs WALLET -- ini yang paling penting
     * buat monitoring: kalau rasio tunai di 1 lembaga tiba-tiba tinggi,
     * kemungkinan kasir pakai tunai sebagai jalan pintas rutin (bukan
     * cuma buat pengunjung/guru sesuai maksudnya).
     */
    public function getSummary(): array
    {
        $rows = $this->baseQuery()->get(['metode', 'total']);

        $totalTransaksi = $rows->count();
        $totalOmzet = (int) $rows->sum('total');

        $wallet = $rows->where('metode', 'wallet');
        $tunai = $rows->where('metode', 'tunai');

        $walletCount = $wallet->count();
        $tunaiCount = $tunai->count();

        return [
            'total_transaksi' => $totalTransaksi,
            'total_omzet' => $totalOmzet,
            'wallet_count' => $walletCount,
            'wallet_total' => (int) $wallet->sum('total'),
            'tunai_count' => $tunaiCount,
            'tunai_total' => (int) $tunai->sum('total'),
            'rasio_tunai_persen' => $totalTransaksi > 0
                ? round(($tunaiCount / $totalTransaksi) * 100, 1)
                : 0,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => Excel::download(
                    new LaporanKantinExport($this->filtersArray()),
                    'laporan-kantin.xlsx'
                )),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->action(function () {

                    $data = LaporanKantinPdf::getData($this->filtersArray());

                    $pdf = Pdf::loadView('exports.laporan-kantin', $data)
                        ->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'laporan-kantin.pdf'
                    );
                }),
        ];
    }

    protected function filtersArray(): array
    {
        return [
            'dari' => $this->dari,
            'sampai' => $this->sampai,
            'kantin_id' => $this->kantin_id,
            'lembaga_id' => $this->lembaga_id,
            'metode' => $this->metode,
            'diinput_oleh' => $this->diinput_oleh,
        ];
    }

    protected function pembeliLabel($record): string
    {
        return $record->siswa?->nama_lengkap
            ?? $record->pegawai?->nama
            ?? 'Umum (Pengunjung)';
    }

    /**
     * Pengunjung umum (tanpa siswa/pegawai) TIDAK PERNAH ditampilkan
     * dengan lembaga, walau datanya kebetulan masih ada lembaga_id
     * (mis. baris lama dari sebelum aturan ini berlaku) -- supaya
     * tampilannya selalu konsisten dengan aturan bisnisnya.
     */
    protected function lembagaLabel($record): string
    {
        if (! $record->siswa && ! $record->pegawai) {
            return '-';
        }

        return $record->lembaga?->nama ?? '-';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->defaultSort('tanggal', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d M Y, H:i')),

                Tables\Columns\TextColumn::make('pembeli')
                    ->label('Pembeli')
                    ->state(fn ($record) => $this->pembeliLabel($record))
                    ->description(fn ($record) => $record->siswa
                        ? 'Siswa'
                        : ($record->pegawai ? 'Guru / Staf' : null)),

                Tables\Columns\TextColumn::make('kantin.nama')
                    ->label('Kantin')
                    ->default('-'),

                Tables\Columns\TextColumn::make('lembaga')
                    ->label('Lembaga')
                    ->state(fn ($record) => $this->lembagaLabel($record)),

                Tables\Columns\BadgeColumn::make('metode')
                    ->colors([
                        'success' => 'wallet',
                        'gray' => 'tunai',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('diinput_oleh')
                    ->label('Kasir')
                    ->default('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items')
                    ->label('Item')
                    ->formatStateUsing(fn ($record) => $record->items->pluck('nama_produk')->implode(', '))
                    ->limit(50)
                    ->wrap(),

            ]);
    }
}
