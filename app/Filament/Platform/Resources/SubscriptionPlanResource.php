<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends BaseResource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationGroup = 'Billing & Harga';
    protected static ?string $navigationLabel = 'Paket Langganan';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Paket Langganan';
    protected static ?string $pluralModelLabel = 'Paket Langganan';
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    // Harga & paket adalah keputusan level platform, bukan per yayasan —
    // cuma Platform Admin yang boleh kelola ini.
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canEdit($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Paket')
                    ->schema([

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('harga_bulanan')
                            ->label('Harga per Bulan')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\TextInput::make('harga_per_siswa_tambahan')
                            ->label('Harga per Siswa Tambahan')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->helperText('Dikenakan untuk siswa di atas Maks. Siswa. Kosongkan kalau paket ini flat (tanpa biaya tambahan siswa).'),

                        Forms\Components\TextInput::make('harga_per_lembaga_tambahan')
                            ->label('Harga per Lembaga Tambahan')
                            ->numeric()
                            ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                            ->stripCharacters('.')
                            ->prefix('Rp')
                            ->helperText('Dikenakan untuk lembaga di atas Maks. Lembaga.'),

                        Forms\Components\TextInput::make('maks_lembaga')
                            ->label('Maks. Lembaga')
                            ->numeric()
                            ->helperText('Kosongkan = tidak dibatasi'),

                        Forms\Components\TextInput::make('maks_siswa')
                            ->label('Maks. Siswa')
                            ->numeric()
                            ->helperText('Kosongkan = tidak dibatasi'),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif (tampil ke calon customer)')
                            ->default(true),

                        Forms\Components\Toggle::make('termasuk_semua_modul')
                            ->label('Paket Full (semua modul termasuk)')
                            ->helperText('Kalau AKTIF: modul yang diaktifkan di Lembaga manapun yang pakai paket ini TIDAK ditagih terpisah (sudah termasuk harga di atas) — jangan aktifkan untuk paket biasa seperti Akses Platform.')
                            ->default(false)
                            ->columnSpanFull(),

                        Forms\Components\CheckboxList::make('fitur')
                            ->label('Fitur Premium yang Dibuka')
                            ->options(\App\Support\FeatureGate::all())
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('Fitur yang TIDAK dicentang akan terkunci untuk yayasan yang pakai paket ini (menu disembunyikan + muncul ajakan upgrade).'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
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

                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Paket')
                    ->searchable(),

                Tables\Columns\TextColumn::make('harga_bulanan')
                    ->label('Harga / Bulan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('harga_per_siswa_tambahan')
                    ->label('+Siswa')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),

                Tables\Columns\TextColumn::make('harga_per_lembaga_tambahan')
                    ->label('+Lembaga')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),

                Tables\Columns\TextColumn::make('maks_lembaga')
                    ->label('Maks. Lembaga')
                    ->formatStateUsing(fn ($state) => $state ?? 'Tidak dibatasi'),

                Tables\Columns\TextColumn::make('maks_siswa')
                    ->label('Maks. Siswa')
                    ->formatStateUsing(fn ($state) => $state ?? 'Tidak dibatasi'),

                Tables\Columns\IconColumn::make('termasuk_semua_modul')
                    ->label('Full')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('Yayasan Pakai')
                    ->counts('subscriptions'),

            ])
            ->defaultSort('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
