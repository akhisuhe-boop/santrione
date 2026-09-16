<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\JadwalKegiatan;

use Filament\Pages\Page;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;

use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

/**
 * DITAMBAHKAN (16 Sep 2026) -- gabungan "Laporan Absensi Pegawai" +
 * "Laporan Absensi Siswa" (LaporanAbsensiPegawai.php,
 * LaporanAbsensiSiswa.php) jadi SATU MENU dengan tab Pegawai/Siswa,
 * BUKAN satu tabel tercampur -- sesuai keputusan bareng user
 * (kolomnya hampir identik, tapi tetap dipisah tab karena "siapa yang
 * alpa" jarang dicek campur Pegawai+Siswa sekaligus, dan supaya kolom
 * "Kelas" -- yang cuma relevan buat Siswa -- tidak janggal).
 *
 * Logic hitung (query, telat, alpha) untuk masing-masing tipe SENGAJA
 * DISALIN APA ADANYA dari 2 halaman lama (bukan ditulis ulang total),
 * cuma diparameterisasi lewat $idKolom/$tipeAbsensi -- supaya tidak
 * beresiko mengubah cara hitung yang sudah terbukti benar & dipakai
 * sekolah aktif.
 *
 * 2 halaman lama (LaporanAbsensiPegawai, LaporanAbsensiSiswa) TIDAK
 * dihapus -- cuma disembunyikan dari menu (shouldRegisterNavigation)
 * supaya ada jalan mundur cepat kalau ternyata ada yang kelewat pas
 * digabung, tanpa perlu saya tulis ulang dari nol.
 *
 * PENTING setelah deploy: halaman ini BARU buat Shield, permission
 * `page_LaporanAbsensiKegiatan` belum ada di database -- jalankan
 * `php artisan shield:generate` di server, lalu assign permission itu
 * ke role yang sama seperti yang sebelumnya punya akses ke Laporan
 * Absensi Pegawai/Siswa (menu Roles di Master Setting).
 */
class LaporanAbsensiKegiatan extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.laporan-absensi-kegiatan';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $title = 'Laporan Absensi Kegiatan';

    protected static ?string $navigationLabel = 'Laporan Absensi Kegiatan';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return \App\Support\FeatureGate::tenantBolehLihatGrup(static::$navigationGroup)
            && auth()->user()->can('page_LaporanAbsensiKegiatan');
    }

    public ?array $formData = [];

    public string $tipe = 'siswa';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function pilihTipe(string $tipe): void
    {
        $this->tipe = $tipe;
        $this->formData = [];
        $this->form->fill();
        $this->resetTable();
    }

    public function filter()
    {
        $this->resetTable();
    }

    public function resetFilter()
    {
        $this->formData = [];
        $this->form->fill();
        $this->resetTable();
    }

    public function form(Form $form): Form
    {
        $tipeAbsensi = $this->tipe === 'siswa' ? 'siswa' : 'guru';

        $fields = [
            DatePicker::make('tanggal_awal')
                ->label('Dari Tanggal')
                ->native(false)
                ->placeholder('Pilih Tanggal Awal'),

            DatePicker::make('tanggal_akhir')
                ->label('Sampai Tanggal')
                ->native(false)
                ->placeholder('Pilih Tanggal Akhir'),
        ];

        if ($this->tipe === 'siswa') {
            $fields[] = Select::make('kelas')
                ->label('Kelas')
                ->searchable()
                ->preload()
                ->options(Kelas::orderBy('nama')->pluck('nama', 'id'));
        }

        $fields[] = Select::make('kegiatan')
            ->label('Kegiatan')
            ->placeholder('Semua Kegiatan')
            ->searchable()
            ->preload()
            ->options(
                JadwalKegiatan::with('template')
                    ->whereHas('template', fn ($q) => $q->where('tipe', $tipeAbsensi))
                    ->orderBy('tanggal', 'desc')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        $item->id => ($item->template->nama_kegiatan ?? '-') . ' - ' . \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y'),
                    ])
            );

        $fields[] = Group::make([
            Actions::make([
                FormAction::make('filter')
                    ->label('Filter')
                    ->icon('heroicon-m-funnel')
                    ->color('primary')
                    ->submit('filter'),
                FormAction::make('reset')
                    ->label('Reset')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(fn () => $this->resetFilter()),
            ]),
        ])->extraAttributes(['class' => 'flex items-end h-full pb-1']);

        return $form
            ->schema($fields)
            ->statePath('formData')
            ->columns($this->tipe === 'siswa' ? 5 : 4);
    }

    protected function applyFilter($query)
    {
        if ($this->formData['kegiatan'] ?? null) {
            $query->where('jadwal_kegiatan_id', $this->formData['kegiatan']);
        }

        if (($this->formData['tanggal_awal'] ?? null) && ($this->formData['tanggal_akhir'] ?? null)) {
            $query->whereHas('jadwalKegiatan', function ($q) {
                $q->whereBetween('tanggal', [
                    $this->formData['tanggal_awal'],
                    $this->formData['tanggal_akhir'],
                ]);
            });
        }

        return $query;
    }

    protected function getTotalKegiatan()
    {
        $tipeAbsensi = $this->tipe === 'siswa' ? 'siswa' : 'guru';

        $query = JadwalKegiatan::query()
            ->whereHas('template', fn ($q) => $q->where('tipe', $tipeAbsensi));

        if ($this->formData['kegiatan'] ?? null) {
            $query->where('id', $this->formData['kegiatan']);
        }

        if (($this->formData['tanggal_awal'] ?? null) && ($this->formData['tanggal_akhir'] ?? null)) {
            $query->whereBetween('tanggal', [
                $this->formData['tanggal_awal'],
                $this->formData['tanggal_akhir'],
            ]);
        }

        return $query->count();
    }

    protected function hitungTotalTelat($record, string $idKolom, string $tipeAbsensi): string
    {
        $query = Absensi::with('jadwalKegiatan.template')
            ->where($idKolom, $record->id)
            ->where('tipe', $tipeAbsensi)
            ->where('status', 'Terlambat');

        $this->applyFilter($query);

        $absensis = $query->get();

        if ($absensis->count() == 0) {
            return '0 Menit';
        }

        $totalMenit = 0;

        foreach ($absensis as $absen) {
            if (! $absen->jadwalKegiatan || ! $absen->jam_scan) {
                continue;
            }

            $tanggal = \Carbon\Carbon::parse($absen->jam_scan)->format('Y-m-d');
            $jamMulai = \Carbon\Carbon::parse($tanggal . ' ' . $absen->jadwalKegiatan->jam_mulai);
            $toleransi = $absen->jadwalKegiatan->template->toleransi_telat ?? 0;
            $batasTelat = $jamMulai->copy()->addMinutes($toleransi);
            $jamScan = \Carbon\Carbon::parse($absen->jam_scan);
            $menitTelat = $batasTelat->diffInMinutes($jamScan, false);

            if ($menitTelat < 0) {
                $menitTelat = 0;
            }

            $totalMenit += $menitTelat;
        }

        return ceil($totalMenit) . ' Menit';
    }

    public function table(Table $table): Table
    {
        $isSiswa = $this->tipe === 'siswa';
        $idKolom = $isSiswa ? 'siswa_id' : 'pegawai_id';
        $tipeAbsensi = $isSiswa ? 'siswa' : 'guru';

        $query = $isSiswa
            ? Siswa::query()->when($this->formData['kelas'] ?? null, fn ($q, $kelas) => $q->where('kelas_id', $kelas))
            : Pegawai::query();

        $columns = [
            TextColumn::make($isSiswa ? 'nama_lengkap' : 'nama')
                ->label($isSiswa ? 'Nama Siswa' : 'Nama Pegawai')
                ->searchable()
                ->sortable(),
        ];

        if ($isSiswa) {
            $columns[] = TextColumn::make('kelas.nama')->label('Kelas')->sortable();
        }

        $columns = array_merge($columns, [
            TextColumn::make('hadir')
                ->label('Hadir')
                ->badge()
                ->color('success')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $query = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)->where('status', 'Hadir');
                    $this->applyFilter($query);

                    return $query->count();
                }),

            TextColumn::make('waktu_absen')
                ->label('Waktu Absen')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $query = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)->whereIn('status', ['Hadir', 'Terlambat']);
                    $this->applyFilter($query);
                    $absen = $query->latest('jam_scan')->first();

                    return $absen?->jam_scan ? \Carbon\Carbon::parse($absen->jam_scan)->format('H:i') : '-';
                }),

            TextColumn::make('terlambat')
                ->label('Terlambat')
                ->badge()
                ->color('warning')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $query = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)->where('status', 'Terlambat');
                    $this->applyFilter($query);

                    return $query->count();
                }),

            TextColumn::make('total_telat')
                ->label('Total Telat')
                ->badge()
                ->color('danger')
                ->getStateUsing(fn ($record) => $this->hitungTotalTelat($record, $idKolom, $tipeAbsensi)),

            TextColumn::make('izin')
                ->label('Izin')
                ->badge()
                ->color('info')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $query = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)->where('status', 'Izin');
                    $this->applyFilter($query);

                    return $query->count();
                }),

            TextColumn::make('sakit')
                ->label('Sakit')
                ->badge()
                ->color('gray')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $query = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)->where('status', 'Sakit');
                    $this->applyFilter($query);

                    return $query->count();
                }),

            TextColumn::make('alpha')
                ->label('Alpha')
                ->badge()
                ->color('danger')
                ->getStateUsing(function ($record) use ($idKolom, $tipeAbsensi) {
                    $totalKegiatan = $this->getTotalKegiatan();
                    $totalAbsen = Absensi::where($idKolom, $record->id)->where('tipe', $tipeAbsensi)
                        ->whereIn('status', ['Hadir', 'Terlambat', 'Izin', 'Sakit']);
                    $this->applyFilter($totalAbsen);
                    $totalAbsen = $totalAbsen->count();
                    $alpha = $totalKegiatan - $totalAbsen;

                    return $alpha > 0 ? $alpha : 0;
                }),

            TextColumn::make('total')
                ->label('Total')
                ->badge()
                ->color('primary')
                ->getStateUsing(fn () => $this->getTotalKegiatan()),
        ]);

        return $table
            ->query($query)
            ->columns($columns)
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->exports([
                        ExcelExport::make()->fromTable()->withFilename(fn () => 'Laporan-Absensi-Kegiatan-' . ($isSiswa ? 'Siswa' : 'Pegawai') . '-' . now()->format('Y-m-d')),
                    ]),
            ])
            ->actions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Riwayat Absensi Kegiatan — ' . ($isSiswa ? $record->nama_lengkap : $record->nama))
                    ->modalContent(function ($record) use ($idKolom, $tipeAbsensi) {
                        $query = Absensi::with('jadwalKegiatan.template')
                            ->where($idKolom, $record->id)
                            ->where('tipe', $tipeAbsensi);

                        $this->applyFilter($query);

                        $riwayat = $query->get()->sortByDesc(fn ($a) => $a->jadwalKegiatan?->tanggal);

                        return view('filament.pages.partials.detail-riwayat-kegiatan', ['riwayat' => $riwayat]);
                    }),

                Action::make('edit_status')
                    ->label('Edit Kehadiran')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Select::make('status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Terlambat' => 'Terlambat',
                                'Izin' => 'Izin',
                                'Sakit' => 'Sakit',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record) use ($idKolom, $tipeAbsensi) {
                        if (! ($this->formData['kegiatan'] ?? null)) {
                            return;
                        }

                        Absensi::updateOrCreate(
                            [$idKolom => $record->id, 'jadwal_kegiatan_id' => $this->formData['kegiatan'], 'tipe' => $tipeAbsensi],
                            ['status' => $data['status'], 'jam_scan' => now(), 'metode' => 'manual']
                        );
                    }),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}
