<?php

namespace App\Filament\Pages;

use App\Models\Lembaga;
use App\Models\JadwalAbsensiHarian;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TableAction;

use Filament\Notifications\Notification;

/**
 * DIUBAH TOTAL (16 Sep 2026) -- jam absensi sekarang PER HARI, bukan
 * general lagi (lihat migration create_jadwal_absensi_harians_table
 * untuk alasan lengkapnya). Tiap hari punya set jam sendiri-sendiri,
 * dan BOLEH DIKOSONGKAN kalau memang hari itu bukan hari sekolah
 * (libur) -- hari yang kosong otomatis TIDAK dihitung wajib absen
 * sama sekali (dicek di TandaiAlpaAbsensiHarian & AbsensiHarianController).
 */
class PengaturanAbsensi extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.pengaturan-absensi';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $title = 'Pengaturan Jam Absensi';

    protected static ?string $navigationLabel = 'Pengaturan Jam Absensi';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return \App\Support\FeatureGate::tenantBolehLihatGrup(static::$navigationGroup)
            && auth()->user()->can('page_PengaturanAbsensi');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $lembaga = Lembaga::orderBy('nama')->first();

        $this->fillFromLembaga($lembaga);
    }

    protected function fillFromLembaga(?Lembaga $lembaga): void
    {
        $isiHari = [];

        foreach (JadwalAbsensiHarian::HARI as $hari) {
            $jadwal = $lembaga?->jadwalAbsensiUntukHari($hari);

            $isiHari[$hari] = [
                'jam_masuk_siswa' => $jadwal?->jam_masuk_siswa,
                'jam_pulang_siswa' => $jadwal?->jam_pulang_siswa,
                'jam_masuk_guru' => $jadwal?->jam_masuk_guru,
                'jam_pulang_guru' => $jadwal?->jam_pulang_guru,
                'toleransi_telat_menit' => $jadwal?->toleransi_telat_menit ?? 15,
            ];
        }

        $this->form->fill([
            'lembaga_id' => $lembaga?->id,
            'hari' => $isiHari,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Lembaga::query()->orderBy('nama'))
            ->columns([

                TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable(),

                TextColumn::make('jadwal_absensi_harians_count')
                    ->label('Hari Diatur')
                    ->counts('jadwalAbsensiHarians')
                    ->formatStateUsing(fn ($state) => $state . ' / 7 hari'),

            ])
            ->actions([

                TableAction::make('atur')
                    ->label('Atur')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->action(function (Lembaga $record) {
                        $record->load('jadwalAbsensiHarians');
                        $this->fillFromLembaga($record);
                    }),

            ])
            ->paginated(false);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Pilih Lembaga')
                    ->schema([
                        Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->options(Lembaga::orderBy('nama')->pluck('nama', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $lembaga = Lembaga::find($state);
                                $lembaga?->load('jadwalAbsensiHarians');
                                $this->fillFromLembaga($lembaga);
                            })
                            ->searchable()
                            ->preload(),
                    ]),

                Tabs::make('Hari')
                    ->tabs(
                        collect(JadwalAbsensiHarian::HARI)->map(
                            fn (string $hari) => Tab::make($hari)
                                ->schema([

                                    TimePicker::make("hari.{$hari}.jam_masuk_siswa")
                                        ->label('Jam Masuk Siswa')
                                        ->seconds(false),

                                    TimePicker::make("hari.{$hari}.jam_pulang_siswa")
                                        ->label('Jam Pulang Siswa')
                                        ->seconds(false),

                                    TextInput::make("hari.{$hari}.toleransi_telat_menit")
                                        ->label('Toleransi Terlambat (menit)')
                                        ->numeric()
                                        ->default(15)
                                        ->suffix('menit'),

                                    TimePicker::make("hari.{$hari}.jam_masuk_guru")
                                        ->label('Jam Masuk Guru/Pegawai')
                                        ->seconds(false),

                                    TimePicker::make("hari.{$hari}.jam_pulang_guru")
                                        ->label('Jam Pulang Guru/Pegawai')
                                        ->seconds(false),

                                ])
                                ->columns(3)
                        )->all()
                    )
                    ->columnSpanFull(),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $lembagaId = $data['lembaga_id'] ?? null;

        $lembaga = Lembaga::find($lembagaId);

        if (!$lembaga) {
            Notification::make()
                ->title('Pilih lembaga terlebih dahulu')
                ->danger()
                ->send();

            return;
        }

        foreach (JadwalAbsensiHarian::HARI as $hari) {
            $isi = $data['hari'][$hari] ?? [];

            // Kalau SEMUA field kosong buat hari ini -> hapus baris
            // (kalau ada) supaya hari itu benar-benar dianggap "tidak
            // diatur" (libur), bukan tersimpan dengan nilai kosong.
            $adaIsi = collect($isi)->except('toleransi_telat_menit')
                ->filter()
                ->isNotEmpty();

            if (!$adaIsi) {
                JadwalAbsensiHarian::where('lembaga_id', $lembaga->id)
                    ->where('hari', $hari)
                    ->delete();

                continue;
            }

            JadwalAbsensiHarian::updateOrCreate(
                ['lembaga_id' => $lembaga->id, 'hari' => $hari],
                [
                    'jam_masuk_siswa' => $isi['jam_masuk_siswa'] ?? null,
                    'jam_pulang_siswa' => $isi['jam_pulang_siswa'] ?? null,
                    'jam_masuk_guru' => $isi['jam_masuk_guru'] ?? null,
                    'jam_pulang_guru' => $isi['jam_pulang_guru'] ?? null,
                    'toleransi_telat_menit' => $isi['toleransi_telat_menit'] ?? 15,
                ]
            );
        }

        Notification::make()
            ->title('Pengaturan jam absensi berhasil disimpan')
            ->success()
            ->send();
    }
}
