<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanHonorPenggantiResource\Pages;
use App\Models\Lembaga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PengaturanHonorPenggantiResource extends BaseResource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Honor Guru Pengganti';
    protected static ?string $modelLabel = 'Honor Guru Pengganti';
    protected static ?string $pluralModelLabel = 'Honor Guru Pengganti';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 4;

    // Lembaga sudah dikelola di resource Lembaga sendiri — di sini
    // cuma untuk atur 1 nilai tarifnya, bukan bikin/hapus lembaga.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Lembaga')
                    ->schema([

                        Forms\Components\Placeholder::make('nama')
                            ->label('Nama Lembaga')
                            ->content(fn ($record) => $record?->nama ?? '-'),

                    ]),

                Forms\Components\Section::make('Honor Guru Pengganti')
                    ->description('Tarif ini berlaku otomatis untuk SEMUA guru pengganti di lembaga ini — tidak perlu diisi manual lagi setiap kali guru mengisi jurnal mengajar pengganti.')
                    ->schema([

                        Forms\Components\TextInput::make('tarif_pengganti_per_jp')
                            ->label('Tarif per JP (Guru Pengganti)')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->helperText('Kosongkan kalau ingin guru pengganti di lembaga ini tetap dibayar memakai tarif per JP miliknya sendiri.'),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tarif_pengganti_per_jp')
                    ->label('Tarif per JP (Guru Pengganti)')
                    ->formatStateUsing(fn ($state) => $state
                        ? 'Rp ' . number_format($state, 0, ',', '.')
                        : 'Belum diatur (pakai tarif guru sendiri)'
                    ),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanHonorPenggantis::route('/'),
            'edit' => Pages\EditPengaturanHonorPengganti::route('/{record}/edit'),
        ];
    }
}
