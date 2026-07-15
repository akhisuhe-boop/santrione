<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpdbResource\Pages;
use App\Models\Ppdb;
use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\NisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;

class PpdbResource extends BaseResource
{
    protected static ?string $model = Ppdb::class;

    protected static ?string $navigationGroup = 'PSB';
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Pendaftaran Siswa Baru';
    protected static ?string $modelLabel = 'Pendaftaran Siswa Baru';
    protected static ?string $pluralModelLabel = 'Pendaftaran Siswa Baru';

    public static function getEloquentQuery(): Builder
    {
        $tahunId = \App\Models\TahunAjaran::where('aktif', true)->value('id');

        return parent::getEloquentQuery()
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->where('status', '!=', 'aktif');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ======================
            // IDENTITAS
            // ======================
            Section::make('Identitas Siswa')->schema([
                Select::make('lembaga_id')
                    ->label('Lembaga')
                    ->options(Lembaga::pluck('nama', 'id'))
                    ->reactive(),

                Select::make('kelas_id')
                ->relationship('kelas', 'nama')
                ->hidden(),

                TextInput::make('rfid')
                ->hidden(),

                TextInput::make('nis')
                ->label('NIS')
                ->disabled()
                ->placeholder('Otomatis saat lulus'),

                TextInput::make('nisn')
                ->label('NISN')
                ->required(),

                TextInput::make('nik')
                ->label('NIK')
                ->required(),

                TextInput::make('nama_lengkap')->required(),

                Select::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),

                TextInput::make('asal_sekolah')
                    ->label('Asal Sekolah')
                    ->placeholder('Contoh: SDIT Al Hikmah'),

                TextInput::make('tempat_lahir')
                ->label('Tempat Lahir')
                ->required(),

                DatePicker::make('tanggal_lahir')
                ->label('Tanggal Lahir')
                ->required(),
          
                TextInput::make('tinggi_badan')
                ->label('Tinggi Badan')
                ->required(),

                TextInput::make('berat_badan')
                ->label('Berat Badan')
                ->required(),

                TextInput::make('golongan_darah')
                ->label('Golongan Darah'),

                TextInput::make('alamat_jalan')->label('Alamat')->required(),
                TextInput::make('provinsi')->label('Provinsi')->required(),
                TextInput::make('kabupaten')->label('Kabupaten')->required(),
                TextInput::make('kecamatan')->label('Kecamatan')->required(),
                TextInput::make('desa')->label('Desa/Kelurahan')->required(),
                TextInput::make('rt')->label('RT')->required(),
                TextInput::make('rw')->label('RW')->required(),
                TextInput::make('kode_pos')->label('Kode Pos')->required(),
            ])->columns(4),

            // ======================
            // AYAH
            // ======================
            Section::make('Data Ayah')->schema([
                TextInput::make('no_kartu_keluarga')->label('No. Kartu Keluarga')->required(),
                TextInput::make('nik_ayah')->label('NIK Ayah')->required(),
                TextInput::make('nama_ayah')->label('Nama Ayah')->required(),

                Select::make('status_ayah')->options([
                    'Hidup' => 'Hidup',
                    'Meninggal' => 'Meninggal',
                    'Cerai' => 'Cerai',
                ])->required(),

                TextInput::make('pekerjaan_ayah')->label('Pekerjaan Ayah')->required(),

                Select::make('pendidikan_ayah')->options([
                    'SD','SMP','SMA','D3','S1','S2','S3'
                ])->required(),

                TextInput::make('penghasilan_ayah')->label('Penghasilan Ayah')->required(),
                TextInput::make('wa_ayah')->label('WhatsApp Ayah')->required(),
            ])->columns(4),

            // ======================
            // IBU
            // ======================
            Section::make('Data Ibu')->schema([
                TextInput::make('nik_ibu')->label('NIK Ibu')->required(),
                TextInput::make('nama_ibu')->label('Nama Ibu')->required(),

                Select::make('status_ibu')->options([
                    'Hidup' => 'Hidup',
                    'Meninggal' => 'Meninggal',
                    'Cerai' => 'Cerai',
                ])->required(),

                TextInput::make('pekerjaan_ibu')->label('Pekerjaan Ibu')->required(),

                Select::make('pendidikan_ibu')->options([
                    'SD','SMP','SMA','D3','S1','S2','S3'
                ])->required(),

                TextInput::make('penghasilan_ibu')->label('Penghasilan Ibu')->required(),
                TextInput::make('wa_ibu')->label('WhatsApp Ibu')->required(),
            ])->columns(4),

            // ======================
            // WALI
            // ======================
            Section::make('Data Wali')->schema([
                TextInput::make('nik_wali')->label('NIK Wali'),
                TextInput::make('nama_wali')->label('Nama Wali'),

                Select::make('status_wali')->options([
                    'Hidup' => 'Hidup',
                    'Meninggal' => 'Meninggal',
                ]),

                TextInput::make('pekerjaan_wali')->label('Pekerjaan Wali'),

                Select::make('pendidikan_wali')->options([
                    'SD','SMP','SMA','D3','S1','S2','S3'
                ]),

                TextInput::make('penghasilan_wali')->label('Penghasilan Wali'),
                TextInput::make('hubungan_wali')->label('Hubungan Wali'),
                TextInput::make('wa_wali')->label('WhatsApp Wali'),
            ])->columns(4),

            // ======================
            // DOKUMEN
            // ======================
            Section::make('Dokumen')->schema([
                FileUpload::make('foto')->image()->required(),
                FileUpload::make('scan_kk')
                ->label('Scan Kartu Keluarga')->required(),
                FileUpload::make('scan_akta')->label('Scan Akta Kelahiran')->required(),
                FileUpload::make('scan_ijazah')->label('Scan Ijazah'),
            ])->columns(2),

            // ======================
            // STATUS
            // ======================
            Section::make('Status')->schema([
                Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'upload_berkas' => 'Upload Berkas',
                    'verifikasi_berkas' => 'Verifikasi Berkas',
                    'tes' => 'Tes Seleksi',
                    'lulus' => 'Lulus',
                    'tidak_lulus' => 'Tidak Lulus',
                    'daftar_ulang' => 'Daftar Ulang',
                    'aktif' => 'Aktif',
                ])
                ->default('draft'),
            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                ->getStateUsing(fn ($record) => $record->foto ? asset('storage/'.$record->foto) : null)
                ->label('Foto')
                ->circular()
                ->size(40),
                Tables\Columns\TextColumn::make('nama_lengkap')
                ->label('Nama')
                ->searchable(),
                Tables\Columns\TextColumn::make('nisn')
                ->label('NISN'),
                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->badge()
                    ->color(fn ($record) => match ($record->lembaga_id) {
                     1 => 'success',   // SDIT
                     2 => 'warning',   // SMPIT
                     3 => 'primary',    // SMAIT
                     4 => 'info',       // SMK
                    default => 'gray',
                })
                ->searchable(),
                Tables\Columns\TextColumn::make('nama_ayah')
                ->label('Orang Tua'),
                Tables\Columns\TextColumn::make('wa_ayah')
                    ->label('WA Ayah')
                    ->url(fn ($record) => $record->wa_ayah
                        ? "https://wa.me/62" . ltrim($record->wa_ayah, '0')
                        : null)
                    ->openUrlInNewTab(),
                
                Tables\Columns\TextColumn::make('status_pembayaran')
                ->label('Pembayaran')
                ->badge()
                ->getStateUsing(function ($record) {
                    $tagihan = $record->tagihans()->latest()->first();
                    if (!$tagihan) {
                        return '-';
                    }
                    return match ($tagihan->status) {
                        'belum' => 'Belum Lunas',
                        'sebagian' => 'Sebagian',
                        'lunas' => 'Lunas',
                        default => '-',
                    };
                })
                ->color(function ($record) {
                    $tagihan = $record->tagihans()->latest()->first();
                    return match ($tagihan?->status) {
                        'belum' => 'danger',
                        'sebagian' => 'warning',
                        'lunas' => 'success',
                        default => 'gray',
                    };
                }),

                Tables\Columns\BadgeColumn::make('status')
                ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    
                        'upload_berkas' => 'Upload Berkas',
                        'verifikasi_berkas' => 'Verifikasi Berkas',
                    
                        'tes' => 'Tes',
                        'lulus' => 'Lulus',
                        'tidak_lulus' => 'Tidak Lulus',
                        'daftar_ulang' => 'Daftar Ulang',
                        'aktif' => 'Aktif',
                    
                        default => ucfirst($state),
                    })

                ->color(fn ($state) => match ($state) {
                    'draft' => 'secondary',
                    'menunggu_pembayaran' => 'warning',
                    'upload_berkas' => 'warning',
                    'verifikasi_berkas' => 'info',
                    'tes' => 'primary',
                    'lulus' => 'success',
                    'tidak_lulus' => 'danger',
                    'daftar_ulang' => 'primary',
                    'aktif' => 'success',
                    default => 'secondary',
                })
            ])

            ->actions([
            // ======================
            // 🟡 DRAFT → MENUNGGU PEMBAYARAN
            // ======================
            Tables\Actions\Action::make('setPembayaran')
            ->label('Setting Pembayaran')
            ->icon('heroicon-o-banknotes')
            ->color('warning')
            ->visible(fn ($record) => $record->status === 'draft')
            ->requiresConfirmation()

            ->modalHeading('Set Tagihan Pendaftaran')
            ->modalDescription('Silakan pilih jenis tagihan dan nominal.')

            ->form([

            // ✅ JENIS TAGIHAN AUTO (LOCKED)
            Forms\Components\Select::make('jenis_tagihan_id')
                ->label('Jenis Tagihan')
                ->options(
                    \App\Models\JenisTagihan::where('kode', 'pendaftaran_ppdb')
                        ->pluck('nama', 'id')
                )
                ->default(fn () =>
                    \App\Models\JenisTagihan::where('kode', 'pendaftaran_ppdb')->value('id')
                )
                ->disabled()
                ->dehydrated(),

            // ✅ NOMINAL AUTO
            Forms\Components\TextInput::make('nominal')
                ->label('Nominal')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->readonly()
                ->afterStateHydrated(function ($set, $livewire) {

            $record = $livewire->getMountedTableActionRecord();

            $jenis = \App\Models\JenisTagihan::where('kode', 'pendaftaran_ppdb')->first();
            if (!$jenis) return;

            $nominal = $jenis->default_nominal;

            if ($record) {

                // 🔴 PRIORITAS 1: SISWA
                if ($record->siswa_id) {
                    $siswa = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                        ->where('siswa_id', $record->siswa_id)
                        ->first();

                    if ($siswa) {
                        $set('nominal', $siswa->nominal);
                        return;
                    }
                }

                // 🟠 PRIORITAS 2: KELAS
                if ($record->kelas_id) {
                    $kelas = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                        ->where('kelas_id', $record->kelas_id)
                        ->first();

                    if ($kelas) {
                        $set('nominal', $kelas->nominal);
                        return;
                    }
                }

                // 🟡 PRIORITAS 3: LEMBAGA
                if ($record->lembaga_id) {
                    $lembaga = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                        ->where('lembaga_id', $record->lembaga_id)
                        ->first();

                    if ($lembaga) {
                        $set('nominal', $lembaga->nominal);
                        return;
                    }
                }
            }

            // ⚪ DEFAULT
            $set('nominal', $nominal);
        }),

            // ✅ REKENING
            Forms\Components\Select::make('rekening_id')
                ->label('Rekening')
                ->options(\App\Models\Rekening::pluck('nama', 'id'))
                ->required(),

        ])

            ->action(function ($record, $data) {

                DB::transaction(function () use ($record, $data) {

                    // 🔥 update status
                    $record->update([
                        'status' => 'menunggu_pembayaran'
                    ]);

                    // 🔥 ambil tahun ajaran
                    $tahunAjaran = \App\Models\TahunAjaran::aktif();

                    if (!$tahunAjaran) {
                        throw new \Exception('Tahun ajaran aktif belum ada');
                    }

                    // 🔥 anti duplikat
                    $sudahAda = \App\Models\Tagihan::where('ppdb_id', $record->id)
                        ->where('jenis_tagihan_id', $data['jenis_tagihan_id'])
                        ->exists();

                    if ($sudahAda) {
                        throw new \Exception('Tagihan sudah dibuat!');
                    }

                    // 🔥 insert tagihan
                    $tagihan = \App\Models\Tagihan::create([
                    'ppdb_id' => $record->id,
                    'siswa_id' => null,
                    'jenis_tagihan_id' => $data['jenis_tagihan_id'],
                    'judul' => \App\Models\JenisTagihan::find($data['jenis_tagihan_id'])->nama,
                    'nominal' => $data['nominal'],
                    'nominal_terbayar' => 0,
                    'status' => 'belum',
                    'rekening_id' => $data['rekening_id'],
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'is_cicilan' => false,
                    'jatuh_tempo' => now()->addDays(3),
                ]);

                NotificationService::sendPpdbPembayaran(
                    $record,
                    $tagihan
                );
                });

            }),
            
            // ======================
            // 🔵 VERIFIKASI BERKAS
            // ======================
            
            Tables\Actions\Action::make('verifikasiBerkas')
                ->label('Verifikasi Berkas')
                ->icon('heroicon-o-document-check')
                ->color('primary')
            
                ->visible(fn ($record) => $record->status === 'verifikasi_berkas')
                ->modalHeading('Verifikasi Berkas')
                ->modalSubmitActionLabel('Approve')
            
                ->form([
                    Forms\Components\Placeholder::make('kk')
                        ->label('Kartu Keluarga')
                        ->content(fn ($record) =>
                            new \Illuminate\Support\HtmlString(
                                $record->scan_kk
                                    ? '<a href="'.asset('storage/'.$record->scan_kk).'" target="_blank" style="color:#0ea5e9">Lihat Kartu Keluarga</a>'
                                    : '-'
                            )
                        ),
            
                    Forms\Components\Placeholder::make('akta')
                        ->label('Akta Kelahiran')
                        ->content(fn ($record) =>
                            new \Illuminate\Support\HtmlString(
                                $record->scan_akta
                                    ? '<a href="'.asset('storage/'.$record->scan_akta).'" target="_blank" style="color:#0ea5e9">Lihat Akta</a>'
                                    : '-'
                            )
                        ),
            
                    Forms\Components\Placeholder::make('ijazah')
                        ->label('Ijazah / SKL')
                        ->content(fn ($record) =>
                            new \Illuminate\Support\HtmlString(
                                $record->scan_ijazah
                                    ? '<a href="'.asset('storage/'.$record->scan_ijazah).'" target="_blank" style="color:#0ea5e9">Lihat Ijazah</a>'
                                    : 'Tidak ada'
                            )
                        ),
            
                ])
            
                ->requiresConfirmation()
                ->action(function ($record) {
                    if ($record->lembaga?->tes_masuk) {
                    
                        // Jalur Tes
                        $record->update([
                            'status' => 'tes',
                        ]);
                    
                        NotificationService::sendPpdbTes($record);
                    
                    } else {
                    
                        // Jalur Non Tes
                        $record->update([
                            'status' => 'lulus',
                        ]);
                    
                        NotificationService::sendPpdbLulus($record);
                    
                    }
                }),


            // ======================
            // 🔵 MENUNGGU → TES
            // ======================
            Tables\Actions\Action::make('setTes')
                ->label('Masuk Tes')
                ->icon('heroicon-o-academic-cap')
                ->color('info')
                ->visible(fn ($record) => $record->status === 'menunggu_pembayaran')
                ->requiresConfirmation()
                ->modalHeading('Masuk Tahap Tes')
                ->modalDescription('Siswa akan mengikuti proses seleksi tes masuk.')
                ->modalSubmitActionLabel('Ya, Lanjutkan')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'tes'
                    ]);
                    NotificationService::sendPpdbTes($record);
                }),

            // ======================
            // 🟢 LULUS TANPA TES
            // ======================
            Tables\Actions\Action::make('lulusTanpaTes')
                ->label('Lulus Tanpa Tes')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'menunggu_pembayaran')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Kelulusan')
                ->modalDescription('Siswa dinyatakan lulus tanpa tes dan dapat melanjutkan ke tahap daftar ulang.')
                ->modalSubmitActionLabel('Ya, Luluskan')
                ->action(function ($record) {
                $record->update([
                        'status' => 'lulus'
                    ]);
                    NotificationService::sendPpdbLulus($record);
                }),

            // ======================
            // 🟢 LULUS (SETELAH TES)
            // ======================
                Tables\Actions\Action::make('lulus')
                ->label('Lulus')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'tes')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Kelulusan')
                ->modalDescription('Siswa dinyatakan lulus dan lanjut ke tahap daftar ulang.')
                ->modalSubmitActionLabel('Ya, Luluskan')

                ->action(function ($record) {

                    DB::transaction(function () use ($record) {

                        $record->update([
                            'status' => 'lulus'
                        ]);

                        NotificationService::sendPpdbLulus($record);

                    });

                }),

            // ======================
            // 🔴 TIDAK LULUS
            // ======================
            Tables\Actions\Action::make('tidakLulus')
                ->label('Tidak Lulus')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status === 'tes')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Tidak Lulus')
                ->modalDescription('Siswa dinyatakan tidak lulus seleksi.')
                ->modalSubmitActionLabel('Ya, Tetapkan')
                ->action(function ($record) {
                    $record->update([
                    'status' => 'tidak_lulus'
                    ]);
                    NotificationService::sendPpdbTidakLulus($record);
                }),

            // ======================
            // 🟣 DAFTAR ULANG
            // ======================
            Tables\Actions\Action::make('daftarUlang')
            ->label('Setting Daftar Ulang')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->visible(fn ($record) => $record->status === 'lulus')
            ->requiresConfirmation()

            ->modalHeading('Set Tagihan Daftar Ulang')
            ->modalDescription('Silakan tentukan tagihan daftar ulang.')

            ->form([

                Forms\Components\Select::make('jenis_tagihan_id')
    ->label('Jenis Tagihan')
    ->options(
        \App\Models\JenisTagihan::where('kode', 'daftar_ulang_ppdb')
            ->pluck('nama', 'id')
    )
    ->default(fn () =>
        \App\Models\JenisTagihan::where('kode', 'daftar_ulang_ppdb')->value('id')
    )
    ->required()
    ->native(false)
    ->selectablePlaceholder(false)
    ->afterStateHydrated(function ($state, $set, $livewire) {

        $jenis = \App\Models\JenisTagihan::find($state);

        if (!$jenis) return;

        $record = $livewire->getMountedTableActionRecord();

        $nominal = $jenis->default_nominal;

        if ($record) {

            // PRIORITAS SISWA
            if ($record->siswa_id) {

                $siswa = \App\Models\SettingNominalTagihan::where(
                    'jenis_tagihan_id',
                    $jenis->id
                )
                ->where('siswa_id', $record->siswa_id)
                ->first();

                if ($siswa) {
                    $set('nominal', $siswa->nominal);
                    return;
                }
            }

            // PRIORITAS KELAS
            if ($record->kelas_id) {

                $kelas = \App\Models\SettingNominalTagihan::where(
                    'jenis_tagihan_id',
                    $jenis->id
                )
                ->where('kelas_id', $record->kelas_id)
                ->first();

                if ($kelas) {
                    $set('nominal', $kelas->nominal);
                    return;
                }
            }

            // PRIORITAS LEMBAGA
            $lembaga = \App\Models\SettingNominalTagihan::where(
                'jenis_tagihan_id',
                $jenis->id
            )
            ->where('lembaga_id', $record->lembaga_id)
            ->first();

            if ($lembaga) {
                $set('nominal', $lembaga->nominal);
                return;
            }
        }

        $set('nominal', $nominal);
    }),

                Forms\Components\TextInput::make('nominal')
                ->label('Nominal')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->readonly()
                ->afterStateHydrated(function ($set, $livewire) {

                    $record = $livewire->getMountedTableActionRecord();

                    $jenis = \App\Models\JenisTagihan::where('kode', 'daftar_ulang_ppdb')->first();
                    if (!$jenis) return;

                    $nominal = $jenis->default_nominal;

                    if ($record) {

                        // 🔴 PRIORITAS 1: SISWA
                        if ($record->siswa_id) {
                            $siswa = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                                ->where('siswa_id', $record->siswa_id)
                                ->first();

                            if ($siswa) {
                                $set('nominal', $siswa->nominal);
                                return;
                            }
                        }

                        // 🟠 PRIORITAS 2: KELAS
                        if ($record->kelas_id) {
                            $kelas = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                                ->where('kelas_id', $record->kelas_id)
                                ->first();

                            if ($kelas) {
                                $set('nominal', $kelas->nominal);
                                return;
                            }
                        }

                        // 🟡 PRIORITAS 3: LEMBAGA
                        $lembaga = \App\Models\SettingNominalTagihan::where('jenis_tagihan_id', $jenis->id)
                            ->where('lembaga_id', $record->lembaga_id)
                            ->first();

                        if ($lembaga) {
                            $set('nominal', $lembaga->nominal);
                            return;
                        }
                    }

                    // ⚪ DEFAULT
                    $set('nominal', $nominal);
                }),

                Forms\Components\Select::make('rekening_id')
                    ->label('Rekening')
                    ->options(\App\Models\Rekening::pluck('nama', 'id'))
                    ->required(),

            ])

            ->action(function ($record, $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->update([
                        'status' => 'daftar_ulang'
                    ]);
                    $tahunAjaran = \App\Models\TahunAjaran::aktif();
                    $tagihan = \App\Models\Tagihan::create([
                        'ppdb_id' => $record->id,
                        'jenis_tagihan_id' => $data['jenis_tagihan_id'],
                        'judul' => \App\Models\JenisTagihan::find($data['jenis_tagihan_id'])->nama,
                        'nominal' => $data['nominal'],
                        'nominal_terbayar' => 0,
                        'status' => 'belum',
                        'rekening_id' => $data['rekening_id'],
                        'tahun_ajaran_id' => $tahunAjaran->id,
                        'jatuh_tempo' => now()->addDays(3),
                    ]);
                    NotificationService::sendPpdbDaftarUlang(
                        $record,
                        $tagihan
                    );});

            }),

            // ======================
            // 💥 FINAL: AKTIFKAN SISWA
            // ======================
            Tables\Actions\Action::make('aktifkan')
                ->label('Aktifkan Siswa')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'daftar_ulang')
                ->requiresConfirmation()
                ->modalHeading('Aktifkan Siswa')
                ->modalDescription('Siswa akan diaktifkan, dibuatkan NIS, dan masuk ke data siswa aktif.')
                ->modalSubmitActionLabel('Aktifkan')
                ->form([
                    Forms\Components\Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(function ($record) {
                            return \App\Models\Kelas::where(
                                'lembaga_id',
                                $record->lembaga_id
                            )->pluck('nama', 'id');
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('asrama_id')
                        ->label('Asrama')
                        ->options(
                            \App\Models\Asrama::pluck('nama', 'id')
                        )
                        ->searchable()
                        ->placeholder('Optional')
                        ->nullable(),

                ])

                ->action(function ($record, $data) {
                DB::transaction(function () use ($record, $data) {

                    // 🔥 Generate NIS
                    $nis = NisService::generate($record->lembaga_id);

                    // 🔥 buat siswa
                    $siswa = \App\Models\Siswa::create(
                        collect($record->toArray())
                            ->except(['id','status','created_at','updated_at'])
                            ->merge([
                                'nis' => $nis,
                                'kelas_id' => $data['kelas_id'],
                                'asrama_id' => $data['asrama_id'] ?? null,
                                'status_siswa' => 'aktif',
                            ])
                            ->toArray()
                    );
                    NotificationService::sendPpdbAktif($siswa);

                    // 🔥 PINDAHKAN TAGIHAN
                    \App\Models\Tagihan::where('ppdb_id', $record->id)
                        ->update([
                            'siswa_id' => $siswa->id,
                            'ppdb_id' => null,
                        ]);

                    // 🔥 UPDATE (BUKAN DELETE)
                    $record->update([
                        'siswa_id' => $siswa->id,
                        'status' => 'aktif',
                    ]);

                });

            }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password')
                    ->modalDescription('Password akan direset menjadi NISN.')
                    ->modalSubmitActionLabel('Reset')
                    ->action(function ($record) {
                
                        $record->update([
                            'password' => Hash::make($record->nisn),
                        ]);
                        
                        NotificationService::sendPpdbResetPassword($record);
                
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil')
                            ->body('Password berhasil direset menjadi NISN.')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn ($record) =>
                        in_array($record->status, [
                            'menunggu_pembayaran',
                            'daftar_ulang',
                        ])
                    )

                ->url(function ($record) {

                    $tenantSlug = \Filament\Facades\Filament::getTenant()?->slug;

                    // 🔥 masih PPDB
                    if ($record->status !== 'aktif') {
                        return route('filament.admin.resources.pembayarans.create', [
                            'tenant' => $tenantSlug,
                            'ppdb_id' => $record->id
                        ]);
                    }

                    // 🔥 sudah siswa
                    if ($record->siswa_id) {
                        return route('filament.admin.resources.pembayarans.create', [
                            'tenant' => $tenantSlug,
                            'siswa_id' => $record->siswa_id
                        ]);
                    }

                    return route('filament.admin.resources.pembayarans.create', [
                        'tenant' => $tenantSlug,
                    ]);
                })
        ])

        ->bulkActions([

        Tables\Actions\BulkAction::make('aktifkanMassal')
            ->label('Aktifkan Massal')
            ->icon('heroicon-o-user-plus')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aktifkan Siswa Massal')
            ->modalDescription('Pastikan siswa yang dipilih berasal dari lembaga yang sama.')

            ->form([
                Forms\Components\Select::make('kelas_id')
                    ->label('Pilih Kelas')
                    ->options(function ($livewire) {

                        $records = $livewire->getSelectedTableRecords();

                        if (!$records->count()) return [];

                        $lembagaId = $records->first()->lembaga_id;

                        if ($records->pluck('lembaga_id')->unique()->count() > 1) {
                            return [];
                        }

                        return \App\Models\Kelas::where('lembaga_id', $lembagaId)
                            ->pluck('nama', 'id');
                    })
                    ->required()
                    ->helperText('Kelas hanya tampil sesuai lembaga siswa'),
            ])

            ->action(function ($records, $data) {

                // 🔴 VALIDASI
                if ($records->pluck('lembaga_id')->unique()->count() > 1) {
                    \Filament\Notifications\Notification::make()
                        ->title('Gagal')
                        ->body('Pilih siswa dari lembaga yang sama!')
                        ->danger()
                        ->send();
                    return;
                }

                $berhasil = 0;

                foreach ($records as $record) {

                    if ($record->status !== 'daftar_ulang') continue;

                    // 🔥 Generate NIS
                    $nis = \App\Services\NisService::generate($record->lembaga_id);

                    // 💥 Insert ke siswa
                    $siswa = \App\Models\Siswa::create(
                        collect($record->toArray())
                            ->except(['id','status','created_at','updated_at','deleted_at'])
                            ->merge([
                                'nis' => $nis,
                                'kelas_id' => $data['kelas_id'],
                                'status_siswa' => 'aktif',
                            ])
                            ->toArray()
                    );

                    // ======================
                    // 🔥 PINDAHKAN TAGIHAN
                    // ======================
                    \App\Models\Tagihan::where('ppdb_id', $record->id)
                        ->update([
                            'siswa_id' => $siswa->id,
                            'ppdb_id' => null,
                        ]);

                    // 🧹 hapus ppdb
                    $record->update([
                        'siswa_id' => $siswa->id,
                        'status' => 'aktif',
                    ]);

                    $berhasil++;
                }

                \Filament\Notifications\Notification::make()
                    ->title('Berhasil')
                    ->body("{$berhasil} siswa berhasil diaktifkan")
                    ->success()
                    ->send();
            }),

            Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Massal')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
    ]);
            }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPpdbs::route('/'),
            'create' => Pages\CreatePpdb::route('/create'),
            'edit' => Pages\EditPpdb::route('/{record}/edit'),
        ];
    }
}