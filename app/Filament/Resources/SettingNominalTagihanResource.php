<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingNominalTagihanResource\Pages;
use App\Models\SettingNominalTagihan;
use App\Models\TahunAjaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use filament\components\CheckboxList;
use Filament\Support\RawJs;

class SettingNominalTagihanResource extends BaseResource
{
    protected static ?string $model = SettingNominalTagihan::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $label = 'Tagihan Khusus';
    protected static ?string $pluralLabel = 'Tagihan Khusus';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Placeholder::make('info')
                ->hiddenLabel()
                ->content(new \Illuminate\Support\HtmlString('
                    <div style="
                        padding:12px;
                        background:#FEF3C7;
                        border:1px solid #FCD34D;
                        color:#92400E;
                        border-radius:8px;
                        font-size:14px;
                        display:flex;
                        gap:8px;
                        align-items:flex-start;
                    ">
                        <span style="font-size:16px;">⚠️</span>
                        <span>
                            Gunakan setting ini hanya jika nominal berbeda dari tagihan umum.
                            Jika tidak diisi, sistem akan menggunakan nominal dari Jenis Tagihan Umum.
                        </span>
                    </div>
                '))
                ->columnSpanFull(),

                Forms\Components\Section::make('Setting Nominal')
                    ->schema([

                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->relationship(
                                'lembaga',
                                'nama',
                                modifyQueryUsing: fn ($query) => $query->where(
                                    'yayasan_id',
                                    \Filament\Facades\Filament::getTenant()?->id
                                ),
                            )
                            ->required()
                            ->preload()
                            ->searchable(),

                        Forms\Components\Select::make('jenis_tagihan_id')
                            ->relationship('jenisTagihan', 'nama')
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(function () {
                                return TahunAjaran::where('aktif', true)
                                    ->pluck('nama', 'id');
                            })
                            ->default(function () {
                                return TahunAjaran::where('aktif', true)->value('id');
                            })
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Select::make('siswa_ids')
                            ->label('Siswa (Pilih Jika Khusus Siswa Tertentu)')
                            ->multiple()
                            ->options(\App\Models\Siswa::pluck('nama_lengkap', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive() // 🔥 WAJIB INI
                            ->helperText('Bisa pilih lebih dari satu siswa')
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!empty($state)) {
                                    $set('kelas_id', null);
                                }
                            }),

                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function () {
                                return ['all' => 'Semua Kelas'] + 
                                    \App\Models\Kelas::pluck('nama', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->reactive() // 🔥 tambahkan juga ini
                            ->helperText('Jika memilih siswa, kelas akan diabaikan')
                            ->disabled(fn ($get) => !empty($get('siswa_ids')))
                            ->dehydrateStateUsing(fn ($state) => $state === 'all' ? null : $state),

                        Forms\Components\CheckboxList::make('bulan')
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
                            ->label('Bulan')
                            ->bulkToggleable() // ✅ ini pengganti select all
                            ->visible(fn ($get) => 
                                \App\Models\JenisTagihan::where('id', $get('jenis_tagihan_id'))
                                    ->value('is_bulanan') == 1
                            )
                            ->required(fn ($get) => 
                                \App\Models\JenisTagihan::where('id', $get('jenis_tagihan_id'))
                                    ->value('is_bulanan') == 1
                            ),

                        Forms\Components\TextInput::make('nominal')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->required()
                            ->prefix('Rp')
                            ->helperText('Isi hanya jika nominal berbeda dari default')
                            ->rule(function ($get, $record) {
                            return function ($attribute, $value, $fail) use ($get, $record) {

                                $siswaIds = $get('siswa_ids') ?? [];

                                // 🔥 VALIDASI: tidak boleh siswa & kelas bersamaan
                                if (!empty($siswaIds) && $get('kelas_id')) {
                                    $fail('Tidak boleh pilih siswa dan kelas sekaligus.');
                                    return;
                                }

                                // 🔥 Loop kalau multi siswa
                                if (!empty($siswaIds)) {

                                    foreach ($siswaIds as $siswaId) {

                                        $query = \App\Models\SettingNominalTagihan::query()
                                            ->where('jenis_tagihan_id', $get('jenis_tagihan_id'))
                                            ->where('tahun_ajaran_id', $get('tahun_ajaran_id'))
                                            ->where('siswa_id', $siswaId);

                                        // bulan
                                        if ($get('bulan')) {
                                            $query->where(function ($q) use ($get) {
                                                foreach ($get('bulan') as $b) {
                                                    $q->orWhereJsonContains('bulan', $b);
                                                }
                                            });
                                        } else {
                                            $query->whereNull('bulan');
                                        }

                                        // 🔥 skip diri sendiri saat edit
                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('Setting untuk salah satu siswa sudah ada.');
                                            return;
                                        }
                                    }

                                } else {

                                    // 🔥 kondisi kelas / umum
                                    $query = \App\Models\SettingNominalTagihan::query()
                                        ->where('jenis_tagihan_id', $get('jenis_tagihan_id'))
                                        ->where('tahun_ajaran_id', $get('tahun_ajaran_id'));

                                    // siswa null
                                    $query->whereNull('siswa_id');

                                    // kelas
                                    if ($get('kelas_id')) {
                                        $query->where('kelas_id', $get('kelas_id'));
                                    } else {
                                        $query->whereNull('kelas_id');
                                    }

                                    // bulan
                                    if ($get('bulan')) {
                                        $query->where(function ($q) use ($get) {
                                            foreach ($get('bulan') as $b) {
                                                $q->orWhereJsonContains('bulan', $b);
                                            }
                                        });
                                    } else {
                                        $query->whereNull('bulan');
                                    }

                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }

                                    if ($query->exists()) {
                                        $fail('Setting nominal untuk kombinasi ini sudah ada.');
                                    }
                                }
                            };
                        })
                                            ])
                                    ->columns(3),

                                    ])
                                    ->columns(2);
                            }

public static function table(Table $table): Table
{
    return $table

        ->modifyQueryUsing(function ($query) {
            return $query
                ->selectRaw('
                    MIN(id) as id,
                    jenis_tagihan_id,
                    tahun_ajaran_id,
                    kelas_id,
                    bulan,
                    nominal,
                    GROUP_CONCAT(siswa_id) as siswa_ids
                ')
                ->groupBy(
                    'jenis_tagihan_id',
                    'tahun_ajaran_id',
                    'kelas_id',
                    'bulan',
                    'nominal'
                );
        })

        ->columns([

            // 🔥 JENIS TAGIHAN (AMAN)
            Tables\Columns\TextColumn::make('jenis_tagihan_id')
                ->label('Jenis')
                ->getStateUsing(fn ($record) =>
                    \App\Models\JenisTagihan::find($record->jenis_tagihan_id)?->nama ?? '-'
                ),

            // 🔥 TAHUN AJARAN
            Tables\Columns\TextColumn::make('tahun_ajaran_id')
                ->label('Tahun Ajaran')
                ->getStateUsing(fn ($record) =>
                    \App\Models\TahunAjaran::find($record->tahun_ajaran_id)?->nama ?? '-'
                ),

            // 🔥 SISWA (DIGABUNG)
            Tables\Columns\TextColumn::make('siswa_ids')
                ->label('Siswa')
                ->getStateUsing(function ($record) {

                    if (!$record->siswa_ids) return '-';

                    static $cache = [];

                    $ids = explode(',', $record->siswa_ids);

                    $missing = array_diff($ids, array_keys($cache));

                    if ($missing) {
                        $data = \App\Models\Siswa::whereIn('id', $missing)
                            ->pluck('nama_lengkap', 'id');

                        foreach ($data as $id => $nama) {
                            $cache[$id] = $nama;
                        }
                    }

                    return collect($ids)
                        ->map(fn ($id) => $cache[$id] ?? '-')
                        ->implode(', ');
                })
                ->limit(30)
                ->tooltip(fn ($state) => $state),

            // 🔥 KELAS
            Tables\Columns\TextColumn::make('kelas_id')
                ->label('Kelas')
                ->getStateUsing(function ($record) {
                    if (!$record->kelas_id) return 'Semua Kelas';

                    return \App\Models\Kelas::find($record->kelas_id)?->nama ?? '-';
                }),

            // 🔥 BULAN
            Tables\Columns\TextColumn::make('bulan')
                ->label('Bulan')
                ->getStateUsing(function ($record) {

                    $jenis = \App\Models\JenisTagihan::find($record->jenis_tagihan_id);

                    if (!$jenis?->is_bulanan) return '-';
                    if (!$record->bulan) return '-';

                    $state = $record->bulan;

                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        $state = json_last_error() === JSON_ERROR_NONE
                            ? $decoded
                            : explode(',', $state);
                    }

                    if (!is_array($state)) {
                        $state = [$state];
                    }

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

                    return collect($state)
                        ->map(fn ($b) => $bulanMap[trim($b)] ?? $b)
                        ->implode(', ');
                }),

            // 🔥 NOMINAL
            Tables\Columns\TextColumn::make('nominal')
                ->label('Nominal')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format($state, 0, ',', '.')
                ),
        ])

        //->actions([
            //Tables\Actions\EditAction::make(),
        //])

        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettingNominalTagihans::route('/'),
            'create' => Pages\CreateSettingNominalTagihan::route('/create'),
            'edit' => Pages\EditSettingNominalTagihan::route('/{record}/edit'),
        ];
    }
}