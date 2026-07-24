<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasResource\Pages;
use App\Filament\Resources\KasResource\RelationManagers;
use App\Models\Kas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Support\RawJs;

class KasResource extends BaseResource
{
    protected static ?string $model = Kas::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Input Kas';
    protected static ?int $navigationSort = 13;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Section::make('Transaksi Kas')
            ->icon('heroicon-o-arrows-right-left')
            ->schema([

            Forms\Components\Select::make('tipe')
                ->options([
                    'masuk' => 'Kas Masuk',
                    'keluar' => 'Kas Keluar',
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($set) => $set('kategori_id', null)),

            Forms\Components\Select::make('kategori_id')
                ->label('Kategori')
                ->options(function ($get) {

                    if (!$get('tipe')) return [];

                    return \App\Models\KategoriKas::where('tipe', $get('tipe'))
                        ->where('is_active', true)
                        ->pluck('nama', 'id');
                })
                ->required()
                ->reactive()
                ->placeholder(fn ($get) => 
                    $get('tipe') 
                        ? 'Pilih kategori ' . $get('tipe') 
                        : 'Pilih tipe dulu'
                ),

                Forms\Components\Select::make('lembaga_id')
                ->label('Lembaga')
                ->relationship('lembaga', 'nama')
                ->preload()
                ->required(),

                Forms\Components\Select::make('rekening_id')
                ->label('Rekening')
                ->relationship(
                    name: 'rekening',
                    titleAttribute: 'nama'
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => 
                    "{$record->nama} - {$record->bank} ({$record->no_rekening}) | Saldo: Rp " . number_format($record->saldo, 0, ',', '.')
                )
                ->preload()
                ->required(),

                Forms\Components\TextInput::make('nominal')
                    ->numeric()
                    ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                    ->stripCharacters('.')
                    ->required()
                    ->rule(function ($get) {
                    return function ($attribute, $value, $fail) use ($get) {
                        if ($get('tipe') === 'keluar') {
                            $rekeningId = $get('rekening_id');
                            if (!$rekeningId) return;
                            $rekening = \App\Models\Rekening::find($rekeningId);
                            if (!$rekening) return;
                            $saldo = $rekening->saldo;
                            if ($value > $saldo) {
                                $fail('Saldo tidak mencukupi! Saldo saat ini: Rp ' . number_format($saldo, 0, ',', '.'));
                            }
                        }

                    };
                }),

                Forms\Components\DatePicker::make('tanggal')
                    ->default(now())
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->placeholder('Keterangan tambahan')
                    ->Required(),

                Forms\Components\TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->placeholder('Contoh: Bu Siti / Pa Ahmad / Bendahara')
                    ->maxLength(100),

                FileUpload::make('bukti')
                    ->label('Bukti Transaksi')
                    ->image() // kalau mau khusus gambar
                    ->directory('bukti-kas')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB
                    ->previewable()
                    ->downloadable()
                    ->openable(),

            ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => 
                $query->with([
                    'lembaga',
                    'rekening',

                    // penting
                    'pembayaran',

                    // wajib
                    'pembayaran.siswa',

                    // turunan
                    'pembayaran.siswa.kelas',
                    'pembayaran.siswa.lembaga',
                ])
            )

            ->defaultSort('tanggal', 'desc')

            ->columns([
                Tables\Columns\TextColumn::make('kode'),
                Tables\Columns\BadgeColumn::make('tipe')
                ->colors([
                    'primary' => 'masuk', // 🔵
                    'danger' => 'keluar', // 🔴
                ])
                ->formatStateUsing(fn ($state) => 
                    $state === 'masuk' ? 'Masuk' : 'Keluar'
                ),
                
                Tables\Columns\TextColumn::make('lembaga_fix')
                    ->label('Lembaga')
                    ->badge()
                    ->color(function ($state) {
                        $state = strtolower($state);

                        if (str_contains($state, 'sdit')) return 'success';
                        if (str_contains($state, 'smpit')) return 'warning';
                        if (str_contains($state, 'smait')) return 'danger';

                        return 'primary';
                    })
                    ->getStateUsing(fn ($record) =>
                        $record->pembayaran?->siswa?->lembaga?->nama
                        ?? $record->lembaga?->nama
                        ?? '-'
                    ),

                Tables\Columns\TextColumn::make('kategori.nama')
                ->label('Kategori')
                ->getStateUsing(function ($record) {

                    $nama = $record->kategori?->nama ?? '-';

                    // 🔥 kalau SPP → tambahkan bulan
                    if (strtolower($nama) === 'spp' && $record->tanggal) {

                        $bulanTahun = \Carbon\Carbon::parse($record->tanggal)
                            ->translatedFormat('F Y');

                        return "SPP ({$bulanTahun})";
                    }

                    return $nama;
                }),

                Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state) => 
                    \Carbon\Carbon::parse($state)->translatedFormat('d F Y')
                ),
                   
                Tables\Columns\TextColumn::make('nominal')
                ->label('Nominal')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                
                Tables\Columns\TextColumn::make('keterangan_custom')
                ->label('Keterangan')
                ->getStateUsing(function ($record) {

                    $siswa = $record->pembayaran?->siswa?->nama_lengkap;
                    $kelas = $record->pembayaran?->siswa?->kelas?->nama;

                    if ($siswa || $kelas) {
                        return trim(($siswa ?? '-') . ' - ' . ($kelas ?? '-'));
                    }

                    return $record->penanggung_jawab ?? '-';
                })
                ->placeholder('-'),

                Tables\Columns\TextColumn::make('rekening.nama')
                ->label('Rekening')
                ->formatStateUsing(fn ($record) => 
                    $record->rekening 
                        ? "{$record->rekening->bank} ({$record->rekening->no_rekening})"
                        : '-'
                ),
                
                Tables\Columns\BadgeColumn::make('bukti_fix')
                ->label('Bukti Transaksi')
                ->getStateUsing(function ($record) {
                    return ($record->pembayaran?->bukti_transfer || $record->bukti)
                        ? 'Lihat Bukti'
                        : '-';
                })
                ->colors([
                    'primary' => 'Lihat Bukti',
                    'gray' => '-',
                ])
               ->icons([
                    'heroicon-o-eye' => 'Lihat Bukti',
                ])

                ->url(function ($record) {

                    if ($record->pembayaran?->bukti_transfer) {
                        return asset('storage/' . $record->pembayaran->bukti_transfer);
                    }

                    if ($record->bukti) {
                        return asset('storage/' . $record->bukti);
                    }

                    return null;
                })

                ->openUrlInNewTab()

                ->disabled(fn ($record) => 
                    !($record->pembayaran?->bukti_transfer || $record->bukti)
                )
            ])
            ->filters([

    Tables\Filters\SelectFilter::make('lembaga_id')
        ->label('Lembaga')
        ->relationship('lembaga', 'nama')
        ->searchable()
        ->preload(),

    Tables\Filters\SelectFilter::make('tipe')
        ->label('Tipe')
        ->options([
            'masuk' => 'Masuk',
            'keluar' => 'Keluar',
        ]),

    Tables\Filters\SelectFilter::make('kategori_id')
        ->label('Kategori')
        ->relationship('kategori', 'nama')
        ->searchable()
        ->preload(),

])
            ->actions([
                //
            ])
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
            'index' => Pages\ListKas::route('/'),
            'create' => Pages\CreateKas::route('/create'),
            'edit' => Pages\EditKas::route('/{record}/edit'),
        ];
    }
}
