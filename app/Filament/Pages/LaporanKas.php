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
    protected static ?int $navigationSort = 10;

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
    public $rekening_id;

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
                        'rekening_id' => $this->rekening_id,
            ]),
            'laporan-kas.xlsx'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
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
                        'rekening_id' => $this->rekening_id,
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
                        'rekening_id' => $this->rekening_id,
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
            Forms\Components\Grid::make(5)
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
                        ->live(debounce: 400),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Rekening')
                        ->options(fn ($get) =>
                            \App\Models\Rekening::query()
                                ->when($get('lembaga_id'), fn ($q) =>
                                    $q->where('lembaga_id', $get('lembaga_id')))
                                ->get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->nama . ' - ' . $r->bank . ' (' . $r->no_rekening . ')'
                                ])
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
                $q->where('rekening_id', $this->rekening_id));

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
                    ?? '-'
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
                    ->formatStateUsing(fn ($record) =>
                        $record->rekening
                            ? "{$record->rekening->bank} ({$record->rekening->no_rekening})"
                            : '-'
                    ),

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
                    ->getStateUsing(fn ($record) => $record->pembayaran?->diinput_oleh ?? '-')
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
                            return asset('storage/' . $record->pembayaran->bukti_transfer);
                        }

                        if ($record->bukti) {
                            return asset('storage/' . $record->bukti);
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