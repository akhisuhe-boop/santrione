<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\BuktiSosialResource\Pages;
use App\Models\BuktiSosial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class BuktiSosialResource extends BaseResource
{
    protected static ?string $model = BuktiSosial::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Pop-up Social Proof';
    protected static ?string $modelLabel = 'Pop-up Social Proof';
    protected static ?string $pluralModelLabel = 'Pop-up Social Proof';
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

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
            Forms\Components\Section::make('Nama yang Ditampilkan Bergiliran')
                ->description('Muncul sebagai pop-up kecil di pojok kiri bawah landing page, bergantian, sebagai social proof. Isi manual satu-satu -- pastikan sudah dapat izin dari lembaga terkait sebelum menampilkan namanya secara publik.')
                ->schema([
                    Forms\Components\TextInput::make('nama_lembaga')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: SDIT Al-Amanah'),
                    Forms\Components\TextInput::make('lokasi')
                        ->maxLength(255)
                        ->placeholder('Contoh: Bandung (opsional)'),
                    Forms\Components\DatePicker::make('tanggal_bergabung')
                        ->label('Tanggal Bergabung (perkiraan)')
                        ->helperText('PENTING: pilih tanggal di MASA LALU (bukan hari ini) supaya muncul teks "X hari/minggu yang lalu" yang meyakinkan. Kalau diisi hari ini, teksnya akan tetap "Hari ini" -- kosongkan field ini kalau memang tidak mau menampilkan info waktu sama sekali.')
                        ->maxDate(now()),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Ikut ditampilkan')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('nama_lembaga')->searchable(),
                Tables\Columns\TextColumn::make('lokasi'),
                Tables\Columns\TextColumn::make('tanggal_bergabung')->date('d M Y'),
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
            'index' => Pages\ListBuktiSosials::route('/'),
            'create' => Pages\CreateBuktiSosial::route('/create'),
            'edit' => Pages\EditBuktiSosial::route('/{record}/edit'),
        ];
    }
}
