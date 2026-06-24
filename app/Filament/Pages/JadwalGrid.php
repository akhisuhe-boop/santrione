<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Kurikulum;

use Filament\Notifications\Notification;

class JadwalGrid extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Jadwal Pelajaran';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.jadwal-grid';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_JadwalGrid');
    }

    // 🔥 FILAMENT FORM STATE
    public $data = [
        'lembaga_id' => null,
        'kelas_id' => null,
    ];

    public $lembaga_id;
    public $kelas_id;

    public $jadwal = [];

    // modal
    public $hari;
    public $jam_ke;
    public $mapel_id;
    public $guru_id;

    public function mount()
    {
        $this->form->fill();
    }
    public function form(Form $form): Form
    {
        return $form
            ->columns([
                'default' => 1,
                'md' => 2, // 🔥 fix 2 kolom
            ])
            ->schema($this->getFormSchema());
    }
    protected function getFormSchema(): array
    {
        return [

            Forms\Components\Select::make('lembaga_id')
                ->label('Lembaga')
                ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    $set('kelas_id', null);
                    $this->jadwal = [];
                }),

            Forms\Components\Select::make('kelas_id')
                ->label('Kelas')
                ->options(function (callable $get) {
                    if (!$get('lembaga_id')) return [];

                    return Kelas::where('lembaga_id', $get('lembaga_id'))
                        ->pluck('nama', 'id');
                })
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->kelas_id = $state;
                    $this->loadJadwal();
                }),
        ];
    }

    public function updatedData()
    {
        $this->lembaga_id = $this->data['lembaga_id'];
        $this->kelas_id   = $this->data['kelas_id'];
    }

    // 🔥 GENERATE JADWAL
    public function generate()
    {
        if (!$this->kelas_id) {
            Notification::make()
                ->title('Pilih Kelas Dulu')
                ->warning()
                ->send();
            return;
        }

        JadwalPelajaran::where('kelas_id', $this->kelas_id)->delete();

        $haris = ['senin','selasa','rabu','kamis','jumat','sabtu'];
        $maxJam = 8;

        $kurikulums = Kurikulum::with('mataPelajaran')
            ->where('kelas_id', $this->kelas_id)
            ->orderByDesc('jumlah_jam_per_minggu')
            ->get();

        if ($kurikulums->isEmpty()) {
            Notification::make()
                ->title('Kurikulum Kosong')
                ->warning()
                ->send();
            return;
        }

        foreach ($kurikulums as $k) {

            // 🔥 JP → PERTEMUAN
            $pertemuan = intdiv($k->jumlah_jam_per_minggu, 2); // FIX (bukan ceil)

            $try = 0;

            while ($pertemuan > 0 && $try < 200) {
                $try++;

                shuffle($haris);
                $jamList = range(1, $maxJam - 1);
                shuffle($jamList);

                $placed = false;

                foreach ($haris as $hari) {
                    foreach ($jamList as $jam1) {

                        $jam2 = $jam1 + 1;

                        // ❌ jangan 2x di hari sama
                        $existHari = JadwalPelajaran::where([
                            'kelas_id' => $this->kelas_id,
                            'hari' => $hari,
                            'mata_pelajaran_id' => $k->mata_pelajaran_id
                        ])->exists();

                        if ($existHari) continue;

                        // ❌ bentrok kelas
                        $kelasBentrok = JadwalPelajaran::where('kelas_id', $this->kelas_id)
                            ->where('hari', $hari)
                            ->whereIn('jam_ke', [$jam1, $jam2])
                            ->exists();

                        if ($kelasBentrok) continue;

                        // ❌ bentrok guru
                        $guruBentrok = JadwalPelajaran::where('pegawai_id', $k->pegawai_id)
                            ->where('hari', $hari)
                            ->whereIn('jam_ke', [$jam1, $jam2])
                            ->exists();

                        if ($guruBentrok) continue;

                        // ✅ INSERT 2 JP (1 pertemuan)
                        JadwalPelajaran::create([
                            'kelas_id' => $this->kelas_id,
                            'hari' => $hari,
                            'jam_ke' => $jam1,
                            'durasi_jam' => 2,
                            'pegawai_id' => $k->pegawai_id,
                            'mata_pelajaran_id' => $k->mata_pelajaran_id,
                        ]);

                        $pertemuan--;
                        $placed = true;

                        break 2;
                    }
                }

                if (!$placed && $try >= 200) {
                    Notification::make()
                        ->title('Gagal Generate')
                        ->body("Mapel {$k->mataPelajaran->nama} bentrok")
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        $this->loadJadwal();

        Notification::make()
            ->title('Berhasil')
            ->success()
            ->send();
    }

    public function loadJadwal()
    {
        if (!$this->kelas_id) return;

        $this->jadwal = [];

        $data = JadwalPelajaran::with(['mataPelajaran','guru'])
            ->where('kelas_id', $this->kelas_id)
            ->get();

        foreach ($data as $item) {
            $this->jadwal[$item->hari][$item->jam_ke] = $item;
        }
    }

    public function openModal($hari, $jam)
    {
        $this->hari = $hari;
        $this->jam_ke = $jam;

        $this->mapel_id = null;
        $this->guru_id = null;

        $this->dispatch('open-modal', id: 'input-jadwal');
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', id: 'input-jadwal');
    }

    public function save()
    {
        if (!$this->mapel_id || !$this->guru_id || !$this->kelas_id) return;

        // ❌ bentrok guru
        if (JadwalPelajaran::where('pegawai_id', $this->guru_id)
            ->where('hari', $this->hari)
            ->where('jam_ke', $this->jam_ke)
            ->exists()) {

            Notification::make()
                ->title('Bentrok Guru')
                ->danger()
                ->send();
            return;
        }

        // ❌ bentrok kelas
        if (JadwalPelajaran::where('kelas_id', $this->kelas_id)
            ->where('hari', $this->hari)
            ->where('jam_ke', $this->jam_ke)
            ->exists()) {

            Notification::make()
                ->title('Slot terisi')
                ->danger()
                ->send();
            return;
        }

        JadwalPelajaran::create([
            'kelas_id' => $this->kelas_id,
            'hari' => $this->hari,
            'jam_ke' => $this->jam_ke,
            'durasi_jam' => 2,
            'pegawai_id' => $this->guru_id,
            'mata_pelajaran_id' => $this->mapel_id,
        ]);

        $this->loadJadwal();
        $this->closeModal();
    }

    public function delete($hari, $jam)
    {
        JadwalPelajaran::where([
            'kelas_id' => $this->kelas_id,
            'hari' => $hari,
            'jam_ke' => $jam,
        ])->delete();

        $this->loadJadwal();
    }
}