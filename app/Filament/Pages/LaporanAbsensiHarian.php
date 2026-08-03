<?php

namespace App\Filament\Pages;

use App\Models\AbsensiHarian;
use App\Models\Kelas;
use App\Models\Lembaga;

use Filament\Pages\Page;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;

class LaporanAbsensiHarian extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static string $view = 'filament.pages.laporan-absensi-harian';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $title = 'Absensi Masuk & Pulang';

    protected static ?string $navigationLabel = 'Absensi Masuk & Pulang';

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_LaporanAbsensiHarian');
    }

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_awal' => now()->startOfMonth()->format('Y-m-d'),
            'tanggal_akhir' => now()->format('Y-m-d'),
        ]);
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
        return $form
            ->schema([

                DatePicker::make('tanggal_awal')
                    ->label('Dari Tanggal')
                    ->native(false),

                DatePicker::make('tanggal_akhir')
                    ->label('Sampai Tanggal')
                    ->native(false),

                Select::make('tipe')
                    ->label('Tipe')
                    ->placeholder('Semua')
                    ->options([
                        'siswa' => 'Siswa',
                        'guru' => 'Guru / Pegawai',
                    ]),

                Select::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->preload()
                    ->options(Kelas::orderBy('nama')->pluck('nama', 'id')),

                Group::make([
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

                    ])
                ])->extraAttributes([
                    'class' => 'flex items-end h-full pb-1'
                ]),

            ])
            ->statePath('formData')
            ->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AbsensiHarian::query()
                    ->with(['siswa.kelas', 'pegawai'])

                    ->when(
                        $this->formData['tanggal_awal'] ?? null,
                        fn ($q, $v) => $q->whereDate('tanggal', '>=', $v)
                    )

                    ->when(
                        $this->formData['tanggal_akhir'] ?? null,
                        fn ($q, $v) => $q->whereDate('tanggal', '<=', $v)
                    )

                    ->when(
                        $this->formData['tipe'] ?? null,
                        fn ($q, $v) => $q->where('tipe', $v)
                    )

                    ->when(
                        $this->formData['kelas'] ?? null,
                        fn ($q, $v) => $q->whereHas('siswa', fn ($sub) => $sub->where('kelas_id', $v))
                    )

                    ->latest('tanggal')
            )
            ->columns([

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->getStateUsing(fn ($record) => $record->tipe === 'siswa'
                        ? ($record->siswa?->nama_lengkap ?? '-')
                        : ($record->pegawai?->nama ?? '-')
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$search}%"))
                              ->orWhereHas('pegawai', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
                        });
                    }),

                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'siswa' ? 'Siswa' : 'Guru/Pegawai')
                    ->color(fn ($state) => $state === 'siswa' ? 'info' : 'warning'),

                TextColumn::make('siswa.kelas.nama')
                    ->label('Kelas')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->dateTime('H:i')
                    ->placeholder('-'),

                TextColumn::make('status_masuk')
                    ->label('Status Masuk')
                    ->badge()
                    ->color(fn ($state) => $state === 'Terlambat' ? 'danger' : 'success')
                    ->placeholder('-'),

                TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->dateTime('H:i')
                    ->placeholder('-'),

                TextColumn::make('status_pulang')
                    ->label('Status Pulang')
                    ->badge()
                    ->color(fn ($state) => $state === 'Pulang Awal' ? 'warning' : 'success')
                    ->placeholder('-'),

            ])
            ->defaultSort('tanggal', 'desc');
    }
}
