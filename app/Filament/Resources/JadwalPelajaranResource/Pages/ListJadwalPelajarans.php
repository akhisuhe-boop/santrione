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

        $jamPelajaran = JamPelajaran::query()

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

                $row['hari'][$h] = optional(

                    $jadwal->get($key)

                )->first();

            }

            $grid[] = $row;

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

        DB::transaction(function () use ($existing) {

            if ($existing) {
        
                $existing->update([
                    'pegawai_id'        => $this->guru_id,
                    'mata_pelajaran_id' => $this->mapel_id,
                ]);
        
            } else {
        
                JadwalPelajaran::create([
                    'kelas_id'          => $this->kelas_id,
                    'hari'              => $this->hari,
                    'jam_pelajaran_id'  => $this->jam_id,
                    'pegawai_id'        => $this->guru_id,
                    'mata_pelajaran_id' => $this->mapel_id,
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

            JadwalPelajaran::query()

                ->where('kelas_id', $this->kelas_id)

                ->where('hari', $hari)

                ->where('jam_pelajaran_id', $jamId)

                ->delete();

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