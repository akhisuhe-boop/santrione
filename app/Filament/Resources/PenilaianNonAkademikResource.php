<?php

namespace App\Filament\Resources;

use App\Models\Siswa;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\RaportNonAkademik;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Filament\Resources\PenilaianNonAkademikResource\Pages;
use App\Helpers\GradeHelper;
use Filament\Forms\Components\Hidden;

class PenilaianNonAkademikResource extends Resource
{
    protected static ?string $model = RaportNonAkademik::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Input Nilai Non-Akademik';
    protected static ?int $navigationSort = 7;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Data Utama')
                    ->schema([

                        Grid::make(3)
                        ->schema([

                            Hidden::make('kelas_id'),
                            Hidden::make('semester'),
                            Hidden::make('tahun_ajaran_id'),

                            Select::make('siswa_id')
                                ->label('Nama')
                                ->relationship('siswa', 'nama_lengkap')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {

                                    $siswa = \App\Models\Siswa::with('kelas')->find($state);

                                    if (!$siswa) {
                                        return;
                                    }

                                    // kelas otomatis
                                    $set('kelas_id', $siswa->kelas_id);

                                    $set(
                                        'kelas_nama',
                                        $siswa->kelas->nama ?? '-'
                                    );

                                    // tahun ajaran aktif
                                    $tahunAjaran = \App\Models\TahunAjaran::where('aktif', true)->first();

                                    if ($tahunAjaran) {

                                        $set('tahun_ajaran_id', $tahunAjaran->id);

                                        $set(
                                            'tahun_ajaran_nama',
                                            $tahunAjaran->nama . ' - ' . $tahunAjaran->semester
                                        );

                                        $set('semester', $tahunAjaran->semester);
                                    }
                                }),

                            TextInput::make('kelas_nama')
                                ->label('Kelas')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('tahun_ajaran_nama')
                                ->label('Tahun Ajaran')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Grid::make(2)
    ->schema([

        /*
        |--------------------------------------------------------------------------
        | KEPRIBADIAN
        |--------------------------------------------------------------------------
        */

        Section::make('Kepribadian')
            ->columnSpan(1)
            ->schema([

                Repeater::make('kepribadians')
                    ->relationship()
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextInput::make('aspek')
                                    ->required(),

                                TextInput::make('nilai')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('grade', GradeHelper::generate($state));
                                    }),

                                TextInput::make('grade')
                                    ->readOnly(),

                            ]),
                    ])
                    ->defaultItems(2)
                    ->cloneable(),

            ]),

        /*
        |--------------------------------------------------------------------------
        | EKSTRAKURIKULER
        |--------------------------------------------------------------------------
        */

        Section::make('Ekstrakurikuler')
            ->columnSpan(1)
            ->schema([

                Repeater::make('ekstrakurikulers')
                    ->relationship()
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextInput::make('nama_ekskul')
                                    ->label('Ekskul')
                                    ->required(),

                                TextInput::make('nilai')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('grade', GradeHelper::generate($state));
                                    }),

                                TextInput::make('grade')
                                    ->readOnly(),

                            ]),
                    ])
                    ->defaultItems(2)
                    ->cloneable(),

            ]),

    ]),

                Section::make('Catatan Wali Kelas')
                    ->schema([

                        Textarea::make('catatan_wali_kelas')
                            ->rows(2)
                            ->columnSpanFull(),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kelas.nama')
                    ->label('Kelas'),

                Tables\Columns\IconColumn::make('kepribadian_status')
                    ->label('Kepribadian')
                    ->boolean()
                    ->getStateUsing(fn ($record) =>
                        $record->kepribadians()->count() > 0
                    ),

                Tables\Columns\IconColumn::make('ekskul_status')
                    ->label('Ekstrakurikuler')
                    ->boolean()
                    ->getStateUsing(fn ($record) =>
                        $record->ekstrakurikulers()->count() > 0
                    ),

                Tables\Columns\IconColumn::make('catatan_status')
                    ->label('Catatan')
                    ->boolean()
                    ->getStateUsing(fn ($record) =>
                        filled($record->catatan_wali_kelas)
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {

                        $kepribadian = $record->kepribadians()->count() > 0;

                        $ekskul = $record->ekstrakurikulers()->count() > 0;

                        $catatan = filled($record->catatan_wali_kelas);

                        return (
                            $kepribadian &&
                            $ekskul &&
                            $catatan
                        )
                            ? 'Lengkap'
                            : 'Belum Lengkap';
                    })
                    ->colors([
                        'success' => 'Lengkap',
                        'danger' => 'Belum Lengkap',
                    ]),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenilaianNonAkademiks::route('/'),
            'create' => Pages\CreatePenilaianNonAkademik::route('/create'),
            'edit' => Pages\EditPenilaianNonAkademik::route('/{record}/edit'),
        ];
    }
}