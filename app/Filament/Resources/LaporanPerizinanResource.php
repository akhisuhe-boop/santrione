<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPerizinanResource\Pages;
use App\Models\Siswa;
use App\Models\Perizinan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class LaporanPerizinanResource extends BaseResource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Perizinan';
    protected static ?string $navigationLabel = 'Laporan Perizinan';
    protected static ?int $navigationSort = 13;

    /**
     * Tentukan rentang tanggal [mulai, selesai] dari state filter
     * "periode" -- dipakai bersama oleh kolom "Total Hari" & modal
     * Riwayat supaya konsisten.
     */
    public static function resolvePeriode(?array $state): array
    {
        $tipe = $state['tipe'] ?? 'bulan_ini';

        if ($tipe === 'bulan_lain' && ! empty($state['bulan']) && ! empty($state['tahun'])) {
            $awal = \Carbon\Carbon::create((int) $state['tahun'], (int) $state['bulan'], 1)->startOfMonth();
            return [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()];
        }

        if ($tipe === 'custom' && ! empty($state['dari']) && ! empty($state['sampai'])) {
            return [$state['dari'], $state['sampai']];
        }

        // default: bulan ini
        return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
    }

    /**
     * Total hari izin yang BENAR-BENAR DISETUJUI (status approved) dalam
     * periode tertentu -- bukan lagi semua pengajuan (termasuk pending/
     * ditolak) sepanjang masa seperti sebelumnya.
     */
    public static function totalHariDisetujui(int $siswaId, array $periode): int
    {
        return Perizinan::where('siswa_id', $siswaId)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $periode[1])
            ->where(function ($q) use ($periode) {
                $q->whereDate('tanggal_selesai', '>=', $periode[0])
                    ->orWhereNull('tanggal_selesai');
            })
            ->get()
            ->sum(function ($izin) use ($periode) {

                if (! $izin->tanggal_mulai) {
                    return 0;
                }

                $mulai = \Carbon\Carbon::parse($izin->tanggal_mulai)->startOfDay();
                $selesai = $izin->tanggal_selesai
                    ? \Carbon\Carbon::parse($izin->tanggal_selesai)->startOfDay()
                    : $mulai->copy();

                // Potong ke batas periode yang dipilih -- kalau izinnya
                // "nyambung" lewat awal/akhir periode, cuma hari yang
                // masuk periode ini yang dihitung.
                $periodeAwal = \Carbon\Carbon::parse($periode[0])->startOfDay();
                $periodeAkhir = \Carbon\Carbon::parse($periode[1])->startOfDay();

                $mulaiEfektif = $mulai->greaterThan($periodeAwal) ? $mulai : $periodeAwal;
                $selesaiEfektif = $selesai->lessThan($periodeAkhir) ? $selesai : $periodeAkhir;

                if ($mulaiEfektif->greaterThan($selesaiEfektif)) {
                    return 0;
                }

                return $mulaiEfektif->diffInDays($selesaiEfektif) + 1;
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()
            )

            ->columns([
                TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->placeholder('-'),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->placeholder('-'),

                TextColumn::make('total_hari')
                ->label('Total Hari Izin Disetujui')
                ->getStateUsing(function ($record, $livewire) {
                    $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);
                    return static::totalHariDisetujui($record->id, $periode);
                })
                ->formatStateUsing(fn ($state) => $state . ' hari')
                ->badge()
                ->color('success'),
                        ])

            ->filters([

                Tables\Filters\Filter::make('periode')
                    ->form([
                        \Filament\Forms\Components\Select::make('tipe')
                            ->label('Periode')
                            ->options([
                                'bulan_ini' => 'Bulan Ini',
                                'bulan_lain' => 'Pilih Bulan Lain',
                                'custom' => 'Custom Range',
                            ])
                            ->default('bulan_ini')
                            ->native(false)
                            ->live(),

                        \Filament\Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->native(false)
                            ->visible(fn ($get) => $get('tipe') === 'bulan_lain'),

                        \Filament\Forms\Components\TextInput::make('tahun')
                            ->label('Tahun')
                            ->numeric()
                            ->default(now()->year)
                            ->visible(fn ($get) => $get('tipe') === 'bulan_lain'),

                        \Filament\Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->visible(fn ($get) => $get('tipe') === 'custom'),

                        \Filament\Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->visible(fn ($get) => $get('tipe') === 'custom'),
                    ])
                    ->query(fn ($query) => $query)
                    ->indicateUsing(function (array $data): ?string {
                        $periode = static::resolvePeriode($data);
                        return 'Periode: ' . \Carbon\Carbon::parse($periode[0])->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($periode[1])->translatedFormat('d M Y');
                    }),

                SelectFilter::make('lembaga')
                    ->relationship('lembaga', 'nama')
                    ->label('Lembaga'),

                SelectFilter::make('kelas')
                    ->relationship('kelas', 'nama')
                    ->label('Kelas'),

                SelectFilter::make('id')
                    ->label('Siswa')
                    ->relationship('perizinans.siswa', 'nama_lengkap')
                    ->searchable(),
            ])

            ->actions([
                Tables\Actions\Action::make('detail')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->label('Riwayat')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'Riwayat Izin - ' . $record->nama_lengkap)
                    ->modalContent(function ($record, $livewire) {

                        $periode = static::resolvePeriode($livewire->tableFilters['periode'] ?? null);

                        $data = Perizinan::where('siswa_id', $record->id)
                            ->whereDate('tanggal_mulai', '<=', $periode[1])
                            ->where(function ($q) use ($periode) {
                                $q->whereDate('tanggal_selesai', '>=', $periode[0])
                                    ->orWhereNull('tanggal_selesai');
                            })
                            ->latest()
                            ->get();

                        return view('filament.pages.detail-perizinan', [
                            'data' => $data
                        ]);
                    })
                    ->modalWidth('4xl'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPerizinans::route('/'),
        ];
    }
}