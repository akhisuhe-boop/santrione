<?php

namespace App\Filament\Pages;

use App\Models\Lembaga;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TableAction;

use Filament\Notifications\Notification;

class PengaturanKurikulum extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string $view = 'filament.pages.pengaturan-kurikulum';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $title = 'Pengaturan Kurikulum';

    protected static ?string $navigationLabel = 'Pengaturan Kurikulum';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_PengaturanKurikulum');
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
            'max_jp_kelas_per_minggu' => $lembaga?->max_jp_kelas_per_minggu,
            'max_jp_guru_per_minggu' => $lembaga?->max_jp_guru_per_minggu,
            'warning_jp_guru_per_minggu' => $lembaga?->warning_jp_guru_per_minggu,
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

                TextColumn::make('max_jp_kelas_per_minggu')
                    ->label('Maks JP / Kelas')
                    ->suffix(' JP')
                    ->placeholder('Tanpa batas'),

                TextColumn::make('max_jp_guru_per_minggu')
                    ->label('Maks JP / Guru')
                    ->suffix(' JP')
                    ->placeholder('Tanpa batas'),

                TextColumn::make('warning_jp_guru_per_minggu')
                    ->label('Peringatan JP Guru')
                    ->suffix(' JP')
                    ->placeholder('Tidak ada'),

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

                \Filament\Forms\Components\Grid::make(2)
                    ->schema([

                        Section::make('Batas JP per Kelas')
                            ->description('Total JP semua mata pelajaran dalam satu kelas per minggu. Kosongkan untuk tanpa batas.')
                            ->icon('heroicon-o-book-open')
                            ->schema([

                                TextInput::make('max_jp_kelas_per_minggu')
                                    ->label('Maksimal JP per Kelas / Minggu')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable()
                                    ->suffix('JP')
                                    ->helperText('Contoh: SMA/SMK biasanya 40-56 JP, SD/MI biasanya 24-36 JP.'),

                            ]),

                        Section::make('Batas JP per Guru')
                            ->description('Total JP mengajar seorang guru per minggu, dijumlahkan dari semua kelas & mapel yang diampu. Kosongkan untuk tanpa batas.')
                            ->icon('heroicon-o-user')
                            ->schema([

                                TextInput::make('max_jp_guru_per_minggu')
                                    ->label('Maksimal JP Guru / Minggu (batas keras, ditolak jika lebih)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable()
                                    ->suffix('JP'),

                                TextInput::make('warning_jp_guru_per_minggu')
                                    ->label('Ambang Peringatan JP Guru / Minggu (cuma peringatan, tetap bisa disimpan)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable()
                                    ->suffix('JP'),

                            ]),

                    ]),

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
            'max_jp_kelas_per_minggu' => $this->data['max_jp_kelas_per_minggu'] ?? null,
            'max_jp_guru_per_minggu' => $this->data['max_jp_guru_per_minggu'] ?? null,
            'warning_jp_guru_per_minggu' => $this->data['warning_jp_guru_per_minggu'] ?? null,
        ]);

        Notification::make()
            ->title('Pengaturan kurikulum berhasil disimpan')
            ->success()
            ->send();
    }
}
