<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranResource\Pages;
use App\Models\Pembayaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Input Pembayaran';
    protected static ?string $label = 'Input Pembayaran';
    protected static ?string $pluralLabel = 'Input Pembayaran';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
{
    return $form->schema([

        Section::make('Pembayaran')
            ->schema([

                // ======================
                // 🔥 HIDDEN MODE (SMART SWITCH)
                // ======================
                Forms\Components\Hidden::make('ppdb_id')
                    ->default(fn () => request()->get('ppdb_id')),

                // ======================
                // 👤 SISWA (SMART MODE)
                // ======================
                Forms\Components\Select::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'nama_lengkap')
                    ->searchable()
                    ->reactive()

                    // 🔥 AUTO ISI kalau dari siswa
                    ->default(fn () => request()->get('siswa_id'))
                    ->disabled(fn () => request()->has('siswa_id'))

                    // 🔥 WAJIB kalau bukan PPDB
                    ->required(fn ($get) => !$get('ppdb_id'))

                    // 🔥 HIDE kalau PPDB
                    ->hidden(fn ($get) => $get('ppdb_id'))

                    // 🔥 RESET TAGIHAN kalau ganti siswa
                    ->afterStateUpdated(fn ($set) => $set('tagihan_id', null))

                    // 🔥 SAFETY RESET kalau mode PPDB
                    ->afterStateHydrated(function ($set, $get) {
                        if ($get('ppdb_id')) {
                            $set('siswa_id', null);
                        }
                    }),

                // ======================
                // 🟡 CALON SISWA (PPDB)
                // ======================
                Forms\Components\TextInput::make('ppdb_nama_display')
                ->label('Calon Siswa')
                ->prefix('👤')
                ->default(function ($get) {
                    if (!$get('ppdb_id')) return '-';

                    $ppdb = \App\Models\Ppdb::find($get('ppdb_id'));
                    return $ppdb?->nama_lengkap ?? '-';
                })
                ->disabled() // 🔥 readonly
                ->dehydrated(false) // 🔥 tidak ikut disimpan
                ->visible(fn ($get) => $get('ppdb_id')),

                // ======================
                // 💰 TAGIHAN (SMART FILTER)
                // ======================
                Forms\Components\Select::make('tagihan_id')
                    ->label('Tagihan')
                    ->placeholder('Pilih salah satu tagihan')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(fn ($set) => $set('tagihan_id', null))

                    ->options(function ($get) {
                    // 🔥 AMBIL LANGSUNG DARI URL (LEBIH AKURAT)
                    $ppdbId = $get('ppdb_id');
                    $siswaId = $get('siswa_id');

                    // ======================
                    // 🔥 MODE PPDB
                    // ======================
                    if ($ppdbId) {
                        return \App\Models\Tagihan::where('ppdb_id', $ppdbId)
                            ->where('status', '!=', 'lunas')
                            ->get()
                            ->mapWithKeys(function ($t) {
                                $sisa = $t->nominal - $t->nominal_terbayar;

                                return [
                                    $t->id => $t->judul . ' - Sisa: Rp ' . number_format($sisa, 0, ',', '.')
                                ];
                            });
                    }

                    // ======================
                    // 🔥 MODE SISWA
                    // ======================
                    if ($siswaId) {
                        return \App\Models\Tagihan::where('siswa_id', $siswaId)
                            ->where('status', '!=', 'lunas')
                            ->get()
                            ->mapWithKeys(function ($t) {
                                $sisa = $t->nominal - $t->nominal_terbayar;

                                return [
                                    $t->id => $t->judul . ' - Sisa: Rp ' . number_format($sisa, 0, ',', '.')
                                ];
                            });
                    }

                    return [];
                })

                    // ======================
                    // 🔥 AUTO NOMINAL + STATE
                    // ======================
                    ->afterStateUpdated(function ($state, $set) {

                        $tagihan = \App\Models\Tagihan::with('jenisTagihan')->find($state);

                        if ($tagihan) {

                            // 🔥 isi siswa kalau ada
                            if ($tagihan->siswa_id) {
                                $set('siswa_id', $tagihan->siswa_id);
                            }

                            $sisa = $tagihan->nominal - $tagihan->nominal_terbayar;

                            $set('nominal', $sisa);
                            $set('sisa_tagihan', $sisa);
                            $set('is_cicilan', !$tagihan->jenisTagihan->is_bulanan);
                        }
                    }),

                // ======================
                // 💵 NOMINAL (ANTI BUG)
                // ======================
                Forms\Components\TextInput::make('nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->helperText('Tidak boleh melebihi sisa tagihan')

                    // 🔥 kalau bukan cicilan → lock
                    ->readOnly(fn ($get) => !$get('is_cicilan'))

                    // 🔥 limit maksimal
                    ->maxValue(fn ($get) => $get('sisa_tagihan') ?? 0)

                    // 🔥 validasi keras
                    ->rule(fn ($get) => function ($attr, $val, $fail) use ($get) {
                        if ($val > ($get('sisa_tagihan') ?? 0)) {
                            $fail('Nominal melebihi sisa tagihan!');
                        }
                    }),

                // ======================
                // 💳 METODE
                // ======================
                Forms\Components\Select::make('metode')
                    ->options([
                        'admin' => 'Tunai',
                        'ewallet' => 'Saldo',
                        'transfer' => 'Transfer',
                        'gateway' => 'Gateway',
                    ])
                    ->required(),

                // ======================
                // 🔥 DEFAULT STATUS
                // ======================
                Forms\Components\Hidden::make('status')->default('sukses'),

                // ======================
                // 🔥 HELPER STATE
                // ======================
                Forms\Components\Hidden::make('sisa_tagihan'),
                Forms\Components\Hidden::make('is_cicilan'),

            ])
            ->columns(2),

            // ========================
            // PROFIL + RIWAYAT (GRID)
            // ========================
            Forms\Components\Grid::make([
                'default' => 1,
                'lg' => 2, // 🔥 FIX agar selalu kanan kiri di layar besar
            ])
            ->schema([

                // =====================
            // 👤 PROFIL (KIRI)
            // =====================
            Section::make('Profil Siswa')
                ->schema([

                    Forms\Components\Placeholder::make('profil')
                        ->content(function ($get) {

                            $siswaId = $get('siswa_id');
                            $ppdbId = $get('ppdb_id');

                            // ======================
                            // 🔵 MODE SISWA
                            // ======================
                            if ($siswaId) {

                                $siswa = \App\Models\Siswa::with('kelas', 'lembaga')->find($siswaId);
                                if (!$siswa) return '-';

                                $foto = $siswa->foto
                                    ? asset('storage/' . $siswa->foto)
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($siswa->nama_lengkap);

                                $nama = $siswa->nama_lengkap;
                                $nis = $siswa->nis ?? '-';
                                $nisn = $siswa->nisn ?? '-';
                                $kelas = optional($siswa->kelas)->nama ?? '-';
                                $lembaga = optional($siswa->lembaga)->nama ?? '-';

                                $query = \App\Models\Tagihan::where('siswa_id', $siswaId);

                            }

                            // ======================
                            // 🟡 MODE PPDB
                            // ======================
                            elseif ($ppdbId) {

                                $ppdb = \App\Models\Ppdb::with('lembaga')->find($ppdbId);
                                if (!$ppdb) return '-';

                                $foto = $ppdb->foto
                                    ? asset('storage/' . $ppdb->foto)
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($ppdb->nama_lengkap);

                                $nama = $ppdb->nama_lengkap;
                                $nis = '-';
                                $nisn = $ppdb->nisn ?? '-';
                                $kelas = '(Calon Siswa)';
                                $lembaga = optional($ppdb->lembaga)->nama ?? '-';

                                $query = \App\Models\Tagihan::where('ppdb_id', $ppdbId);

                            } else {
                                return '-';
                            }

                            // ======================
                            // 💰 HITUNGAN
                            // ======================
                            $total = (clone $query)->sum('nominal');
                            $terbayar = (clone $query)->sum('nominal_terbayar');
                            $sisa = $total - $terbayar;
                            $persen = $total > 0 ? round(($terbayar / $total) * 100) : 0;

                            return new \Illuminate\Support\HtmlString("

                                <div style='display:flex; gap:20px; align-items:center;'>

                                    <img src='{$foto}' 
                                        style='width:80px;height:80px;border-radius:10px;object-fit:cover;'>

                                    <div style='flex:1; display:flex; gap:25px; flex-wrap:wrap;'>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Nama</div>
                                            <div style='font-weight:600;'>{$nama}</div>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>NIS</div>
                                            <div style='font-weight:600;'>{$nis}</div>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>NISN</div>
                                            <div style='font-weight:600;'>{$nisn}</div>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Kelas</div>
                                            <div style='font-weight:600;'>{$kelas}</div>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Lembaga</div>
                                            <div style='font-weight:600;'>{$lembaga}</div>
                                        </div>

                                    </div>
                                </div>

                                <div style='margin-top:15px;'>

                                    <div style='display:flex; justify-content:space-between;'>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Total</div>
                                            <b>Rp " . number_format($total,0,',','.') . "</b>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Terbayar</div>
                                            <b style='color:#16a34a;'>Rp " . number_format($terbayar,0,',','.') . "</b>
                                        </div>

                                        <div>
                                            <div style='font-size:12px;color:#6b7280;'>Sisa</div>
                                            <b style='color:#dc2626;'>Rp " . number_format($sisa,0,',','.') . "</b>
                                        </div>

                                    </div>

                                    <div style='margin-top:8px;background:#e5e7eb;height:8px;border-radius:6px;'>
                                        <div style='width:{$persen}%;background:#22c55e;height:100%;'></div>
                                    </div>

                                    <small style='color:#6b7280'>{$persen}% terbayar</small>

                                </div>

                            ");
                        }),

                ])
                ->columnSpan(1),

            // =====================
            // 📜 RIWAYAT (KANAN)
            // =====================
            Forms\Components\Livewire::make(
                \App\Filament\Widgets\RiwayatPembayaranTable::class,
                fn ($get) => [
                    'siswaId' => $get('siswa_id'),
                    'ppdbId' => $get('ppdb_id'), // 🔥 TAMBAHAN
                ]
            )
            ->key(fn ($get) => 'riwayat-' . ($get('siswa_id') ?? $get('ppdb_id')))
            ->reactive()
            ->columnSpan(1),

        ]),

                ]);
            }

        // ========================
        // TABLE
        // ========================
        public static function table(Table $table): Table
        {
            return $table
                ->defaultSort('created_at', 'desc')

                ->columns([

                    Tables\Columns\TextColumn::make('kode')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('tagihan.nama')
                        ->label('Siswa')
                        ->searchable(),

                    Tables\Columns\TextColumn::make('tagihan.kelas_nama')
                        ->label('Kelas'),

                    Tables\Columns\TextColumn::make('tagihan.lembaga_nama')
                        ->label('Lembaga')
                        ->badge()
                        ->color(function ($state) {

                            $state = strtolower($state ?? '');

                            if (str_contains($state, 'sdit')) {
                                return 'primary';
                            }

                            if (str_contains($state, 'smpit')) {
                                return 'warning';
                            }

                            if (str_contains($state, 'smait')) {
                                return 'success';
                            }

                            if (str_contains($state, 'smk')) {
                                return 'info';
                            }

                            return 'gray';
                        }),

                    Tables\Columns\TextColumn::make('tagihan.judul')
                        ->label('Tagihan')
                        ->searchable()
                        ->wrap(),

                    Tables\Columns\TextColumn::make('nominal')
                        ->label('Nominal')
                        ->alignRight()
                        ->formatStateUsing(
                            fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')
                        ),

                    Tables\Columns\BadgeColumn::make('metode')
                        ->colors([
                            'success' => 'ewallet',
                            'primary' => 'transfer',
                            'warning' => 'gateway',
                            'gray'    => 'admin',
                        ]),

                    Tables\Columns\BadgeColumn::make('status')
                        ->colors([
                            'warning' => 'pending',
                            'success' => 'sukses',
                            'danger'  => 'gagal',
                        ]),

                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Tanggal Bayar')
                        ->dateTime('d M Y H:i')
                        ->sortable(),

                ])

                ->filters([

                    Tables\Filters\SelectFilter::make('lembaga')
                        ->label('Lembaga')
                        ->relationship('siswa.lembaga', 'nama'),

                    Tables\Filters\SelectFilter::make('kelas')
                        ->label('Kelas')
                        ->relationship('siswa.kelas', 'nama'),

                    Tables\Filters\SelectFilter::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'sukses' => 'Sukses',
                            'gagal' => 'Gagal',
                        ]),

                ])

                ->actions([

                    Tables\Actions\Action::make('lihat_bukti')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('info')

                    ->visible(fn ($record) =>
                        $record->metode === 'transfer'
                    )

                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')

                    ->modalContent(fn ($record) =>
                        view('filament.modals.bukti-transfer', [
                            'record' => $record
                        ])
                    ),

                    // =====================
                    // TERIMA TRANSFER
                    // =====================
                    Tables\Actions\Action::make('approve')
                        ->label('Terima')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')

                        ->visible(fn ($record) =>
                            $record->metode === 'transfer'
                            && $record->status === 'pending'
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Terima Pembayaran Transfer')
                        ->modalDescription('Pembayaran akan dianggap berhasil dan otomatis masuk ke kas.')

                        ->action(function ($record) {
                            $record->update([
                                'status' => 'sukses',
                            ]);

                        }),

                    // =====================
                    // TOLAK TRANSFER
                    // =====================
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) =>
                            $record->metode === 'transfer'
                            && $record->status === 'pending'
                        )
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('keterangan')
                                ->label('Alasan Penolakan')
                                ->rows(3)
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'gagal',
                                'keterangan' => $data['keterangan'],
                            ]);

                        }),

                ])

                ->bulkActions([

                    Tables\Actions\DeleteBulkAction::make(),

                ]);
        }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayarans::route('/'),
            'create' => Pages\CreatePembayaran::route('/create'),
            'edit' => Pages\EditPembayaran::route('/{record}/edit'),
        ];
    }
}