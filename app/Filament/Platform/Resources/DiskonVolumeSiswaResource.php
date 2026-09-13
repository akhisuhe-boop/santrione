<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\DiskonVolumeSiswaResource\Pages;
use App\Models\DiskonVolumeSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- kelola tier diskon volume berdasar TOTAL SISWA
 * se-Yayasan (pengganti diskon bertingkat per-urutan-Lembaga yang
 * lama). Lihat App\Services\TenantBillingCalculator untuk cara
 * pakainya, dan App\Models\DiskonVolumeSiswa::persenUntuk().
 */
class DiskonVolumeSiswaResource extends BaseResource
{
    protected static ?string $model = DiskonVolumeSiswa::class;
    protected static ?string $navigationLabel = 'Diskon Volume Siswa';
    protected static ?string $navigationGroup = 'Billing & Harga';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $modelLabel = 'Tier Diskon Volume';
    protected static ?string $pluralModelLabel = 'Diskon Volume Siswa';

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
            Forms\Components\Section::make('Tier Diskon')
                ->schema([
                    Forms\Components\TextInput::make('siswa_min')
                        ->label('Total Siswa Minimal')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    Forms\Components\TextInput::make('siswa_max')
                        ->label('Total Siswa Maksimal')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Kosongkan kalau tier ini "tidak terbatas ke atas" (tier paling tinggi).'),

                    Forms\Components\TextInput::make('diskon_persen')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),

                    Forms\Components\TextInput::make('urutan')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa_min')
                    ->label('Dari Siswa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('siswa_max')
                    ->label('Sampai Siswa')
                    ->formatStateUsing(fn ($state) => $state === null ? 'Tidak terbatas' : $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('diskon_persen')
                    ->label('Diskon')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('siswa_min');
    }

    /**
     * DITAMBAHKAN -- cegah tier baru/diedit bikin celah atau tumpang
     * tindih dengan tier lain (mis. 1-100 lalu lompat ke 150-300,
     * siswa ke-120 jadi tidak dapat diskon apa pun -- atau 1-100 dan
     * 50-200 tumpang tindih, ambigu diskon mana yang berlaku).
     */
    public static function validasiTidakBentrok(?int $idSaatIni, int $min, ?int $max): ?string
    {
        $lainnya = DiskonVolumeSiswa::query()
            ->when($idSaatIni, fn ($q, $id) => $q->where('id', '!=', $id))
            ->orderBy('siswa_min')
            ->get();

        foreach ($lainnya as $tier) {
            $tumpangTindih = $min <= ($tier->siswa_max ?? PHP_INT_MAX) && ($max ?? PHP_INT_MAX) >= $tier->siswa_min;

            if ($tumpangTindih) {
                return "Rentang ini tumpang tindih dengan tier siswa {$tier->siswa_min}-".($tier->siswa_max ?? '∞').' ('.$tier->diskon_persen.'%).';
            }
        }

        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiskonVolumeSiswas::route('/'),
            'create' => Pages\CreateDiskonVolumeSiswa::route('/create'),
            'edit' => Pages\EditDiskonVolumeSiswa::route('/{record}/edit'),
        ];
    }
}
