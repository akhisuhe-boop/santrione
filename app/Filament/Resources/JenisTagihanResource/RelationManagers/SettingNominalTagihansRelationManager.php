<?php

namespace App\Filament\Resources\JenisTagihanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\TahunAjaran;
use Filament\Tables\Actions\CreateAction;
use Filament\Support\RawJs;

class SettingNominalTagihansRelationManager extends RelationManager
{
    protected static string $relationship = 'settingNominalTagihans';

    protected static ?string $title = 'Setting Khusus (Kelas / Siswa / Bulan)';

    // ======================
    // 🔥 FORM FINAL
    // ======================
    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Placeholder::make('info')
                ->hiddenLabel()
                ->content('Gunakan hanya jika nominal berbeda dari default')
                ->columnSpanFull(),

            // ======================
            // 📅 TAHUN AJARAN
            // ======================
            Forms\Components\Select::make('tahun_ajaran_id')
                ->label('Tahun Ajaran')
                ->options(TahunAjaran::where('aktif', true)->pluck('nama', 'id'))
                ->default(TahunAjaran::where('aktif', true)->value('id'))
                ->disabled()
                ->dehydrated()
                ->required(),

            // ======================
            // 🏫 LEMBAGA
            // ======================
            Forms\Components\Select::make('lembaga_id')
                ->label('Lembaga')
                ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                ->searchable()
                ->preload()
                ->reactive()
                ->helperText('Kosongkan jika berlaku semua lembaga')
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('kelas_id', null);
                    $set('siswa_ids', null);
                }),

            // ======================
            // 👤 SISWA (MULTI)
            // ======================
            Forms\Components\Select::make('siswa_ids')
                ->label('Siswa')
                ->multiple()
                ->options(function ($get) {
                    if ($get('lembaga_id')) {
                        return \App\Models\Siswa::where('lembaga_id', $get('lembaga_id'))
                            ->pluck('nama_lengkap', 'id');
                    }
                    return [];
                })
                ->searchable()
                ->preload()
                ->reactive()
                ->disabled(fn ($get) => empty($get('lembaga_id')))
                ->helperText('Pilih lembaga terlebih dahulu')
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!empty($state)) {
                        $set('kelas_id', null);
                    }
                }),

            // ======================
            // 🏫 KELAS
            // ======================
            Forms\Components\Select::make('kelas_id')
                ->label('Kelas')
                ->options(function ($get) {
                    if ($get('lembaga_id')) {
                        return \App\Models\Kelas::where('lembaga_id', $get('lembaga_id'))
                            ->pluck('nama', 'id');
                    }
                    return [];
                })
                ->searchable()
                ->preload()
                ->reactive()
                ->disabled(fn ($get) => !empty($get('siswa_ids')))
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $set('siswa_ids', null);
                    }
                })
                ->helperText('Kosongkan jika berlaku semua kelas'),

            // ======================
            // 📆 BULAN
            // ======================
            Forms\Components\CheckboxList::make('bulan')
                ->label('Bulan')
                ->options([
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember',
                ])
                ->columns(3)
                ->bulkToggleable()
                ->visible(fn () => $this->getOwnerRecord()->is_bulanan)
                ->dehydrated(fn () => $this->getOwnerRecord()->is_bulanan)
                ->required(fn () => $this->getOwnerRecord()->is_bulanan),

            // ======================
            // 💰 NOMINAL
            // ======================
            Forms\Components\TextInput::make('nominal')
                ->numeric()
                ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                ->stripCharacters('.')
                ->prefix('Rp')
                ->required(fn ($get) =>
                    empty($get('siswa_ids')) &&
                    empty($get('kelas_id')) &&
                    empty($get('lembaga_id'))
                ),
        ]);
    }

    // ======================
    // 🔥 CREATE ACTION (MULTI SISWA)
    // ======================
    public function table(Table $table): Table
{
    return $table

        // ======================
        // 🔥 HEADER ACTION (FIX FINAL)
        // ======================
        ->headerActions([
        CreateAction::make()
            ->label('Buat setting nominal tagihan')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->using(function (array $data) {

                $siswaIds = $data['siswa_ids'] ?? [];

                unset($data['siswa_ids']);

                // tanpa siswa
                if (empty($siswaIds)) {
                    return $this->getRelationship()->create($data);
                }

                // multi siswa
                $lastRecord = null;

                foreach ($siswaIds as $siswaId) {
                        $siswa = \App\Models\Siswa::find($siswaId);
                        $lastRecord = $this->getRelationship()->create([
                        ...$data,
                        'siswa_id' => $siswaId,
                        'kelas_id' => $siswa?->kelas_id ?? null,
                    ]);
                }

                return $lastRecord; // 🔥 FIX ERROR
            }),
    ])

        ->columns([

            Tables\Columns\TextColumn::make('tahunAjaran.nama')
                ->label('Tahun Ajaran')
                ->placeholder('-'),

            Tables\Columns\TextColumn::make('lembaga.nama')
                ->label('Lembaga')
                ->placeholder('Semua Lembaga'),

            Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                ->label('Siswa')
                ->placeholder('-'),

            Tables\Columns\TextColumn::make('kelas.nama')
                ->label('Kelas')
                ->placeholder('Semua Kelas'),

            Tables\Columns\TextColumn::make('bulan')
                ->label('Bulan')
                ->getStateUsing(function ($record) {

                    if (empty($record->bulan)) return '-';

                    $bulanMap = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ];

                    return collect($record->bulan)
                        ->map(fn ($b) => $bulanMap[str_pad($b, 2, '0', STR_PAD_LEFT)] ?? $b)
                        ->implode(', ');
                }),

            Tables\Columns\TextColumn::make('nominal')
                ->label('Nominal')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format($state, 0, ',', '.')
                ),
        ])

        ->actions([
            Tables\Actions\DeleteAction::make(),
        ])

        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
}
}