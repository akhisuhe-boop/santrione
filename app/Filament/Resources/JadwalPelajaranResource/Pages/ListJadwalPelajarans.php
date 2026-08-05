<?php

namespace App\Filament\Resources\JadwalPelajaranResource\Pages;

use App\Filament\Resources\JadwalPelajaranResource;
use App\Models\JamPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pegawai;
use App\Services\JadwalGeneratorService;
use App\Services\CurriculumService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Throwable;


class ListJadwalPelajarans extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = JadwalPelajaranResource::class;

    protected static string $view =
        'filament.resources.jadwal-pelajaran-resource.pages.list-jadwal-pelajarans';

    /**
     * Judul halaman & breadcrumb.
     *
     * Ini custom Page (bukan ListRecords bawaan Filament), jadi
     * judulnya tidak otomatis ikut label Resource — kalau tidak
     * di-set manual, Filament akan menebak dari nama class
     * "ListJadwalPelajarans" apa adanya (ikut ada akhiran "s").
     */
    protected static ?string $title = 'Jadwal Pelajaran';

    public function getBreadcrumb(): string
    {
        return 'Jadwal Pelajaran';
    }

    /**
     * ==========================================================
     * FILTER
     * ==========================================================
     */

    public ?int $kelas_id = null;

    /**
     * ==========================================================
     * GRID
     * ==========================================================
     */

    public array $grid = [];

    /**
     * ==========================================================
     * MODAL
     * ==========================================================
     */

    public ?string $hari = null;

    public ?int $jam_id = null;

    public ?int $guru_id = null;

    public ?int $mapel_id = null;

    /**
     * ==========================================================
     * INIT
     * ==========================================================
     */

    public function mount(): void
    {
        $this->form->fill();

        if ($this->kelas_id) {
            $this->loadGrid();
        }
    }

    /**
     * ==========================================================
     * FORM
     * ==========================================================
     */

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('kelas_id')

                    ->label('Kelas')

                    ->options(
                        Kelas::query()
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                    )

                    ->searchable()

                    ->preload()

                    ->live()

                    ->afterStateUpdated(
                        fn () => $this->loadGrid()
                    )

                    ->required(),

            ]);
    }

    /**
     * ==========================================================
     * LOAD GRID
     * ==========================================================
     */

    protected function loadGrid(): void
    {
        if (!$this->kelas_id) {

            $this->grid = [];

            return;

        }

        $hari = [

            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',

        ];

        $kelas = Kelas::find($this->kelas_id);

        $jamPelajaran = JamPelajaran::query()

            ->where('lembaga_id', $kelas?->lembaga_id)

            ->where('aktif', true)

            ->orderBy('urutan')

            ->get();

        $jadwal = JadwalPelajaran::query()

            ->with([

                'guru',

                'mataPelajaran',

                'jamPelajaran',

            ])

            ->where('kelas_id', $this->kelas_id)

            ->get()

            ->groupBy(function ($item) {

                return $item->hari.'-'.$item->jam_pelajaran_id;

            });

        $grid = [];

        foreach ($jamPelajaran as $jam) {

            $row = [

                'jam' => $jam,

                'hari' => [],

            ];

            foreach ($hari as $h) {

                $key = $h.'-'.$jam->id;

                $row['hari'][$h] = [
                    'record'  => optional($jadwal->get($key))->first(),
                    'rowspan' => 1,
                    'hide'    => false,
                ];

            }

            $grid[] = $row;

        }

        /*
        |--------------------------------------------------------------------------
        | Gabungkan tampilan (rowspan) untuk 1 pertemuan yang JP-nya
        | berurutan (mis. JP/Pertemuan = 2 -> 2 baris JP yang sama
        | persis mapel & gurunya di hari yang sama), supaya tampil
        | sebagai 1 kotak visual, bukan 2 kotak identik yang menumpuk.
        |--------------------------------------------------------------------------
        */

        foreach ($hari as $h) {

            for ($i = 0; $i < count($grid); $i++) {

                $current = $grid[$i]['hari'][$h]['record'] ?? null;

                if (! $current) {
                    continue;
                }

                if ($grid[$i]['hari'][$h]['hide']) {
                    continue;
                }

                $span = 1;

                for ($j = $i + 1; $j < count($grid); $j++) {

                    $next = $grid[$j]['hari'][$h]['record'] ?? null;

                    if (
                        ! $next ||
                        $next->mata_pelajaran_id !== $current->mata_pelajaran_id ||
                        $next->pegawai_id !== $current->pegawai_id
                    ) {
                        break;
                    }

                    $grid[$j]['hari'][$h]['hide'] = true;

                    $span++;
                }

                $grid[$i]['hari'][$h]['rowspan'] = $span;

                $i += ($span - 1);
            }
        }

        $this->grid = $grid;
    }

    /**
 * ==========================================================
 * GENERATE JADWAL
 * ==========================================================
 */
public function generate(): void
{
    if (
        ! auth()->user()?->is_platform_admin
        && ! \Filament\Facades\Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::AKADEMIK)
    ) {

        Notification::make()
            ->title('Fitur Generate Jadwal Otomatis belum aktif di paket Anda.')
            ->body('Silakan upgrade paket langganan untuk membuka fitur ini, atau susun jadwal secara manual lewat tombol "+" di setiap slot.')
            ->warning()
            ->send();

        return;
    }

    if (!$this->kelas_id) {

        Notification::make()
            ->title('Silakan pilih kelas terlebih dahulu.')
            ->danger()
            ->send();

        return;
    }

    try {

        app(JadwalGeneratorService::class)
            ->generate($this->kelas_id);

        $this->loadGrid();

        Notification::make()
            ->title('Jadwal berhasil digenerate.')
            ->success()
            ->send();

    } catch (Throwable $e) {

        report($e);

        Notification::make()
            ->title('Generate jadwal gagal.')
            ->body($e->getMessage())
            ->danger()
            ->send();

    }
}

/**
 * ==========================================================
 * OPEN MODAL
 * ==========================================================
 */
public function openModal(
    string $hari,
    int $jamId
): void {

    $this->hari = $hari;

    $this->jam_id = $jamId;

    $this->guru_id = null;

    $this->mapel_id = null;

    $this->dispatch(
        'open-modal',
        id: 'input-jadwal'
    );
}

/**
 * ==========================================================
 * SIMPAN JADWAL MANUAL
 * ==========================================================
 */
public function save(): void
{
    if (
        !$this->kelas_id ||
        !$this->guru_id ||
        !$this->mapel_id ||
        !$this->hari ||
        !$this->jam_id
    ) {

        Notification::make()
            ->title('Data belum lengkap.')
            ->danger()
            ->send();

        return;
    }

    try {
        
        $curriculum = app(CurriculumService::class);

        $existing = JadwalPelajaran::query()
        
            ->where('kelas_id', $this->kelas_id)
            ->where('hari', $this->hari)
            ->where('jam_pelajaran_id', $this->jam_id)
        
            ->first();
        
        if ($existing) {
        
            $result = $curriculum->validateUpdate(
                $existing,
                [
                    'kelas_id' => $this->kelas_id,
                    'pegawai_id' => $this->guru_id,
                    'mata_pelajaran_id' => $this->mapel_id,
                ]
            );
        
        } else {
        
            $result = $curriculum->validateCreate(
                $this->kelas_id,
                $this->mapel_id,
                $this->guru_id,
            );
        
        }
        
        if (! $result['valid']) {
        
            Notification::make()
                ->warning()
                ->title('Validasi Kurikulum')
                ->body($result['message'])
                ->persistent()
                ->send();
        
            return;
        }

        $jam = JamPelajaran::find($this->jam_id);

        DB::transaction(function () use ($existing, $jam) {

            if ($existing) {
        
                $existing->update([
                    'pegawai_id'        => $this->guru_id,
                    'mata_pelajaran_id' => $this->mapel_id,
                    'jam_ke'            => $jam?->urutan,
                    'durasi_jam'        => $jam?->durasi_jp,
                ]);
        
            } else {
        
                JadwalPelajaran::create([
                    'kelas_id'          => $this->kelas_id,
                    'hari'              => $this->hari,
                    'jam_pelajaran_id'  => $this->jam_id,
                    'pegawai_id'        => $this->guru_id,
                    'mata_pelajaran_id' => $this->mapel_id,
                    'jam_ke'            => $jam?->urutan,
                    'durasi_jam'        => $jam?->durasi_jp,
                ]);
        
            }
        
        });

        $this->dispatch(
            'close-modal',
            id: 'input-jadwal'
        );

        $this->guru_id = null;
        $this->mapel_id = null;
        $this->hari = null;
        $this->jam_id = null;

        $this->loadGrid();

        Notification::make()
            ->title('Jadwal berhasil disimpan.')
            ->success()
            ->send();

    } catch (Throwable $e) {

    report($e);

    /*
    |--------------------------------------------------------------------------
    | Bentrok Guru
    |--------------------------------------------------------------------------
    */

    if (str_contains($e->getMessage(), 'jadwal_guru_unique')) {

        $guru = Pegawai::find($this->guru_id);
        $jam  = JamPelajaran::find($this->jam_id);

        Notification::make()

            ->warning()

            ->icon('heroicon-o-exclamation-triangle')

            ->title('Jadwal Guru Bentrok')

            ->body(
                "Guru {$guru?->nama} sudah memiliki jadwal mengajar pada {$this->hari}, {$jam?->nama}. Silakan pilih guru lain atau ubah jam pelajaran."
            )

            ->persistent()

            ->send();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Bentrok Kelas
    |--------------------------------------------------------------------------
    */

    if (str_contains($e->getMessage(), 'jadwal_kelas_unique')) {

        $jam = JamPelajaran::find($this->jam_id);

        Notification::make()

            ->warning()

            ->icon('heroicon-o-exclamation-triangle')

            ->title('Jadwal Kelas Bentrok')

            ->body(
                "Kelas ini sudah memiliki jadwal pada {$this->hari}, {$jam?->nama}. Silakan pilih jam pelajaran yang lain."
            )

            ->persistent()

            ->send();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Error Lain
    |--------------------------------------------------------------------------
    */

    Notification::make()

        ->danger()

        ->icon('heroicon-o-x-circle')

        ->title('Gagal Menyimpan Jadwal')

        ->body(
            'Terjadi kesalahan saat menyimpan jadwal. Silakan coba kembali.'
        )

        ->send();
}
}

/**
 * ==========================================================
 * HAPUS JADWAL
 * ==========================================================
 */
public function delete(
    string $hari,
    int $jamId
): void
{
    if (!$this->kelas_id) {

        Notification::make()
            ->title('Kelas belum dipilih.')
            ->danger()
            ->send();

        return;
    }

    try {

        DB::transaction(function () use ($hari, $jamId) {

            // Sel di grid bisa merepresentasikan 1 pertemuan yang
            // berisi lebih dari 1 JP berurutan (rowspan). Klik hapus
            // di sel itu harus menghapus SELURUH JP pertemuan tsb,
            // bukan cuma baris JP yang paling atas.

            $anchor = JadwalPelajaran::query()
                ->where('kelas_id', $this->kelas_id)
                ->where('hari', $hari)
                ->where('jam_pelajaran_id', $jamId)
                ->first();

            if (! $anchor) {
                return;
            }

            $kelasUntukHapus = Kelas::find($this->kelas_id);

            $jamUrutanList = JamPelajaran::query()
                ->where('lembaga_id', $kelasUntukHapus?->lembaga_id)
                ->where('aktif', true)
                ->orderBy('urutan')
                ->pluck('id')
                ->values();

            $startIndex = $jamUrutanList->search($jamId);

            $idsToDelete = [$anchor->id];

            if ($startIndex !== false) {

                for ($i = $startIndex + 1; $i < $jamUrutanList->count(); $i++) {

                    $next = JadwalPelajaran::query()
                        ->where('kelas_id', $this->kelas_id)
                        ->where('hari', $hari)
                        ->where('jam_pelajaran_id', $jamUrutanList[$i])
                        ->first();

                    if (
                        ! $next ||
                        $next->mata_pelajaran_id !== $anchor->mata_pelajaran_id ||
                        $next->pegawai_id !== $anchor->pegawai_id
                    ) {
                        break;
                    }

                    $idsToDelete[] = $next->id;
                }
            }

            JadwalPelajaran::whereIn('id', $idsToDelete)->delete();

        });

        $this->loadGrid();

        Notification::make()
            ->title('Jadwal berhasil dihapus.')
            ->success()
            ->send();

    } catch (Throwable $e) {

        report($e);

        Notification::make()
            ->title('Gagal menghapus jadwal.')
            ->body($e->getMessage())
            ->danger()
            ->send();

    }
}

/**
 * ==========================================================
 * LIST GURU
 * ==========================================================
 */
public function getGuruProperty()
{
    return Pegawai::query()
        ->select('id', 'nama')
        ->orderBy('nama')
        ->get();
}

/**
 * ==========================================================
 * LIST MATA PELAJARAN
 * ==========================================================
 */
public function getMataPelajaranProperty()
{
    return MataPelajaran::query()
        ->select('id', 'nama')
        ->orderBy('nama')
        ->get();
}
}