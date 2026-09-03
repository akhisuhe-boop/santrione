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
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Produk Kantin')
                    ->description('Data dasar produk yang dijual di kantin.')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([

                        Forms\Components\Select::make('kantin_id')
                            ->label('Kantin')
                            ->relationship('kantin', 'nama', fn ($query) => $query->where(
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

                Tables\Columns\TextColumn::make('kantin.nama')
                    ->label('Kantin'),

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
                Tables\Filters\SelectFilter::make('kantin_id')
                    ->relationship('kantin', 'nama')
                    ->label('Kantin'),
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

                // Upload beberapa foto produk sekaligus, dicocokkan
                // otomatis lewat nama file = Barcode -- pola yang sama
                // persis dengan "Upload Foto Massal" di data Siswa/
                // Pegawai, supaya konsisten buat admin sekolah yang
                // sudah terbiasa pakai fitur itu.
                Tables\Actions\Action::make('uploadFotoMassal')
                    ->label('Upload Foto Massal')
                    ->modalSubmitActionLabel('Upload & Pasangkan')
                    ->color('info')
                    ->icon('heroicon-o-photo')
                    ->form([

                        Forms\Components\Placeholder::make('petunjuk_foto_massal_produk')
                            ->label('Petunjuk')
                            ->content(new \Illuminate\Support\HtmlString(
                                'Nama tiap file foto <b>HARUS PERSIS SAMA</b> dengan Barcode produknya.<br>' .
                                'Contoh: produk dengan barcode <code>PRD-A1B2C3D4</code> → nama file harus <code>PRD-A1B2C3D4.jpg</code> (atau .png).<br>' .
                                'Bisa pilih/drag banyak file foto sekaligus di bawah ini — sistem otomatis mencocokkan ke produk yang barcode-nya sesuai, dan foto disimpan ke R2 (format WebP), sama seperti upload foto satu-satu.'
                            )),

                        Forms\Components\FileUpload::make('foto_massal_produk')
                            ->label('Pilih Foto Produk (bisa banyak sekaligus)')
                            ->multiple()
                            ->image()
                            ->maxSize(2048)
                            ->saveUploadedFileUsing(function ($file) {
                                $namaAsli = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                                $webp = \Intervention\Image\Laravel\Facades\Image::decode(file_get_contents($file->getRealPath()))
                                    ->cover(800, 800)
                                    ->encodeUsingFileExtension('webp', quality: 80);

                                $filename = 'kantin-produk/' . uniqid() . '.webp';

                                \Storage::disk('r2-public')->put($filename, (string) $webp);

                                \Illuminate\Support\Facades\Cache::put(
                                    'foto-massal-produk-barcode:' . $filename,
                                    $namaAsli,
                                    now()->addMinutes(10)
                                );

                                return $filename;
                            })
                            ->required(),

                    ])
                    ->action(function (array $data) {

                        $files = $data['foto_massal_produk'] ?? [];
                        $cocok = 0;
                        $tidakCocok = [];

                        foreach ($files as $filePath) {

                            $barcode = \Illuminate\Support\Facades\Cache::pull('foto-massal-produk-barcode:' . $filePath);

                            if (! $barcode) {
                                $tidakCocok[] = basename($filePath) . ' (info barcode hilang, coba upload ulang)';
                                continue;
                            }

                            $produk = KantinProduk::where('barcode', $barcode)->first();

                            if ($produk) {
                                $produk->update(['gambar' => $filePath]);
                                $cocok++;
                            } else {
                                $tidakCocok[] = "Barcode {$barcode} tidak ditemukan di data produk manapun";
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("Upload selesai — {$cocok} foto berhasil dipasangkan ke produk")
                            ->body(count($tidakCocok) > 0
                                ? 'Nama file berikut TIDAK cocok dengan barcode produk manapun (cek lagi penamaannya): ' . implode(', ', $tidakCocok)
                                : null)
                            ->color(count($tidakCocok) > 0 ? 'warning' : 'success')
                            ->send();
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('cetakBarcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record->barcode))
                    ->url(fn ($record) => route('kantin-produk.cetak-barcode', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('cetakBarcodeMassal')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-qr-code')
                        ->color('gray')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->join(',');

                            return redirect()->route('kantin-produk.cetak-barcode', [
                                'ids' => $ids,
                            ]);
                        }),
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
