<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiHarian;

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

use Filament\Notifications\Notification;

use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

/**
 * DITAMBAHKAN (16 Sep 2026) -- laporan absensi HARIAN (masuk/pulang)
 * gabungan Siswa+Pegawai, 1 menu 2 tab -- pola sama persis dengan
 * LaporanAbsensiKegiatan.php (lihat komentar di sana buat alasan
 * kenapa tab, bukan 1 tabel tercampur).
 *
 * BEDA dari Laporan Kegiatan: absensi_harians (App\Models\AbsensiHarian)
 * sudah punya 1 kolom status terpadu per hari (status_masuk: Hadir/
 * Terlambat/Izin/Sakit/Alpa -- diisi TandaiAlpaAbsensiHarian buat
 * yang tidak scan sama sekali, lihat catatan di command itu soal jam
 * per-hari), jadi rekapnya cukup COUNT per status, tidak perlu hitung
 * "total kegiatan - hadir" seperti Laporan Kegiatan.
 *
 * Class SENGAJA dinamai LaporanAbsensiHarianGabungan (bukan
 * LaporanAbsensiHarian) -- nama itu sudah dipakai class LAIN
 * (App\Filament\Pages\LaporanAbsensiHarian.php = halaman INPUT
 * "Absensi Masuk & Pulang", bukan laporan, sudah ada dari sebelumnya).
 *
 * PENTING setelah deploy: halaman ini BARU buat Shield, jalankan
 * `php artisan shield:generate --panel=admin --all` lalu assign
 * permission `page_LaporanAbsensiHarianGabungan` ke role yang perlu
 * (menu Roles di Master Setting -- kalau login sebagai admin tenant
 * biasa dan role-nya GLOBAL/dipakai semua Yayasan, wajib login
 * sebagai Platform Admin dulu buat bisa edit role itu).
 */
class LaporanAbsensiHarianGabungan extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.laporan-absensi-harian-gabungan';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $title = 'Laporan Absensi Harian';

    protected static ?string $navigationLabel = 'Laporan Absensi Harian';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return \App\Support\FeatureGate::tenantBolehLihatGrup(static::$navigationGroup)
            && auth()->user()->can('page_LaporanAbsensiHarianGabungan');
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
            ->columns($this->tipe === 'siswa' ? 4 : 3);
    }

    protected function applyFilterTanggal($query)
    {
        if (($this->formData['tanggal_awal'] ?? null) && ($this->formData['tanggal_akhir'] ?? null)) {
            $query->whereBetween('tanggal', [
                $this->formData['tanggal_awal'],
                $this->formData['tanggal_akhir'],
            ]);
        }

        return $query;
    }

    public function table(Table $table): Table
    {
        $isSiswa = $this->tipe === 'siswa';
        $idKolom = $isSiswa ? 'siswa_id' : 'pegawai_id';

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

        $hitungStatus = function ($record, string $status) use ($idKolom) {
            $query = AbsensiHarian::where($idKolom, $record->id)->where('status_masuk', $status);
            $this->applyFilterTanggal($query);

            return $query->count();
        };

        $columns = array_merge($columns, [
            TextColumn::make('hadir')
                ->label('Hadir')
                ->badge()
                ->color('success')
                ->getStateUsing(fn ($record) => $hitungStatus($record, 'Hadir')),

            TextColumn::make('jam_masuk_terakhir')
                ->label('Jam Masuk Terakhir')
                ->getStateUsing(function ($record) use ($idKolom) {
                    $query = AbsensiHarian::where($idKolom, $record->id)->whereIn('status_masuk', ['Hadir', 'Terlambat']);
                    $this->applyFilterTanggal($query);
                    $absen = $query->latest('tanggal')->first();

                    return $absen?->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '-';
                }),

            TextColumn::make('terlambat')
                ->label('Terlambat')
                ->badge()
                ->color('warning')
                ->getStateUsing(fn ($record) => $hitungStatus($record, 'Terlambat')),

            TextColumn::make('izin')
                ->label('Izin')
                ->badge()
                ->color('info')
                ->getStateUsing(fn ($record) => $hitungStatus($record, 'Izin')),

            TextColumn::make('sakit')
                ->label('Sakit')
                ->badge()
                ->color('gray')
                ->getStateUsing(fn ($record) => $hitungStatus($record, 'Sakit')),

            TextColumn::make('alpa')
                ->label('Alpa')
                ->badge()
                ->color('danger')
                ->getStateUsing(fn ($record) => $hitungStatus($record, 'Alpa')),

            TextColumn::make('total')
                ->label('Total Hari')
                ->badge()
                ->color('primary')
                ->getStateUsing(function ($record) use ($idKolom) {
                    $query = AbsensiHarian::where($idKolom, $record->id);
                    $this->applyFilterTanggal($query);

                    return $query->count();
                }),
        ]);

        return $table
            ->query($query)
            ->columns($columns)
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->exports([
                        ExcelExport::make()->fromTable()->withFilename(fn () => 'Laporan-Absensi-Harian-' . ($isSiswa ? 'Siswa' : 'Pegawai') . '-' . now()->format('Y-m-d')),
                    ]),
            ])
            ->actions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Riwayat Absensi Harian — ' . ($isSiswa ? $record->nama_lengkap : $record->nama))
                    ->modalContent(function ($record) use ($idKolom) {
                        $query = AbsensiHarian::where($idKolom, $record->id);
                        $this->applyFilterTanggal($query);

                        $riwayat = $query->orderByDesc('tanggal')->get();

                        return view('filament.pages.partials.detail-riwayat-harian', ['riwayat' => $riwayat]);
                    }),

                Action::make('edit_status')
                    ->label('Edit Kehadiran')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Select::make('status_masuk')
                            ->label('Status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Terlambat' => 'Terlambat',
                                'Izin' => 'Izin',
                                'Sakit' => 'Sakit',
                                'Alpa' => 'Alpa',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record) use ($idKolom) {
                        // WAJIB pilih 1 tanggal spesifik (awal = akhir)
                        // dulu lewat filter -- kalau rentang tanggal
                        // masih lebar, tidak jelas hari mana yang mau
                        // dikoreksi.
                        $tanggalAwal = $this->formData['tanggal_awal'] ?? null;
                        $tanggalAkhir = $this->formData['tanggal_akhir'] ?? null;

                        if (! $tanggalAwal || ! $tanggalAkhir || $tanggalAwal !== $tanggalAkhir) {
                            Notification::make()
                                ->title('Pilih 1 tanggal spesifik dulu')
                                ->body('Isi "Dari Tanggal" dan "Sampai Tanggal" dengan tanggal yang SAMA, lalu klik Filter, baru Edit Kehadiran.')
                                ->warning()
                                ->send();

                            return;
                        }

                        AbsensiHarian::updateOrCreate(
                            [$idKolom => $record->id, 'tanggal' => $tanggalAwal],
                            ['status_masuk' => $data['status_masuk'], 'metode_masuk' => 'Manual']
                        );
                    }),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}
