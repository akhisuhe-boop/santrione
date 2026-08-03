<?php

namespace App\Filament\Pages;

use App\Models\Lembaga;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TableAction;

use Filament\Notifications\Notification;

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
        return auth()->user()->can('page_PengaturanAbsensi');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $lembaga = Lembaga::orderBy('nama')->first();

        $this->fillFromLembaga($lembaga);
    }

    protected function fillFromLembaga(?Lembaga $lembaga): void
    {
        $this->form->fill([
            'lembaga_id' => $lembaga?->id,
            'jam_masuk_siswa' => $lembaga?->jam_masuk_siswa,
            'jam_pulang_siswa' => $lembaga?->jam_pulang_siswa,
            'jam_masuk_guru' => $lembaga?->jam_masuk_guru,
            'jam_pulang_guru' => $lembaga?->jam_pulang_guru,
            'toleransi_telat_menit' => $lembaga?->toleransi_telat_menit ?? 15,
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

                TextColumn::make('jam_masuk_siswa')
                    ->label('Masuk Siswa')
                    ->time('H:i')
                    ->placeholder('Belum diatur'),

                TextColumn::make('jam_pulang_siswa')
                    ->label('Pulang Siswa')
                    ->time('H:i')
                    ->placeholder('Belum diatur'),

                TextColumn::make('jam_masuk_guru')
                    ->label('Masuk Guru')
                    ->time('H:i')
                    ->placeholder('Belum diatur'),

                TextColumn::make('jam_pulang_guru')
                    ->label('Pulang Guru')
                    ->time('H:i')
                    ->placeholder('Belum diatur'),

                TextColumn::make('toleransi_telat_menit')
                    ->label('Toleransi')
                    ->suffix(' menit'),

            ])
            ->actions([

                TableAction::make('atur')
                    ->label('Atur')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->action(function (Lembaga $record) {
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
                            ->afterStateUpdated(fn ($state) => $this->fillFromLembaga(Lembaga::find($state)))
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Jam Absensi Masuk & Pulang')
                    ->description('Dipakai untuk menentukan status Terlambat / Pulang Awal pada fitur Absensi Masuk & Pulang')
                    ->icon('heroicon-o-clock')
                    ->schema([

                        TimePicker::make('jam_masuk_siswa')
                            ->label('Jam Masuk Siswa')
                            ->seconds(false),

                        TimePicker::make('jam_pulang_siswa')
                            ->label('Jam Pulang Siswa')
                            ->seconds(false),

                        TextInput::make('toleransi_telat_menit')
                            ->label('Toleransi Terlambat (menit)')
                            ->numeric()
                            ->default(15)
                            ->suffix('menit'),

                        TimePicker::make('jam_masuk_guru')
                            ->label('Jam Masuk Guru/Pegawai')
                            ->seconds(false),

                        TimePicker::make('jam_pulang_guru')
                            ->label('Jam Pulang Guru/Pegawai')
                            ->seconds(false),

                    ])
                    ->columns(3),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $lembagaId = $this->data['lembaga_id'] ?? null;

        $lembaga = Lembaga::find($lembagaId);

        if (!$lembaga) {
            Notification::make()
                ->title('Pilih lembaga terlebih dahulu')
                ->danger()
                ->send();

            return;
        }

        $lembaga->update([
            'jam_masuk_siswa' => $this->data['jam_masuk_siswa'] ?? null,
            'jam_pulang_siswa' => $this->data['jam_pulang_siswa'] ?? null,
            'jam_masuk_guru' => $this->data['jam_masuk_guru'] ?? null,
            'jam_pulang_guru' => $this->data['jam_pulang_guru'] ?? null,
            'toleransi_telat_menit' => $this->data['toleransi_telat_menit'] ?? 15,
        ]);

        Notification::make()
            ->title('Pengaturan jam absensi berhasil disimpan')
            ->success()
            ->send();
    }
}
