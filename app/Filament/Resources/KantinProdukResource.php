<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KantinProdukResource\Pages;
use App\Models\KantinProduk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class KantinProdukResource extends BaseResource
{
    protected static ?string $model = KantinProduk::class;
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Produk Kantin')
                    ->description('Data dasar produk yang dijual di kantin.')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([

                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->relationship('lembaga', 'nama', fn ($query) => $query->where(
                                'yayasan_id',
                                \Filament\Facades\Filament::getTenant()?->id
                            ))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('barcode')
                            ->label('Barcode / Kode Scan')
                            ->unique(ignoreRecord: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-qr-code')
                                    ->action(fn ($set) => $set('barcode', 'PRD-' . strtoupper(\Illuminate\Support\Str::random(8))))
                            )
                            ->helperText('Kode unik buat di-scan di halaman Kasir. Kosongkan lalu klik ikon di kanan untuk generate otomatis, atau isi manual sesuai barcode kemasan produk.'),

                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->placeholder('Makanan / Minuman / Snack / dll')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('harga')
                            ->label('Harga')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\TextInput::make('stok')
                            ->label('Stok')
                            ->numeric()
                            ->helperText('Kosongkan kalau tidak mau lacak stok (mis. produk buatan langsung/tidak terbatas).'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif (bisa dijual)')
                            ->default(true),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Gambar Produk')
                    ->icon('heroicon-o-photo')
                    ->schema([

                        Forms\Components\FileUpload::make('gambar')
                            ->label('Gambar')
                            ->image()
                            ->disk('r2-public')
                            ->maxSize(2048)
                            ->saveUploadedFileUsing(function ($file) {
                                $webp = \Intervention\Image\Laravel\Facades\Image::decode(file_get_contents($file->getRealPath()))
                                    ->cover(800, 800)
                                    ->encodeUsingFileExtension('webp', quality: 80);

                                $filename = 'kantin-produk/' . uniqid() . '.webp';

                                \Storage::disk('r2-public')->put($filename, (string) $webp);

                                return $filename;
                            })
                            ->columnSpanFull(),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('gambar')
                    ->label('')
                    ->disk('r2-public')
                    ->circular(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Produk')
                    ->searchable(),

                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga'),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori'),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lembaga_id')
                    ->relationship('lembaga', 'nama')
                    ->label('Lembaga'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->headerActions([

                Tables\Actions\Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\KantinProdukExport,
                        'kantin-produk.xlsx'
                    )),

                Tables\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->modalSubmitActionLabel('Upload')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->form([

                        Forms\Components\Placeholder::make('download_template')
                            ->label('Download Template')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<a href="' . route('kantin-produk.template') . '" target="_blank" style="color:#16a34a;font-weight:bold;">
                                    ⬇️ Download Template Excel
                                </a>'
                            )),

                        Forms\Components\FileUpload::make('file')
                            ->disk('public')
                            ->directory('imports')
                            ->required(),

                    ])
                    ->action(function (array $data) {

                        $path = storage_path('app/public/' . $data['file']);

                        \Maatwebsite\Excel\Facades\Excel::import(
                            new \App\Imports\KantinProdukImport,
                            $path
                        );
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKantinProduks::route('/'),
            'create' => Pages\CreateKantinProduk::route('/create'),
            'edit' => Pages\EditKantinProduk::route('/{record}/edit'),
        ];
    }
}
