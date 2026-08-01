<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKasExport;

use App\Models\Kas;
use App\Models\KategoriKas;
use App\Models\Lembaga;
use Carbon\Carbon;
use Filament\Actions\Action;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\LaporanKasPdf;

class LaporanKas extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Laporan Kas';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 14;

    protected static string $view = 'filament.pages.laporan-kas';
    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanKas');
    }

    public $dari;
    public $sampai;
    public $tipe;
    public $kategori_id;
    public $lembaga_id;
    public $kelas_id;
    public $rekening_id;
    public $diinput_oleh;
    public $tampilkan_alumni = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function exportExcel()
    {
        return Excel::download(
            new LaporanKasExport([
                'dari' => $this->dari,
                'sampai' => $this->sampai,
                'tipe' => $this->tipe,
                'kategori_id' => $this->kategori_id,
                'lembaga_id' => $this->lembaga_id,
                        'kelas_id' => $this->kelas_id,
                        'rekening_id' => $this->rekening_id,
                        'diinput_oleh' => $this->diinput_oleh,
                        'tampilkan_alumni' => $this->tampilkan_alumni,
            ]),
            'laporan-kas.xlsx'
        );
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
                        new LaporanKasExport([
                            'dari' => $this->dari,
                            'sampai' => $this->sampai,
                            'tipe' => $this->tipe,
                            'kategori_id' => $this->kategori_id,
                            'lembaga_id' => $this->lembaga_id,
                        'kelas_id' => $this->kelas_id,
                        'rekening_id' => $this->rekening_id,
                        'diinput_oleh' => $this->diinput_oleh,
                        'tampilkan_alumni' => $this->tampilkan_alumni,
                        ]),
                        'laporan-kas.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->action(function () {

                    $data = LaporanKasPdf::getData([
                        'dari' => $this->dari,
                        'sampai' => $this->sampai,
                        'tipe' => $this->tipe,
                        'kategori_id' => $this->kategori_id,
                        'lembaga_id' => $this->lembaga_id,
                        'kelas_id' => $this->kelas_id,
                        'rekening_id' => $this->rekening_id,
                        'diinput_oleh' => $this->diinput_oleh,
                        'tampilkan_alumni' => $this->tampilkan_alumni,
                    ]);

                    $pdf = Pdf::loadView('exports.laporan-kas', $data)
                        ->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'laporan-kas.pdf'
                    );
                })
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 AUTO REFRESH (PENTING)
    |--------------------------------------------------------------------------
    */
    public function updated()
    {
        $this->resetTable();
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return 'table';
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(4)
                ->schema([

                    Forms\Components\DatePicker::make('dari')
                        ->label('Dari Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('Silahkan pilih rentang tanggal')
                        ->extraInputAttributes(['class' => 'placeholder-gray-400'])
                        ->live(debounce: 400),

                    Forms\Components\DatePicker::make('sampai')
                        ->label('Sampai Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('Silahkan pilih rentang tanggal')
                        ->extraInputAttributes(['class' => 'placeholder-gray-400'])
                        ->live(debounce: 400),

                    Forms\Components\Select::make('tipe')
                        ->options([
                            'masuk' => 'Masuk',
                            'keluar' => 'Keluar',
                        ])
                        ->placeholder('Semua')
                        ->selectablePlaceholder() // 🔥 biar bisa balik ke "Semua"
                        ->native(false)
                        ->extraInputAttributes([
                            'class' => 'text-gray-400' // 🔥 warna abu-abu saat belum dipilih
                        ])
                        ->live(debounce: 400)
                        ->afterStateUpdated(fn ($set) => $set('kategori_id', null)),

                    Forms\Components\Select::make('kategori_id')
                        ->label('Kategori')
                        ->options(fn ($get) =>
                            KategoriKas::query()
                                ->when($get('tipe'), fn ($q) =>
                                    $q->where('tipe', $get('tipe')))
                                ->pluck('nama', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live(debounce: 400),

                    Forms\Components\Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->options(Lembaga::pluck('nama', 'id'))
                        ->searchable()
                        ->preload()
                        ->live(debounce: 400)
                        ->afterStateUpdated(fn ($set) => $set('kelas_id', null)),

                    Forms\Components\Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(fn ($get) =>
                            \App\Models\Kelas::query()
                                ->when($get('lembaga_id'), fn ($q) =>
                                    $q->where('lembaga_id', $get('lembaga_id')))
                                ->pluck('nama', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live(debounce: 400),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Rekening')
                        ->options(fn ($get) =>
                            \App\Models\Rekening::with('lembaga')
                                ->when($get('lembaga_id'), fn ($q) =>
                                    $q->where('lembaga_id', $get('lembaga_id')))
                                ->get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->nama
                                        . (filled($r->bank) || filled($r->no_rekening) ? ' - ' . $r->bank . ' (' . $r->no_rekening . ')' : '')
                                        . ' — ' . ($r->lembaga->nama ?? 'Semua Lembaga')
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->live(debounce: 400),

                    Forms\Components\Select::make('diinput_oleh')
                        ->label('Kasir')
                        ->options(fn () =>
                            \App\Models\Kas::query()
                                ->whereNotNull('diinput_oleh')
                                ->distinct()
                                ->orderBy('diinput_oleh')
                                ->pluck('diinput_oleh', 'diinput_oleh')
                        )
                        ->searchable()
                        ->preload()
                        ->live(debounce: 400),
                ])
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */
    public function getSummary()
    {
        $query = Kas::query()

            ->when($this->dari, fn ($q) =>
                $q->whereDate('tanggal', '>=', $this->dari))

            ->when($this->sampai, fn ($q) =>
                $q->whereDate('tanggal', '<=', $this->sampai))

            ->when($this->kategori_id, fn ($q) =>
                $q->where('kategori_id', $this->kategori_id))

            ->when($this->lembaga_id, fn ($q) =>
                $q->where('lembaga_id', $this->lembaga_id))

            ->when($this->rekening_id, fn ($q) =>
                $q->where('rekening_id', $this->rekening_id))

            ->when($this->kelas_id, fn ($q) =>
                $q->whereHas('pembayaran.siswa', fn ($s) =>
                    $s->where('kelas_id', $this->kelas_id)))

            ->when($this->diinput_oleh, fn ($q) =>
                $q->where('diinput_oleh', $this->diinput_oleh))

            ->when(! $this->tampilkan_alumni, fn ($q) =>
                $q->where(function ($sub) {
                    $sub->whereDoesntHave('pembayaran.siswa')
                        ->orWhereHas('pembayaran.siswa', fn ($s) =>
                            $s->where('status_siswa', 'Aktif'));
                })
            );

        $masuk = (clone $query)->where('tipe', 'masuk')->sum('nominal');
        $keluar = (clone $query)->where('tipe', 'keluar')->sum('nominal');

        return [
            'masuk' => $masuk,
            'keluar' => $keluar,
            'saldo' => $masuk - $keluar,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE (🔥 TIDAK DIUBAH)
    |--------------------------------------------------------------------------
    */
    public function table(Table $table): Table
    {
        return $table

            ->query(
                Kas::query()
                    ->with([
                        'lembaga',
                        'rekening',
                        'kategori',
                        'pembayaran',
                        'pembayaran.siswa',
                        'pembayaran.siswa.kelas',
                        'pembayaran.siswa.lembaga',
                        'pembayaran.tagihan.ppdb',
                        'pembayaran.tagihan.ppdb.lembaga',
                    ])

                    ->when($this->dari, fn ($q) =>
                        $q->whereDate('tanggal', '>=', $this->dari))

                    ->when($this->sampai, fn ($q) =>
                        $q->whereDate('tanggal', '<=', $this->sampai))

                    ->when($this->tipe, fn ($q) =>
                        $q->where('tipe', $this->tipe))

                    ->when($this->kategori_id, fn ($q) =>
                        $q->where('kategori_id', $this->kategori_id))

                    ->when($this->lembaga_id, fn ($q) =>
                        $q->where('lembaga_id', $this->lembaga_id))

                    ->when($this->rekening_id, fn ($q) =>
                        $q->where('rekening_id', $this->rekening_id))

                    ->when($this->kelas_id, fn ($q) =>
                        $q->whereHas('pembayaran.siswa', fn ($s) =>
                            $s->where('kelas_id', $this->kelas_id)))

                    ->when($this->diinput_oleh, fn ($q) =>
                        $q->where('diinput_oleh', $this->diinput_oleh))

                    ->when(! $this->tampilkan_alumni, fn ($q) =>
                        $q->where(function ($sub) {
                            $sub->whereDoesntHave('pembayaran.siswa')
                                ->orWhereHas('pembayaran.siswa', fn ($s) =>
                                    $s->where('status_siswa', 'Aktif'));
                        })
                    )

                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
            )

            ->defaultSort('tanggal', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('kode')->label('Kode'),

                Tables\Columns\BadgeColumn::make('tipe')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'masuk',
                        'danger' => 'keluar',
                    ])
                    ->formatStateUsing(fn ($state) =>
                        $state === 'masuk' ? 'Masuk' : 'Keluar'
                    ),

                Tables\Columns\TextColumn::make('lembaga_fix')
                ->label('Lembaga')
                ->badge()
                ->color(function ($state) {
                    $state = strtolower($state);

                    if (str_contains($state, 'sdit')) return 'success';
                    if (str_contains($state, 'smpit')) return 'warning';
                    if (str_contains($state, 'smait')) return 'danger';

                    return 'primary';
                })
                ->getStateUsing(fn ($record) =>
                    $record->pembayaran?->tagihan?->lembaga_nama
                    ?? $record->lembaga?->nama
                    ?? 'Yayasan/Pesantren'
                ),

                Tables\Columns\TextColumn::make('kategori.nama')
                ->label('Kategori')
                ->getStateUsing(function ($record) {
            
                    $tagihan = $record->pembayaran?->tagihan;
            
                    if (!$tagihan) {
                        return $record->kategori?->nama ?? '-';
                    }
            
                    return $tagihan->judul;
                }),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) =>
                        Carbon::parse($state)->translatedFormat('d-m-Y')
                    ),

                Tables\Columns\TextColumn::make('kas_masuk')
                    ->label('Kas Masuk')
                    ->getStateUsing(fn ($record) =>
                        $record->tipe === 'masuk'
                            ? 'Rp ' . number_format($record->nominal, 0, ',', '.')
                            : 'Rp 0'
                    ),

                Tables\Columns\TextColumn::make('kas_keluar')
                    ->label('Kas Keluar')
                    ->getStateUsing(fn ($record) =>
                        $record->tipe === 'keluar'
                            ? 'Rp ' . number_format($record->nominal, 0, ',', '.')
                            : 'Rp 0'
                    ),

                Tables\Columns\TextColumn::make('rekening.nama')
                    ->label('Rekening')
                    ->formatStateUsing(function ($record) {

                        $rekening = $record->rekening;

                        if (! $rekening) {
                            return '-';
                        }

                        if (filled($rekening->bank) || filled($rekening->no_rekening)) {
                            return "{$rekening->bank} ({$rekening->no_rekening})";
                        }

                        // Rekening tipe e-wallet biasanya tidak punya
                        // bank/no_rekening beneran — tampilkan namanya
                        // saja daripada "()" kosong.
                        return $rekening->nama ?: '-';
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                ->label('Keterangan')
                ->wrap()
                ->getStateUsing(function ($record) {
                    /*
                    |--------------------------------------------------------------------------
                    | PEMBAYARAN SANTRI/SISWA
                    |--------------------------------------------------------------------------
                    */

                    $tagihan = $record->pembayaran?->tagihan;
                    if ($tagihan) {
                        return trim(
                            ($tagihan->nama ?? '-') .
                            ' - ' .
                            ($tagihan->kelas_nama ?? '-')
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PAYROLL
                    |--------------------------------------------------------------------------
                    */
                    if ($record->sumber === 'payroll') {
                        return $record->keterangan
                            ?? 'Pembayaran Gaji Pegawai';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DEFAULT
                    |--------------------------------------------------------------------------
                    */
                    return $record->keterangan
                        ?? $record->penanggung_jawab
                        ?? '-';
                }),

                Tables\Columns\TextColumn::make('diinput_oleh')
                    ->label('Diinput Oleh')
                    ->getStateUsing(fn ($record) => $record->diinput_oleh ?? $record->pembayaran?->diinput_oleh ?? '-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('diverifikasi_oleh')
                    ->label('Diverifikasi Oleh')
                    ->getStateUsing(fn ($record) => $record->pembayaran?->diverifikasi_oleh ?? '-')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('bukti')
                    ->label('Bukti')
                    ->getStateUsing(fn ($record) =>
                        ($record->pembayaran?->bukti_transfer || $record->bukti)
                            ? 'Lihat'
                            : '-'
                    )
                    ->colors([
                        'primary' => 'Lihat',
                        'gray' => '-',
                    ])
                    ->url(function ($record) {
                        if ($record->pembayaran?->bukti_transfer) {
                            return \Storage::disk('r2-private')->temporaryUrl($record->pembayaran->bukti_transfer, now()->addMinutes(10));
                        }

                        if ($record->bukti) {
                            return \Storage::disk('r2-private')->temporaryUrl($record->bukti, now()->addMinutes(10));
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->disabled(fn ($record) =>
                        !($record->pembayaran?->bukti_transfer || $record->bukti)
                    ),
            ])

            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}