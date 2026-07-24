<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanGajiResource\Pages;
use App\Filament\Resources\PengaturanGajiResource\RelationManagers;
use App\Models\PegawaiLembaga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class PengaturanGajiResource extends BaseResource
{
    protected static ?string $model = PegawaiLembaga::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Pengaturan Gaji';
    protected static ?string $modelLabel = 'Pengaturan Gaji';
    protected static ?string $pluralModelLabel = 'Pengaturan Gaji';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 3;


    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            /*
            |--------------------------------------------------------------------------
            | INFORMASI PEGAWAI
            |--------------------------------------------------------------------------
            */
            Forms\Components\Section::make('Informasi Pegawai')
            ->schema([
                Forms\Components\Placeholder::make('pegawai')
                    ->label(' ')
                    ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                        <div>
                            <div class="text-sm text-gray-500">
                                Pegawai
                            </div>
                            <div class="text-lg font-semibold">
                                ' . ($record?->pegawai?->nama ?? '-') . '
                            </div>
                        </div>
                    ')),

                Forms\Components\Placeholder::make('lembaga')
                    ->label(' ')
                    ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                        <div>
                            <div class="text-sm text-gray-500">
                                Lembaga
                            </div>
                            <div class="text-lg font-semibold">
                                ' . ($record?->lembaga?->nama ?? '-') . '
                            </div>
                        </div>
                    ')),

                Forms\Components\Placeholder::make('jabatan')
                    ->label(' ')
                    ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                        <div>
                            <div class="text-sm text-gray-500">
                                Jabatan
                            </div>
                            <div class="text-lg font-semibold">
                                ' . ($record?->jabatan ?? '-') . '
                            </div>
                        </div>
                    ')),

                Forms\Components\Placeholder::make('status')
                    ->label(' ')
                    ->content(fn ($record) => new \Illuminate\Support\HtmlString('
                        <div>
                            <div class="text-sm text-gray-500">
                                Status
                            </div>
                            <div class="text-lg font-semibold">
                                ' . ucfirst($record?->status ?? '-') . '
                            </div>
                        </div>
                    ')),
            ])
            ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | PENGATURAN GAJI
            |--------------------------------------------------------------------------
            */
            Forms\Components\Section::make('Pengaturan Gaji')
                ->schema([
                    Forms\Components\Select::make('metode_penggajian')
                        ->label('Metode Penggajian')
                        ->options([
                            'tetap' => 'Gaji Tetap',
                            'per_jp' => 'Honor per JP',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('nominal_tetap')
                        ->label('Gaji Tetap')
                        ->numeric()
                        ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                        ->stripCharacters('.')
                        ->prefix('Rp')
                        ->visible(fn ($get) =>
                            $get('metode_penggajian') === 'tetap'
                        ),

                    Forms\Components\TextInput::make('tarif_per_jp')
                        ->label('Honor per JP')
                        ->numeric()
                        ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                        ->stripCharacters('.')
                        ->prefix('Rp')
                        ->visible(fn ($get) =>
                            $get('metode_penggajian') === 'per_jp'
                        ),
                ])
                ->columns(2),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('pegawai.nama')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('lembaga.nama')
                ->badge()
                ->color(function ($record) {
                    return match ($record->lembaga_id) {
                        1 => 'success',
                        2 => 'warning',
                        3 => 'danger',
                        4 => 'info',
                        5 => 'primary',
                        default => 'gray',
                    };
                }),

            Tables\Columns\TextColumn::make('jabatan')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('metode_penggajian')
                ->colors([
                    'success' => 'tetap',
                    'warning' => 'per_jp',
                ]),

            Tables\Columns\TextColumn::make('nominal')
            ->label('Nominal Gaji')
            ->getStateUsing(function ($record) {
                if ($record->metode_penggajian === 'tetap') {
                    return $record->nominal_tetap;
                }
                return $record->tarif_per_jp;
            })
            ->formatStateUsing(fn ($state) =>
                'Rp ' . number_format($state, 0, ',', '.')
            ),
        ])
        ->filters([

            Tables\Filters\SelectFilter::make('metode_penggajian')
                ->options([
                    'tetap' => 'Gaji Tetap',
                    'per_jp' => 'Honor per JP',
                ]),

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPengaturanGajis::route('/'),
            'create' => Pages\CreatePengaturanGaji::route('/create'),
            'edit' => Pages\EditPengaturanGaji::route('/{record}/edit'),
        ];
    }
}
