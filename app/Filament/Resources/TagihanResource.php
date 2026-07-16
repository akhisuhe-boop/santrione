<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagihanResource\Pages;
use App\Models\Tagihan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\Action;
use Filament\Support\RawJs;

class TagihanResource extends BaseResource
{
    protected static ?string $model = Tagihan::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Generate Tagihan';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $label = 'Generate Tagihan';
    protected static ?string $pluralLabel = 'Generate Tagihan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data Tagihan')
                ->schema([

                    Select::make('siswa_id')
                        ->relationship('siswa', 'nama_lengkap')
                        ->searchable()
                        ->required(),

                    Select::make('jenis_tagihan_id')
                        ->label('Jenis Tagihan')
                        ->relationship('jenisTagihan', 'nama')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {

                            $jenis = \App\Models\JenisTagihan::find($state);

                            if ($jenis) {
                                $set('nominal', $jenis->default_nominal);
                                $set('is_bulanan', $jenis->is_bulanan);
                            }
                        }),

                    Hidden::make('is_bulanan'),

                    CheckboxList::make('bulan')
                        ->label('Pilih Bulan')
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
                        ->visible(fn ($get) => $get('is_bulanan')),

                    Forms\Components\TextInput::make('judul')
                    ->label('Nama Tagihan')
                    ->required(),

                    Forms\Components\TextInput::make('nominal')
                        ->numeric()
                        ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                        ->stripCharacters('.')
                        ->required()
                        ->prefix('Rp'),

                    Forms\Components\DatePicker::make('jatuh_tempo')
                        ->visible(fn ($get) => !$get('is_bulanan'))
                        ->required(fn ($get) => !$get('is_bulanan')),

                    Select::make('rekening_id')
                        ->label('Rekening')
                        ->options(
                            \App\Models\Rekening::get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->nama . ' - ' . $r->bank . ' (' . $r->no_rekening . ')'
                                ])
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Textarea::make('keterangan'),

                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->getStateUsing(fn ($record) =>
                        $record->siswa?->nama_lengkap
                        ?? $record->ppdb?->nama_lengkap
                        ?? '-'
                    )
                    ->description(fn ($record) =>
                        ! $record->siswa && $record->ppdb ? 'Calon siswa (PPDB)' : null
                    ),
                Tables\Columns\TextColumn::make('judul')
                ->label('Jenis Tagihan'),
                Tables\Columns\TextColumn::make('nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('nominal_terbayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Belum' => 'Belum Lunas',
                        'sebagian' => 'Sebagian',
                        'lunas' => 'Lunas',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'Belum',
                        'warning' => 'sebagian',
                        'success' => 'lunas',
                    ]),
                Tables\Columns\TextColumn::make('tahunAjaran.nama'),
                Tables\Columns\TextColumn::make('periodeTahunAjaran.nama')->placeholder('-'),
                Tables\Columns\TextColumn::make('bulan')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
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
                        return $bulanMap[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('rekening.nama'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\BulkAction::make('kirimTagihan')
                    ->label('Kirim Tagihan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()

                    ->action(function ($records) {

                        foreach ($records as $tagihan) {

                            \App\Services\NotificationService::sendTagihan(
                                $tagihan->siswa,
                                $tagihan
                            );
                        }
                    }),
                Tables\Actions\DeleteBulkAction::make(),
            ])

            ->filters([
            Tables\Filters\SelectFilter::make('lembaga_id')
                ->label('Lembaga')
                ->relationship('siswa.lembaga', 'nama'),

            Tables\Filters\SelectFilter::make('kelas_id')
                ->label('Kelas')
                ->relationship('siswa.kelas', 'nama'),

            Tables\Filters\SelectFilter::make('siswa_id')
                ->label('Siswa')
                ->relationship('siswa', 'nama_lengkap')
                ->searchable(),

            Tables\Filters\SelectFilter::make('jenis_tagihan_id')
                ->label('Jenis Tagihan')
                ->relationship('jenisTagihan', 'nama'),

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'Belum' => 'Belum Lunas',
                    'sebagian' => 'Sebagian',
                    'lunas' => 'Lunas',
                ]),
        ])

            ->headerActions([
            // Tables\Actions\Action::make('createManual')
            //     ->label('Buat Input Tagihan')
            //     ->icon('heroicon-o-plus')
            //     ->url(fn () => static::getUrl('create'))
            //     ->color('primary'),

            Action::make('generateTagihan')
                ->label('Generate Tagihan')
                ->icon('heroicon-o-bolt')
                ->form([

                    Select::make('mode')
                        ->label('Generate Untuk')
                        ->options([
                            'kelas' => 'Per Kelas',
                            'siswa' => 'Per Siswa (pilih manual)',
                        ])
                        ->default('kelas')
                        ->required()
                        ->reactive(),

                    Select::make('kelas_id')
                        ->options(\App\Models\Kelas::pluck('nama', 'id'))
                        ->label('Kelas')
                        ->visible(fn ($get) => $get('mode') !== 'siswa')
                        ->required(fn ($get) => $get('mode') !== 'siswa'),

                    Select::make('siswa_ids')
                        ->label('Siswa')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(\App\Models\Siswa::pluck('nama_lengkap', 'id'))
                        ->visible(fn ($get) => $get('mode') === 'siswa')
                        ->required(fn ($get) => $get('mode') === 'siswa'),

                    Select::make('jenis_tagihan_id')
                        ->options(\App\Models\JenisTagihan::pluck('nama', 'id'))
                        ->label('Jenis Tagihan')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $jenis = \App\Models\JenisTagihan::find($state);
                            $set('is_bulanan', $jenis?->is_bulanan);
                        }),

                    Hidden::make('is_bulanan'),

                    Toggle::make('is_tunggakan')
                        ->label('Input Tunggakan?')
                        ->reactive(),

                    Toggle::make('regenerate')
                        ->label('Update nominal tagihan lama yang belum dibayar?')
                        ->helperText('Kalau aktif: tagihan yang sudah ada dan BELUM ADA PEMBAYARAN SAMA SEKALI akan di-update nominalnya sesuai setting terbaru. Tagihan yang sudah dicicil/lunas TIDAK akan diubah. Kalau nonaktif: hanya membuat tagihan baru untuk yang belum pernah di-generate (perilaku lama).')
                        ->default(false),

                    CheckboxList::make('bulan')
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
                        ->columns(4)
                        ->bulkToggleable()
                        ->visible(fn ($get) => $get('is_bulanan')),

                    Select::make('tahun_ajaran_id')
                        ->default(fn () => \App\Models\TahunAjaran::aktif()?->id)
                        ->options(\App\Models\TahunAjaran::pluck('nama', 'id'))
                        ->label('Tahun Ajaran')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('periode_tahun_ajaran_id')
                        ->label('Periode Tagihan')
                        ->options(function () {

                            $aktif = \App\Models\TahunAjaran::where('aktif', true)->first();
                            if (!$aktif) return [];

                            $tahunAktif = (int) substr($aktif->nama, 0, 4);

                            return \App\Models\TahunAjaran::query()
                                ->select('nama', \DB::raw('MIN(id) as id'))
                                ->groupBy('nama')
                                ->get()
                                ->filter(function ($item) use ($tahunAktif) {
                                    $tahun = (int) substr($item->nama, 0, 4);
                                    return $tahun < $tahunAktif && $tahun >= ($tahunAktif - 6);
                                })
                                ->sortByDesc('nama')
                                ->pluck('nama', 'id');
                        })
                        ->visible(fn ($get) => $get('is_tunggakan'))
                        ->required(fn ($get) => $get('is_tunggakan')),

                    Forms\Components\DatePicker::make('jatuh_tempo')
                        ->visible(fn ($get) => !$get('is_bulanan'))
                        ->required(fn ($get) => !$get('is_bulanan')),

                    Select::make('rekening_id')
                        ->label('Rekening')
                        ->options(
                            \App\Models\Rekening::get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->nama . ' - ' . $r->bank . ' (' . $r->no_rekening . ')'
                                ])
                        )
                        ->searchable()
                        ->required(),

                ])

                ->action(function (array $data) {

                    $bulanNama = [
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

                    $siswaList = $data['mode'] === 'siswa'
                        ? \App\Models\Siswa::whereIn('id', $data['siswa_ids'])->get()
                        : \App\Models\Siswa::where('kelas_id', $data['kelas_id'])->get();

                    $jenis = \App\Models\JenisTagihan::findOrFail($data['jenis_tagihan_id']);
                    $tahunAjaran = \App\Models\TahunAjaran::find($data['tahun_ajaran_id']);
                    $tahun = (int) substr($tahunAjaran->nama, 0, 4);
                    // 🔥 VALIDASI BULAN
                    if ($jenis->is_bulanan && empty($data['bulan'])) {
                        throw new \Exception('Bulan wajib dipilih untuk tagihan bulanan');
                    }

                    $dibuat = 0;
                    $diupdate = 0;
                    $dilewati = 0;

                    foreach ($siswaList as $siswa) {

                        $bulans = $jenis->is_bulanan ? $data['bulan'] : [null];

                        foreach ($bulans as $bulan) {

                            // 🔥 AMBIL SETTING PALING SPESIFIK (FINAL FIX)
                            $setting = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                                ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])

                                // 🔥 PRIORITAS SISWA
                                ->where(function ($q) use ($siswa) {
                                    $q->where('siswa_id', $siswa->id)
                                    ->orWhereNull('siswa_id');
                                })

                                // 🔥 PRIORITAS KELAS
                                ->where(function ($q) use ($siswa) {
                                    $q->where('kelas_id', $siswa->kelas_id)
                                    ->orWhereNull('kelas_id');
                                })

                                // 🔥 BULAN
                                ->where(function ($q) use ($bulan) {
                                    if ($bulan) {
                                        $q->whereJsonContains('bulan', $bulan)
                                        ->orWhereNull('bulan');
                                    } else {
                                        $q->whereNull('bulan');
                                    }
                                })

                                // 🔥 URUTAN PRIORITAS (PALING PENTING)
                                ->orderByRaw('siswa_id IS NULL') // siswa dulu
                                ->orderByRaw('kelas_id IS NULL') // lalu kelas
                                ->orderByRaw('bulan IS NULL')    // lalu bulan umum

                                ->first();

                            $nominal = $setting?->nominal ?? $jenis->default_nominal;

                            // 🔥 CEK APAKAH TAGIHAN INI SUDAH PERNAH DIBUAT
                            $existing = Tagihan::where([
                                    'siswa_id' => $siswa->id,
                                    'jenis_tagihan_id' => $jenis->id,
                                    'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                                ])
                                ->when($bulan, fn ($q) => $q->where('bulan', $bulan))
                                ->first();

                            if ($existing) {

                                // REGENERATE: hanya update nominal kalau tagihan
                                // BELUM ADA PEMBAYARAN SAMA SEKALI. Tagihan yang
                                // sudah dicicil/lunas tidak pernah disentuh --
                                // supaya riwayat pembayaran tetap konsisten.
                                if (
                                    !empty($data['regenerate'])
                                    && $existing->status === 'belum'
                                    && $existing->nominal_terbayar == 0
                                ) {
                                    $existing->update([
                                        'nominal' => $nominal,
                                        'rekening_id' => $data['rekening_id'],
                                    ]);
                                    $diupdate++;
                                } else {
                                    $dilewati++;
                                }

                                continue;
                            }

                            $judul = $jenis->nama 
                                . ($bulan ? ' - ' . $bulanNama[$bulan] : '');

                            $jatuhTempo = $jenis->is_bulanan
                            ? \Carbon\Carbon::createFromDate($tahun, (int) $bulan, 10)
                            : $data['jatuh_tempo'];

                            Tagihan::create([
                                'siswa_id' => $siswa->id,
                                'jenis_tagihan_id' => $jenis->id, // 🔥 WAJIB
                                'judul' => $judul,
                                'nominal' => $nominal,
                                'nominal_terbayar' => 0,
                                'status' => 'Belum',
                                'rekening_id' => $data['rekening_id'], // 🔥 WAJIB
                                'tahun_ajaran_id' => $data['tahun_ajaran_id'], // 🔥 WAJIB
                                'periode_tahun_ajaran_id' => $data['is_tunggakan']
                                    ? $data['periode_tahun_ajaran_id']
                                    : null,
                                'bulan' => $bulan,
                                'jatuh_tempo' => $jatuhTempo,
                            ]);
                            $dibuat++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Generate Tagihan Selesai')
                        ->body("Dibuat baru: {$dibuat}. Diupdate: {$diupdate}. Dilewati (sudah ada pembayaran): {$dilewati}.")
                        ->success()
                        ->send();
                }),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTagihans::route('/'),
            'edit' => Pages\EditTagihan::route('/{record}/edit'),
        ];
    }
}