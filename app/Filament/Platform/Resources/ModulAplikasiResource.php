<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\ModulAplikasiResource\Pages;
use App\Models\ModulAplikasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ModulAplikasiResource extends BaseResource
{
    protected static ?string $model = ModulAplikasi::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Modul Aplikasi';
    protected static ?string $modelLabel = 'Modul Aplikasi';
    protected static ?string $pluralModelLabel = 'Modul Aplikasi';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

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
        return $form->schema([
            Forms\Components\Section::make('Kartu Modul')
                ->description('Kartu fitur di section "Infrastruktur Digital Terlengkap".')
                ->schema([
                    Forms\Components\TextInput::make('judul')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Keuangan Lembaga'),
                    Forms\Components\TextInput::make('icon')
                        ->label('Nama Icon (lucide.dev)')
                        ->required()
                        ->default('layout-dashboard')
                        ->helperText('Cari nama icon di lucide.dev.'),
                    Forms\Components\Textarea::make('deskripsi')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('tag_text')
                        ->label('Teks Tag (bawah kartu)')
                        ->maxLength(255)
                        ->placeholder('Contoh: Laporan Keuangan Otomatis'),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Kartu Besar (Full-Width)')
                        ->helperText('Aktifkan untuk 1 kartu yang mau ditonjolkan selebar penuh, seperti dulu kartu "Absensi Digital Real-time".')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('judul')->searchable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Full-Width'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModulAplikasis::route('/'),
            'create' => Pages\CreateModulAplikasi::route('/create'),
            'edit' => Pages\EditModulAplikasi::route('/{record}/edit'),
        ];
    }
}
