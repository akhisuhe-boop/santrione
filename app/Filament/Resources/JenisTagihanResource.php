<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisTagihanResource\Pages;
use App\Models\JenisTagihan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use App\Filament\Resources\JenisTagihanResource\RelationManagers\SettingNominalTagihansRelationManager;
use Filament\Support\RawJs;

class JenisTagihanResource extends BaseResource
{
    protected static ?string $model = JenisTagihan::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Jenis Tagihan';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $label = 'Tagihan Umum';
    protected static ?string $pluralLabel = 'Tagihan Umum';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Jenis Tagihan')
                    ->icon('heroicon-o-tag')
                    ->schema([

                        Forms\Components\TextInput::make('nama')
                            ->label('Jenis Tagihan')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('kode', \Illuminate\Support\Str::slug($state, '_'));
                            })
                            ->placeholder('Contoh: SPP, PPDB, Daftar Ulang'),

                        Forms\Components\TextInput::make('kode')
                            ->label('Kode')
                            ->required()
                            ->disabled() // 🔥 tidak bisa diedit
                            ->dehydrated() // 🔥 tetap disimpan ke DB
                            ->helperText('Kode otomatis dari nama'),

                        Forms\Components\Select::make('tipe_sistem')
                            ->label('Peran Sistem')
                            ->helperText('Tandai kalau jenis tagihan ini yang dipakai sistem untuk alur PPDB otomatis (nama tampilan boleh apa saja, tidak harus persis).')
                            ->options([
                                'pendaftaran_ppdb' => 'Biaya Pendaftaran PPDB',
                                'daftar_ulang_ppdb' => 'Biaya Daftar Ulang PPDB',
                            ])
                            ->placeholder('Tagihan umum (bukan bagian alur PPDB otomatis)')
                            ->nullable(),

                        Forms\Components\TextInput::make('default_nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->required()
                            ->placeholder('Nominal yang akan dibuat tagihan siswa'),

                        Forms\Components\Select::make('kategori_kas_id')
                            ->label('Kategori Kas')
                            ->relationship(
                                name: 'kategoriKas',
                                titleAttribute: 'nama',
                                modifyQueryUsing: fn ($query) => $query->where('tipe', 'masuk') // 🔥 INI KUNCINYA
                            )
                            ->required(),

                        Forms\Components\Toggle::make('is_bulanan')
                            ->label('Tagihan Bulanan?')
                            ->helperText('Aktifkan jika tagihan ini berulang setiap bulan (contoh: SPP)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // 🔥 otomatis matikan cicilan
                                    $set('is_cicilan', false);
                                }
                            }),

                        Forms\Components\Toggle::make('is_cicilan')
                            ->label('Boleh Dicicil?')
                            ->helperText('Nonaktif jika bulanan aktif')
                            ->default(true)
                            ->disabled(fn ($get) => $get('is_bulanan'))
                            ->dehydrateStateUsing(function ($state, $get) {
                                // 🔥 paksa false kalau bulanan
                                return $get('is_bulanan') ? false : $state;
                            }),

                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('tipe_sistem')
                    ->label('Peran Sistem')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pendaftaran_ppdb' => 'Pendaftaran PPDB',
                        'daftar_ulang_ppdb' => 'Daftar Ulang PPDB',
                        default => 'Tagihan Umum',
                    }),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Jenis Tagihan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategoriKas.nama')
                    ->label('Kategori Kas')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('default_nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),                

                Tables\Columns\IconColumn::make('is_bulanan')
                    ->label('Bulanan')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_cicilan')
                    ->label('Dicicil')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y'),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('kategori_kas_id')
                    ->label('Kategori Kas')
                    ->relationship('kategoriKas', 'nama'),

                Tables\Filters\TernaryFilter::make('is_bulanan')
                    ->label('Bulanan'),

                Tables\Filters\TernaryFilter::make('is_cicilan')
                    ->label('Cicilan'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SettingNominalTagihansRelationManager::class, // 🔥 tambah ini
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisTagihans::route('/'),
            'create' => Pages\CreateJenisTagihan::route('/create'),
            'edit' => Pages\EditJenisTagihan::route('/{record}/edit'),
        ];
    }
}