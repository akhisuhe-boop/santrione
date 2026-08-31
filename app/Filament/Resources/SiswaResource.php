<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Exports\SiswaPdfExport;
use Illuminate\Database\Eloquent\Builder;
use Intervention\Image\Laravel\Facades\Image;

class SiswaResource extends BaseResource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Siswa';
    protected static ?string $pluralModelLabel = 'Siswa';
    protected static ?string $navigationLabel = 'Siswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identitas Siswa')
                ->schema([
                TextInput::make('nik')
                    ->label('NIK'),
                TextInput::make('nis')
                    ->label('NIS')
                    ->required(),
                TextInput::make('nisn')
                    ->label('NISN'),
                TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->required(),
                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),
                TextInput::make('rfid')
                    ->label('RFID'),
                    ])
                    ->columns(3),

            Section::make('Data Sekolah')
            ->schema([
                Select::make('lembaga_id')
                    ->label('Lembaga')
                    ->options(Lembaga::pluck('nama', 'id'))
                    ->required()
                    ->reactive(),

                Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(fn (callable $get) =>
                        $get('lembaga_id')
                            ? Kelas::where('lembaga_id', $get('lembaga_id'))
                                ->pluck('nama', 'id')
                            : []
                    )
                    ->required(),
                Select::make('asrama_id')
                    ->label('Asrama')
                    ->relationship('asrama', 'nama')
                    ->searchable()
                    ->preload(),
                TextInput::make('asal_sekolah')
                    ->label('Asal Sekolah'),
            ])
            ->columns(4),

            Section::make('Data Kelahiran')->schema([
                TextInput::make('tempat_lahir')
                    ->label('Tempat Lahir'),
                DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir'),
            ])->columns(2),

            Section::make('Data Fisik')->schema([
                TextInput::make('tinggi_badan')
                    ->label('Tinggi Badan'),
                TextInput::make('berat_badan')
                    ->label('Berat Badan'),
                TextInput::make('golongan_darah')
                    ->label('Golongan Darah'),
            ])->columns(3),

            Section::make('Alamat')->schema([
                TextInput::make('alamat_jalan')->label('Alamat'),
                TextInput::make('provinsi')->label('Provinsi'),
                TextInput::make('kabupaten')->label('Kabupaten'),
                TextInput::make('kecamatan')->label('Kecamatan'),
                TextInput::make('desa')->label('Desa'),
                TextInput::make('rt')->label('RT'),
                TextInput::make('rw')->label('RW'),
                TextInput::make('kode_pos')->label('Kode Pos'),
            ])->columns(4),

            Section::make('Data Ayah')->schema([
                TextInput::make('no_kartu_keluarga')->label('No. Kartu Keluarga'),
                TextInput::make('nik_ayah')->label('NIK Ayah'),
                TextInput::make('nama_ayah')->label('Nama Ayah')->reactive(),

                Select::make('status_ayah')
                    ->label('Status Ayah')
                    ->options([
                        'Hidup' => 'Hidup',
                        'Meninggal' => 'Meninggal',
                        'Cerai' => 'Cerai',
                    ]),

                TextInput::make('pekerjaan_ayah')->label('Pekerjaan Ayah'),

                Select::make('pendidikan_ayah')
                    ->label('Pendidikan Ayah')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'D3' => 'D3',
                        'S1' => 'S1',
                        'S2' => 'S2',
                        'S3' => 'S3',
                    ]),

                TextInput::make('penghasilan_ayah')->label('Penghasilan Ayah'),
                TextInput::make('wa_ayah')->label('WhatsApp Ayah')->reactive(),
            ])->columns(4),

            Section::make('Data Ibu')->schema([
                TextInput::make('nik_ibu')->label('NIK Ibu'),
                TextInput::make('nama_ibu')->label('Nama Ibu')->reactive(),

                Select::make('status_ibu')
                    ->label('Status Ibu')
                    ->options([
                        'Hidup' => 'Hidup',
                        'Meninggal' => 'Meninggal',
                        'Cerai' => 'Cerai',
                    ]),

                TextInput::make('pekerjaan_ibu')->label('Pekerjaan Ibu'),

                Select::make('pendidikan_ibu')
                    ->label('Pendidikan Ibu')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'D3' => 'D3',
                        'S1' => 'S1',
                        'S2' => 'S2',
                        'S3' => 'S3',
                    ]),

                TextInput::make('penghasilan_ibu')->label('Penghasilan Ibu'),
                TextInput::make('wa_ibu')->label('WhatsApp Ibu')->reactive(),             
            ])->columns(4),

            Section::make('Data Wali')->schema([
            TextInput::make('nik_wali')->label('NIK Wali'),
            TextInput::make('nama_wali')->label('Nama Wali'),
    
            Select::make('status_wali')
            ->label('Status Wali')
            ->options([
            'Hidup' => 'Hidup',
            'Meninggal' => 'Meninggal',            
            ]),
    
            TextInput::make('pekerjaan_wali')->label('Pekerjaan Wali'),
    
            Select::make('pendidikan_wali')
            ->label('Pendidikan Wali')
            ->options([
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'D3' => 'D3',
            'S1' => 'S1',
            'S2' => 'S2',
            'S3' => 'S3',
        ]),
    
            TextInput::make('penghasilan_wali')->label('Penghasilan Wali'),
            TextInput::make('hubungan_wali')->label('Hubungan Wali')
            ->placeholder('Contoh: Kakek / Nenek / Paman / Bibi'),
            TextInput::make('wa_wali')->label('WA Wali'),
            ])->columns(4),

            Section::make('Dokumen')->schema([
		FileUpload::make('foto')
    		->label('Foto')
    		->image()
    		->disk('r2-public')
    		->maxSize(2048)
    		->saveUploadedFileUsing(function ($file) {
        	$webp = Image::decode(file_get_contents($file->getRealPath()))
    		->cover(800, 1000)
    		->encodeUsingFileExtension('webp', quality: 80);

        	$filename = 'siswa-photos/' . uniqid() . '.webp';

	        \Storage::disk('r2-public')->put($filename, (string) $webp);

        	return $filename;
    		}),

                FileUpload::make('scan_kk')
                    ->label('Scan Kartu Keluarga')
                    ->disk('r2-private')
		    ->maxSize(2048)
                    ->directory('ppdb/scan-kk'),
            
                FileUpload::make('scan_akta')
                    ->label('Scan Akta Kelahiran')
                    ->disk('r2-private')
		    ->maxSize(2048)
                    ->directory('ppdb/scan-akta'),
            
                FileUpload::make('scan_ijazah')
                    ->label('Scan Ijazah')
                    ->disk('r2-private')
                    ->maxSize(2048)
		    ->directory('ppdb/scan-ijazah'),
            ])->columns(2),

            Section::make('Status')->schema([
                Select::make('status_siswa')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Lulus' => 'Lulus',
                        'Pindah' => 'Pindah',
                    ])
                    ->default('Aktif')
                    ->required(),
                    DatePicker::make('tanggal_lulus')->label('Tanggal Lulus'),
                    DatePicker::make('tanggal_pindah')->label('Tanggal Pindah'),
                    ])->columns(3),
            
            Section::make('Akun Orang Tua')->schema([

            //TextInput::make('nama_ayah')
            //->label('Nama Orang Tua')
            //->default(fn (callable $get) => $get('nama_ayah'))
            //->disabled()
            //->required(),

            //TextInput::make('wa_ayah')
            //->label('Username (WA Ayah)')
            //->default(fn (callable $get) => $get('wa_ayah'))
            //->disabled()
            //->required(),

            Forms\Components\TextInput::make('password')
            ->password()
            ->dehydrated(fn ($state) => filled($state))
            ->default('123456')
            ->required(fn (string $operation) => $operation === 'create'),

            TextInput::make('pin')
            ->label('PIN')
            ->numeric()
            ->integer()
            ->length(6)
            ->required(false), // optional saat edit,
            ])->columns(4),               
            
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                ->label('Foto')
                ->disk('r2-public')
                ->circular()
                ->size(40),

                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
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
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->badge()
                    ->color(fn ($record) => match ($record->lembaga_id) {
                     1 => 'primary',   // SDIT
                     2 => 'success',   // SMPIT
                     3 => 'warning',    // SMAIT
                     4 => 'info',       // SMK
                    default => 'gray',
                })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('asrama.nama')
                    ->label('Asrama')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('asal_sekolah')
                    ->label('Asal Sekolah')
                    ->searchable(),

                Tables\Columns\TextColumn::make('wa_ayah')
                    ->label('WA Ayah')
                    ->url(fn ($record) => $record->wa_ayah
                        ? "https://wa.me/62" . ltrim($record->wa_ayah, '0')
                        : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status_siswa')
                    ->badge()
                    ->color(fn ($state) => match (strtolower($state)) {
                    'aktif' => 'success',
                    'lulus' => 'warning',
                    'pindah' => 'danger',
                    default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('lembaga_id')
                    ->relationship('lembaga', 'nama')
                    ->label('Lembaga'),

                SelectFilter::make('kelas_id')
                    ->relationship('kelas', 'nama')
                    ->label('Kelas'),

                SelectFilter::make('asrama_id')
                    ->relationship('asrama', 'nama')
                    ->label('Asrama'),

                SelectFilter::make('status_siswa')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Lulus' => 'Lulus',
                        'Pindah' => 'Pindah',
                    ]),
            ])
            ->headerActions([

                Action::make('create')
                    ->label('Tambah Siswa')
                    ->url(SiswaResource::getUrl('create'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning'),

                Tables\Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('primary')
                ->modalSubmitActionLabel('Upload')
                ->form([
                Placeholder::make('download_template')
                ->content(new \Illuminate\Support\HtmlString(
                '<a href="'.route('siswa.template').'" target="_blank" 
                style="
                background:#00A39D;
                color:#ffffff;
                padding:7px 10px;
                border-radius:5px;
                font-size:14px;
                text-decoration:none;
                ">Download Template Excel
                </a>'
            )),

                FileUpload::make('file')
                ->label('File Excel')
                ->disk('local') // WAJIB
                ->directory('imports') // WAJIB
                ->required()
                ->acceptedFileTypes([
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
            ]),
    ])
                ->action(function (array $data) {

                    $path = \Illuminate\Support\Facades\Storage::disk('local')->path($data['file']);

                    \Maatwebsite\Excel\Facades\Excel::import(
                        new \App\Imports\SiswaImport(auth()->id()),
                        $path
                    );

                    // SiswaImport sekarang ShouldQueue + WithChunkReading, jadi
                    // proses import berjalan di background worker, bukan
                    // langsung selesai di request ini.
                    \Filament\Notifications\Notification::make()
                        ->title('Import sedang diproses')
                        ->body('Data siswa sedang diimport di background. Silakan refresh halaman ini dalam beberapa saat.')
                        ->success()
                        ->send();
                }),

                // 🔥 PERUBAHAN 29 Agt 2026: upload banyak foto siswa
                // SEKALIGUS (drag & drop), tidak perlu lagi lewat
                // SFTP/SSH manual. Nama FILE ASLI yang di-upload harus
                // persis NIS (mis. 26271001.jpg) -- dipakai SEKALI
                // buat cocokkan ke siswa, sebelum diproses.
                //
                // Disimpan ke R2 (bukan disk lokal), dikonversi ke
                // WebP, PERSIS mengikuti pola yang SUDAH dipakai upload
                // foto satu-satu di atas (lihat FileUpload::make('foto')
                // -- disk('r2-public'), folder 'siswa-photos', cover
                // (800,1000), quality 80) -- supaya semua foto siswa
                // (satu-satu maupun massal) konsisten format & lokasinya.
                // Pola yang sama juga baru dibuat untuk Pegawai, lihat
                // PegawaiResource.php.
                //
                // CATATAN: ini terpisah dari fitur "foto otomatis" di
                // SiswaImport.php (yang masih cari file di folder lokal
                // storage/app/public/foto-siswa/ SAAT Import Excel
                // dijalankan) -- tombol ini adalah cara BARU yang
                // menggantikannya (tidak perlu upload file ke server
                // dulu secara manual), tapi kode lama itu belum dihapus,
                // masih ikut jalan berbarengan kalau memang folder
                // lokalnya diisi manual.
                Tables\Actions\Action::make('uploadFotoMassal')
                    ->label('Upload Foto Massal')
                    ->modalSubmitActionLabel('Upload & Pasangkan')
                    ->color('info')
                    ->icon('heroicon-o-photo')
                    ->form([

                        Placeholder::make('petunjuk_foto_massal_siswa')
                            ->label('Petunjuk')
                            ->content(new \Illuminate\Support\HtmlString(
                                'Nama tiap file foto <b>HARUS PERSIS SAMA</b> dengan NIS siswa yang bersangkutan.<br>' .
                                'Contoh: siswa dengan NIS <code>26271001</code> → nama file harus <code>26271001.jpg</code> (atau .png).<br>' .
                                'Bisa pilih/drag banyak file foto sekaligus di bawah ini — sistem otomatis mencocokkan ke siswa yang NIS-nya sesuai, dan foto disimpan ke R2 (format WebP), sama seperti upload foto satu-satu.'
                            )),

                        FileUpload::make('foto_massal_siswa')
                            ->label('Pilih Foto Siswa (bisa banyak sekaligus)')
                            ->multiple()
                            ->image()
                            ->maxSize(2048)
                            ->saveUploadedFileUsing(function ($file) {
                                $namaAsli = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                                $webp = Image::decode(file_get_contents($file->getRealPath()))
                                    ->cover(800, 1000)
                                    ->encodeUsingFileExtension('webp', quality: 80);

                                $filename = 'siswa-photos/' . uniqid() . '.webp';

                                \Illuminate\Support\Facades\Storage::disk('r2-public')->put($filename, (string) $webp);

                                \Illuminate\Support\Facades\Cache::put(
                                    'foto-massal-siswa-nis:' . $filename,
                                    $namaAsli,
                                    now()->addMinutes(10)
                                );

                                return $filename;
                            })
                            ->required(),

                    ])
                    ->action(function (array $data) {

                        $files = $data['foto_massal_siswa'] ?? [];
                        $cocok = 0;
                        $tidakCocok = [];

                        foreach ($files as $filePath) {
                            $nis = \Illuminate\Support\Facades\Cache::pull('foto-massal-siswa-nis:' . $filePath);

                            if (! $nis) {
                                $tidakCocok[] = basename($filePath) . ' (info NIS hilang, coba upload ulang)';
                                continue;
                            }

                            $siswa = \App\Models\Siswa::where('nis', $nis)->first();

                            if ($siswa) {
                                $siswa->update(['foto' => $filePath]);
                                $cocok++;
                            } else {
                                $tidakCocok[] = "NIS {$nis} tidak ditemukan di data siswa manapun";
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("Upload selesai — {$cocok} foto berhasil dipasangkan ke siswa")
                            ->body(count($tidakCocok) > 0
                                ? 'Nama file berikut TIDAK cocok dengan NIS siswa manapun (cek lagi penamaannya): ' . implode(', ', $tidakCocok)
                                : null)
                            ->color(count($tidakCocok) > 0 ? 'warning' : 'success')
                            ->send();
                    }),
                
                Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success')
                ->modalSubmitActionLabel('Export')
                ->form([

                Select::make('lembaga_id')
                ->label('Pilih Lembaga')
                ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('kelas_id', null)),

                Select::make('kelas_id')
                ->label('Pilih Kelas')
                ->options(function (callable $get) {

                $lembagaId = $get('lembaga_id');

                if (!$lembagaId) {
                    return [];
                }

                return \App\Models\Kelas::where('lembaga_id', $lembagaId)
                    ->pluck('nama', 'id');
            })
            ->disabled(fn (callable $get) => !$get('lembaga_id')),
    ])
    ->action(function (array $data) {

        return Excel::download(
            new \App\Exports\SiswaExport(
                $data['lembaga_id'] ?? null,
                $data['kelas_id'] ?? null
            ),
            'data-siswa.xlsx'
        );
    }),

                Action::make('export_buku_induk')
                ->label('Export Buku Induk')
                ->icon('heroicon-o-book-open')
                ->color('danger')
                ->action(function (array $data) {
                    return redirect()->to(
                        route('export.siswa.buku-induk', [
                            'lembaga_id' => $data['lembaga_id'] ?? null,
                            'kelas_id'   => $data['kelas_id'] ?? null,
                        ])
                    );
                
                })
                
                ->form([
                Select::make('lembaga_id')
                ->label('Pilih Lembaga')
                ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                ->reactive() // penting supaya kelas ikut berubah
                ->afterStateUpdated(fn (callable $set) => $set('kelas_id', null)),

                Select::make('kelas_id')
                ->label('Pilih Kelas')
                ->options(function (callable $get) {

                $lembagaId = $get('lembaga_id');

                if (!$lembagaId) {
                return [];
                }
    
                return \App\Models\Kelas::where('lembaga_id', $lembagaId)
                    ->pluck('nama', 'id');
                })
                ->disabled(fn (callable $get) => !$get('lembaga_id')),
                    ]),              
        
                    ])
                
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('cetak_kartu')
                ->label('Cetak Kartu')
                ->icon('heroicon-o-identification')
                ->color('success')
                ->url(fn ($record) => url('/kartu/siswa/'.$record->id))
                ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('delete')
                ->label('Hapus Terpilih')
                ->icon('heroicon-o-trash')
                ->action(function ($records) {
                        foreach ($records as $record) {
                        $record->delete();
                        }
                    })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()  // tanya dulu sebelum hapus
                ->color('danger'),

                Tables\Actions\BulkAction::make('naik_kelas')
                ->label('Naik Kelas')
                ->icon('heroicon-o-arrow-up-circle')
                ->form([
                Forms\Components\Select::make('kelas_id')
                ->label('Pilih Kelas Tujuan')
                ->relationship('kelas', 'nama')
                ->required(),
        ])
        ->action(function ($records, array $data) {

            foreach ($records as $siswa) {
                $siswa->update([
                    'kelas_id' => $data['kelas_id'],
                ]);
            }

        })
        ->color('success'),


    Tables\Actions\BulkAction::make('lulus')
    ->label('Lulus')
    ->icon('heroicon-o-academic-cap')
    ->form([
        Forms\Components\DatePicker::make('tanggal_lulus')
            ->required()
            ->label('Tanggal Lulus'),
    ])
    ->action(function ($records, array $data) {

        foreach ($records as $siswa) {
            $siswa->status_siswa = 'Lulus';
            $siswa->tanggal_lulus = $data['tanggal_lulus'];
            $siswa->save();
        }

    })
    ->deselectRecordsAfterCompletion()
    ->successNotificationTitle('Siswa berhasil diluluskan')
    ->color('primary'),

    Tables\Actions\BulkAction::make('pindah')
    ->label('Pindah')
    ->icon('heroicon-o-arrow-right-circle')
    ->form([
        Forms\Components\DatePicker::make('tanggal_pindah')
            ->required()
            ->label('Tanggal Pindah'),
    ])
    ->action(function ($records, array $data) {

        foreach ($records as $siswa) {
            $siswa->status_siswa = 'Pindah';
            $siswa->tanggal_pindah = $data['tanggal_pindah'];
            $siswa->save();
        }

    })
    ->deselectRecordsAfterCompletion()
    ->successNotificationTitle('Siswa berhasil dipindahkan')
    ->color('warning'),

    BulkAction::make('cetak_massal')
    ->label('Cetak Kartu Massal')
    ->icon('heroicon-o-printer')
    ->color('success')
    ->visible(fn () => (bool) auth()->user()?->is_platform_admin)

    ->action(function ($records) {

        $ids = $records->pluck('id')->implode(',');

        return redirect(
            url('/kartu/siswa-massal?ids='.$ids)
        );
    })
    ->deselectRecordsAfterCompletion()
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswas::route('/'),
            'create' => Pages\CreateSiswa::route('/create'),
            'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
            'tagihans.pembayarans',
            'kelas', // 🔥 WAJIB TAMBAH
            'lembaga',
        ]);
    }
}
