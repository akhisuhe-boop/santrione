<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\QinaraKasTransaksiResource\Pages;
use App\Models\QinaraKasKategori;
use App\Models\QinaraKasTransaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- kas masuk/keluar untuk pembukuan internal bisnis
 * Qinara sendiri (revenue langganan, biaya operasional, gaji, dll).
 * Model laba-rugi sederhana (bukan double-entry) -- lihat juga
 * Pages\LaporanLabaRugi untuk rekapnya.
 */
class QinaraKasTransaksiResource extends BaseResource
{
    protected static ?string $model = QinaraKasTransaksi::class;
    protected static ?string $navigationLabel = 'Kas Masuk & Keluar';
    protected static ?string $navigationGroup = 'Keuangan Qinara';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Transaksi Kas';
    protected static ?string $pluralModelLabel = 'Kas Masuk & Keluar';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Transaksi Kas')
                ->schema([
                    Forms\Components\DatePicker::make('tanggal')
                        ->required()
                        ->default(now())
                        ->native(false),

                    Forms\Components\Select::make('tipe')
                        ->label('Tipe')
                        ->options([
                            'masuk' => 'Kas Masuk (Pemasukan)',
                            'keluar' => 'Kas Keluar (Pengeluaran)',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('kategori_id', null)),

                    Forms\Components\Select::make('kategori_id')
                        ->label('Kategori')
                        ->options(fn (Forms\Get $get) => QinaraKasKategori::query()
                            ->when($get('tipe'), fn ($q, $tipe) => $q->where('tipe', $tipe))
                            ->where('aktif', true)
                            ->pluck('nama', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Pilih tipe dulu -- daftar kategori otomatis kefilter sesuai tipe yang dipilih.'),

                    Forms\Components\TextInput::make('nominal')
                        ->label('Nominal (Rp)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp'),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->badge()
                    ->color(fn (string $state) => $state === 'masuk' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => $state === 'masuk' ? 'Kas Masuk' : 'Kas Keluar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\IconColumn::make('subscription_payment_id')
                    ->label('Otomatis?')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-pencil')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? 'Tercatat otomatis dari pembayaran gateway' : 'Diinput manual'),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(40)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('diinputOleh.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'masuk' => 'Kas Masuk',
                        'keluar' => 'Kas Keluar',
                    ]),

                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->options(fn () => QinaraKasKategori::pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->native(false),
                        Forms\Components\DatePicker::make('sampai')->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
                            ->when($data['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQinaraKasTransaksis::route('/'),
            'create' => Pages\CreateQinaraKasTransaksi::route('/create'),
            'edit' => Pages\EditQinaraKasTransaksi::route('/{record}/edit'),
        ];
    }
}
