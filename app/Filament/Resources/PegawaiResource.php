<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Laravel\Facades\Image;
use App\Imports\PegawaiImport;
use App\Exports\PegawaiExport;
use App\Exports\PegawaiTemplateExport;

class PegawaiResource extends BaseResource
{
    protected static ?string $model = Pegawai::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 5;
    
    // 🔥 WAJIB: LOAD RELASI
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('lembagas');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ================= DATA PEGAWAI =================
            Forms\Components\Section::make('Data Pegawai')
                ->description('Isi data pegawai')
                ->schema([
                    Forms\Components\TextInput::make('nama')
                    ->placeholder('Masukkan Nama Lengkap')
                    ->required(),

                    Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->placeholder('Masukkan NIK'),

                    Forms\Components\TextInput::make('niy')
                    ->label('NIY')
                    ->placeholder('Masukkan NIY')
                    ->required()
                    ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('no_hp')
                    ->label('No HP / WA')
                    ->placeholder('Masukkan nomor WhatsApp'),

                    Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->placeholder('Masukkan alamat email')
                    ->email(),

                    Forms\Components\Textarea::make('alamat')
                    ->label('Alamat')
                    ->rows(2)
                    ->placeholder('Masukkan alamat lengkap')
                    ->columnSpanFull(),
                ])
                ->columns(2),

            // ================= DATA TAMBAHAN =================
            Forms\Components\Section::make('Data Tambahan')
            ->description('Data administratif pegawai')
            ->schema([

                // 🔥 GOLONGAN
                Forms\Components\TextInput::make('golongan')
                    ->placeholder('Contoh: III/a atau A1'),

                // 🔥 PENDIDIKAN
                Forms\Components\TextInput::make('pendidikan')
                    ->label('Pendidikan Terakhir & Program Studi')
                    ->placeholder('S1, S2, D3'),

                // 🔥 UNIVERSITAS
                Forms\Components\TextInput::make('universitas')
                    ->placeholder('Nama Universitas'),

                // 🔥 TANGGAL MASUK
                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->displayFormat('d/m/Y'),

                // 🔥 FOTO
                Forms\Components\FileUpload::make('foto')
		    ->image()
		    ->disk('r2-public')
		    ->maxSize(2048)
		    ->imagePreviewHeight('100')
		    ->saveUploadedFileUsing(function ($file) {
		        $webp = Image::decode(file_get_contents($file->getRealPath()))
		            ->cover(800, 1000)
		            ->encodeUsingFileExtension('webp', quality: 80);

		        $filename = 'pegawai-photos/' . uniqid() . '.webp';

		        \Storage::disk('r2-public')->put($filename, (string) $webp);

		        return $filename;
		    }),

                // 🔥 IJAZAH
                Forms\Components\FileUpload::make('file_ijazah')
                    ->label('Fotocopy Ijazah')
		    ->disk('r2-private')
		    ->maxSize(2048)
                    ->directory('pegawai/ijazah')
                    ->acceptedFileTypes(['application/pdf','image/*'])
                    ->maxSize(2048),

                // 🔥 STATUS
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),

            ])
            ->columns(2),

            // ================= MULTI LEMBAGA =================
            Forms\Components\Section::make('Penugasan Lembaga')
                ->schema([

                    Forms\Components\Repeater::make('pegawaiLembaga')
                        ->label('Lembaga & Jabatan')
                        ->schema([
                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->placeholder('Yayasan/Pesantren (bukan 1 lembaga tertentu)')
                            ->options(
                                \App\Models\Lembaga::orderBy('nama')
                                    ->pluck('nama', 'id')
                            ),

                        Forms\Components\TextInput::make('jabatan')
                            ->placeholder('Guru / TU / Kepala')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'tetap' => 'Pegawai Tetap',
                                'honorer' => 'Pegawai Honorer',
                            ])
                            ->required()
                            ])
                            ->columns(3)
                            ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('foto')
                ->disk('r2-public')
                ->circular()
                ->size(40),

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('niy')
                    ->label('NIY')
                    ->searchable(),

                Tables\Columns\TextColumn::make('no_hp')
                ->label('No WA')
                ->searchable()
                ->copyable()
                ->copyMessage('Nomor berhasil disalin'),

                Tables\Columns\TextColumn::make('lembaga_list')
                    ->label('Lembaga')
                    ->badge()
                    ->getStateUsing(fn ($record) =>
                        $record->pegawaiLembagas
                            ->map(fn ($pl) => $pl->lembaga?->nama ?? 'Yayasan/Pesantren')
                            ->join(', ')
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('jabatan_list')
                    ->label('Jabatan')
                    ->getStateUsing(fn ($record) => $record->pegawaiLembagas->pluck('jabatan')->filter()->join(', ')),    

                Tables\Columns\TextColumn::make('pendidikan')
                    ->label('Pendidikan'),

                Tables\Columns\TextColumn::make('universitas')
                ->label('Universitas')
                ->searchable()
                ->limit(20),

                Tables\Columns\TextColumn::make('status_list')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->pegawaiLembagas->pluck('status')->filter()->join(', ')),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),

            ])
            ->filters([

                // 🔥 FILTER LEMBAGA (SUDAH ADA)
                Tables\Filters\SelectFilter::make('lembaga_id')
                    ->label('Filter Lembaga')
                    ->options(\App\Models\Lembaga::pluck('nama', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('lembagas', function ($q) use ($data) {
                                $q->where('lembaga_id', $data['value']);
                            });
                        }
                    }),

                // 🔥 STATUS AKTIF
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status Aktif')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ]),

                // 🔥 JENIS KELAMIN
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                // 🔥 STATUS PEGAWAI (PIVOT)
                Tables\Filters\SelectFilter::make('status_pegawai')
                    ->label('Status Pegawai')
                    ->options([
                        'tetap' => 'Tetap',
                        'honorer' => 'Honorer',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('lembagas', function ($q) use ($data) {
                                $q->where('status', $data['value']);
                            });
                        }
                    }),

            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('reset_password')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->visible(fn ($record) => filled($record->niy))
                ->requiresConfirmation()
                ->modalHeading('Reset Password ke NIY')
                ->modalDescription(fn ($record) =>
                    'Password login "' . $record->nama . '" akan direset ke NIY-nya sendiri ('
                    . $record->niy . '). Pegawai bisa langsung login pakai NIY itu sebagai password.'
                )
                ->modalSubmitActionLabel('Ya, Reset')
                ->action(function ($record) {

                    $record->update([
                        'password' => \Illuminate\Support\Facades\Hash::make($record->niy),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Password berhasil direset')
                        ->body('Password "' . $record->nama . '" sudah kembali ke NIY (' . $record->niy . ').')
                        ->success()
                        ->send();
                }),

                Tables\Actions\Action::make('cetak')
                ->label('Cetak ID')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('kartu.pegawai', [
                    'ids' => $record->id
                ]))
                ->openUrlInNewTab(),
            ])
            
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('cetak_kartu')
                ->label('Cetak Kartu ID')
                ->icon('heroicon-o-identification')
                ->color('success')
                ->visible(fn () => (bool) auth()->user()?->is_platform_admin)
                ->action(function ($records) {

                    $ids = $records->pluck('id')->join(',');

                    return redirect()->route('kartu.pegawai', [
                        'ids' => $ids
                    ]);
                }),         
            ])
            
            ->headerActions([

                Action::make('create')
                    ->label('Tambah Pegawai')
                    ->url(PegawaiResource::getUrl('create'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary'),

                Tables\Actions\Action::make('Export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\PegawaiExport,
                        'pegawai.xlsx'
                    )),

                Tables\Actions\Action::make('Import')
                    ->label('Import Excel')
                    ->modalSubmitActionLabel('Upload')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->form([

                        // 🔥 LINK DOWNLOAD TEMPLATE
                        Forms\Components\Placeholder::make('download_template')
                            ->label('Download Template')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<a href="' . route('pegawai.template') . '" target="_blank" style="color:#16a34a;font-weight:bold;">
                                    ⬇️ Download Template Excel
                                </a>'
                            )),

                        // 🔥 UPLOAD FILE
                        Forms\Components\FileUpload::make('file')
                        ->disk('public')
                        ->directory('imports')
                        ->required(),

                    ])
                    ->action(function (array $data) {

                        $path = storage_path('app/public/' . $data['file']);
                    
                        \Maatwebsite\Excel\Facades\Excel::import(
                            new \App\Imports\PegawaiImport,
                            $path
                        );
                    })
            ]);
            
            
    }

    // ================= LOAD DATA SAAT EDIT =================
    public static function mutateFormDataBeforeFill(array $data): array
    {
        $data['pegawaiLembaga'] = DB::table('pegawai_lembaga')
            ->where('pegawai_id', $data['id'])
            ->get()
            ->map(function ($item) {
                return [
                    'lembaga_id' => $item->lembaga_id,
                    'jabatan' => $item->jabatan,
                    'status' => $item->status,
                ];
            })
            ->toArray();

        return $data;
    }

    // ================= SAVE CREATE =================
    public static function afterCreate($record, array $data): void
    {
        if (isset($data['pegawaiLembaga'])) {
            foreach ($data['pegawaiLembaga'] as $item) {
                DB::table('pegawai_lembaga')->insert([
                    'pegawai_id' => $record->id,
                    'lembaga_id' => $item['lembaga_id'],
                    'jabatan' => $item['jabatan'] ?? null,
                    'status' => $item['status'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ================= SAVE EDIT =================
    public static function afterSave($record, array $data): void
    {
        if (isset($data['pegawaiLembaga'])) {

            DB::table('pegawai_lembaga')
                ->where('pegawai_id', $record->id)
                ->delete();

            foreach ($data['pegawaiLembaga'] as $item) {
                DB::table('pegawai_lembaga')->insert([
                    'pegawai_id' => $record->id,
                    'lembaga_id' => $item['lembaga_id'],
                    'jabatan' => $item['jabatan'] ?? null,
                    'status' => $item['status'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }
}
