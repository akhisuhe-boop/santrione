<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahfidzSetoranResource\Pages;
use App\Models\TahfidzSetoran;
use App\Helpers\QuranHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TahfidzSetoranResource extends BaseResource
{
    protected static ?string $model = TahfidzSetoran::class;
    protected static ?string $navigationGroup = 'Tahfidz';
    protected static ?string $navigationLabel = 'Setoran Tahfidz';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Setoran Tahfidz')
                ->description('Form untuk mencatat setoran tahfidz siswa')
                ->schema([

                    Forms\Components\Select::make('siswa_id')
                        ->label('Siswa')
                        ->relationship('siswa', 'nama_lengkap')
                        ->searchable()
                        ->required()
                        ->live(),

                    Forms\Components\Placeholder::make('progress_badge')
                        ->label('Progress Hafalan')
                        ->reactive()
                        ->content(function ($get) {

                            $siswa = $get('siswa_id');

                            if (!$siswa) {
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-500'>
                                        -
                                    </span>"
                                );
                            }

                            $last = \App\Models\TahfidzSetoran::select('surah_id', 'ayat_sampai', 'juz_id')
                                ->where('siswa_id', $siswa)
                                ->where('jenis', 'ziyadah')
                                ->latest()
                                ->first();

                            if (!$last) {
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-500'>
                                        Belum ada
                                    </span>"
                                );
                            }

                            $surah = \App\Models\Surah::find($last->surah_id)?->nama;
                            $juz   = \App\Models\Juz::find($last->juz_id)?->nama;

                            return new \Illuminate\Support\HtmlString("
                        <div class='inline-block px-4 py-2 rounded-lg bg-primary-500 text-white text-sm font-semibold'>
                            {$surah} : {$last->ayat_sampai} ({$juz})
                        </div>
                    ");
                        }),

                    Forms\Components\Select::make('pegawai_id')
                        ->label('Ustadz')
                        ->relationship('pegawai', 'nama')
                        ->searchable()
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal')
                        ->required(),

                    Forms\Components\Select::make('jenis')
                        ->options([
                            'ziyadah' => 'Ziyadah',
                            'murajaah' => 'Murajaah',
                        ])
                        ->required()
                        ->live(),

                    // ✅ SURAH (STABIL)
                    Forms\Components\Select::make('surah_id')
                        ->label('Surah')
                        ->relationship('surah', 'nama')
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {

                            if (!$state) return;

                            // 🔥 mapping cepat surah → juz
                            $mapping = [
                                78 => 30, 79 => 30, 80 => 30, 81 => 30, 82 => 30,
                                83 => 30, 84 => 30, 85 => 30, 86 => 30, 87 => 30,
                                88 => 30, 89 => 30, 90 => 30, 91 => 30, 92 => 30,
                                93 => 30, 94 => 30, 95 => 30, 96 => 30, 97 => 30,
                                98 => 30, 99 => 30, 100 => 30, 101 => 30, 102 => 30,
                                103 => 30, 104 => 30, 105 => 30, 106 => 30, 107 => 30,
                                108 => 30, 109 => 30, 110 => 30, 111 => 30, 112 => 30,
                                113 => 30, 114 => 30,
                            ];

                            $juz = $mapping[$state] ?? null;

                            if ($juz) {
                                $juzModel = \App\Models\Juz::where('nama', 'Juz ' . $juz)->first();
                                if ($juzModel) {
                                    $set('juz_id', $juzModel->id);
                                }
                            }
                        }),

                    // ✅ JUZ AUTO (LOCK)
                    Forms\Components\Select::make('juz_id')
                        ->label('Juz')
                        ->relationship('juz', 'nama')
                        ->disabled()
                        ->dehydrated()
                        ->live(),

                    // ✅ AYAT DARI
                    Forms\Components\TextInput::make('ayat_dari')
                    ->label('Dari Ayat')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->rule(function ($get) {
                        return function ($attribute, $value, $fail) use ($get) {

                            // 🔥 hanya berlaku untuk ZIYADAH
                            if ($get('jenis') !== 'ziyadah') return;

                            $siswa = $get('siswa_id');
                            $surah = $get('surah_id');

                            if (!$siswa || !$surah) return;

                            $last = \App\Models\TahfidzSetoran::where('siswa_id', $siswa)
                                ->where('jenis', 'ziyadah')
                                ->where('surah_id', $surah)
                                ->latest()
                                ->first();

                            if ($last) {

                                $nextAyat = ($last->ayat_sampai ?? 0) + 1;

                                // ❌ tidak boleh ulang / mundur
                                if ($value < $nextAyat) {
                                    $fail("Harus mulai dari ayat {$nextAyat}");
                                }
                            }
                        };
                    }),

                    // ✅ AYAT SAMPAI + AUTO JUZ + VALIDASI
                    Forms\Components\TextInput::make('ayat_sampai')
                        ->label('Sampai Ayat')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->live(debounce: 700)
                        ->rule(function ($get) {
                        return function ($attribute, $value, $fail) use ($get) {

                            if ($get('jenis') !== 'ziyadah') return;

                            $dari = $get('ayat_dari');

                            if ($value < $dari) {
                                $fail('Ayat sampai tidak boleh lebih kecil dari ayat dari');
                            }
                        };
                    })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {

                            $surahId = $get('surah_id');
                            $ayatDari = $get('ayat_dari');
                            $ayatSampai = $state;

                            if (!$surahId || !$ayatSampai) return;

                            // 🔥 VALIDASI AYAT
                            $surah = \App\Models\Surah::find($surahId);

                            if ($surah) {

                                // ❌ ayat kebalik
                                if ($ayatDari && $ayatSampai < $ayatDari) {
                                    $set('ayat_sampai', null);
                                    return;
                                }

                                // ❌ melebihi jumlah ayat
                                if ($ayatSampai > $surah->jumlah_ayat) {
                                    $set('ayat_sampai', $surah->jumlah_ayat);
                                }
                            }

                            // 🔥 AUTO JUZ (AKURAT)
                            $juz = QuranHelper::getJuz($surahId, $ayatSampai);

                            if ($juz) {
                                $juzModel = \App\Models\Juz::where('nama', 'Juz ' . $juz)->first();

                                if ($juzModel) {
                                    $set('juz_id', $juzModel->id);
                                }
                            }
                        }),

                    // ✅ NILAI
                    Forms\Components\TextInput::make('nilai')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(fn ($get) => $get('jenis') === 'ziyadah')
                        ->disabled(fn ($get) => $get('jenis') !== 'ziyadah')
                        ->dehydrated(fn ($get) => $get('jenis') === 'ziyadah'),

                    // ✅ CATATAN
                    Forms\Components\Textarea::make('catatan')
                        ->rows(3),

                ])
                ->columns(4),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->colors([
                        'success' => 'ziyadah',
                        'primary' => 'murajaah',
                    ]),

                Tables\Columns\TextColumn::make('surah.nama')
                    ->label('Surah'),

                Tables\Columns\TextColumn::make('ayat_dari')
                    ->label('Dari Ayat'),

                Tables\Columns\TextColumn::make('ayat_sampai')
                    ->label('Sampai Ayat'),

                Tables\Columns\TextColumn::make('jumlah_ayat')
                    ->label('Jml Ayat'),

                Tables\Columns\TextColumn::make('nilai')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Ustadz'),

            ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTahfidzSetorans::route('/'),
            'create' => Pages\CreateTahfidzSetoran::route('/create'),
            'edit' => Pages\EditTahfidzSetoran::route('/{record}/edit'),
        ];
    }
}