<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollAdjustmentTemplateResource\Pages;
use App\Models\PayrollAdjustmentTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollAdjustmentTemplateResource extends BaseResource
{
    protected static ?string $model = PayrollAdjustmentTemplate::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Tunjangan/Potongan Tetap';
    protected static ?string $modelLabel = 'Tunjangan/Potongan Tetap';
    protected static ?string $pluralModelLabel = 'Tunjangan/Potongan Tetap';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?int $navigationSort = 5;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Tunjangan / Potongan Tetap')
                    ->description('Diisi 1x, otomatis ditambahkan ke payroll pegawai ini SETIAP BULAN — tidak perlu diinput ulang. Cocok untuk komponen yang nilainya tidak berubah-ubah, mis. Tunjangan Wali Kelas atau Tunjangan Pembina Eskul. Kalau nilainya berubah tiap bulan (bonus/potongan insidental), input langsung di halaman Payroll pegawai terkait, bukan di sini.')
                    ->schema([

                        Forms\Components\Select::make('pegawai_id')
                            ->label('Pegawai')
                            ->relationship('pegawai', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('tipe')
                            ->label('Tipe')
                            ->options([
                                'tambahan' => 'Tambahan (Tunjangan)',
                                'potongan' => 'Potongan',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('nama_komponen')
                            ->label('Nama Komponen')
                            ->placeholder('Contoh: Tunjangan Wali Kelas')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal per Bulan')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Matikan kalau tunjangan/potongan ini sudah tidak berlaku lagi, tanpa harus menghapus riwayatnya.'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'success' => 'tambahan',
                        'danger' => 'potongan',
                    ]),

                Tables\Columns\TextColumn::make('nama_komponen')
                    ->label('Komponen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal / Bulan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'tambahan' => 'Tambahan (Tunjangan)',
                        'potongan' => 'Potongan',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

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
            'index' => Pages\ListPayrollAdjustmentTemplates::route('/'),
            'create' => Pages\CreatePayrollAdjustmentTemplate::route('/create'),
            'edit' => Pages\EditPayrollAdjustmentTemplate::route('/{record}/edit'),
        ];
    }
}
