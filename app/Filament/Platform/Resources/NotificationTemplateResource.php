<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Template pesan WA otomatis platform -- editable dari panel,
 * gantikan string hardcode di NotificationService. Cuma List + Edit
 * (TIDAK ada Create/Delete) karena key template itu FIXED, dipakai
 * langsung oleh kode (NotificationTemplate::render('tagihan_subscription', ...)
 * dst) -- kalau tenant/admin bisa hapus/bikin sembarang key baru,
 * template yang dipakai kode nyata bisa hilang.
 */
class NotificationTemplateResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static ?string $navigationGroup = 'Komunikasi';
    protected static ?string $navigationLabel = 'Template Notifikasi';
    protected static ?string $modelLabel = 'Template Notifikasi';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 30;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

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
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->required()
                ->maxLength(150),

            Forms\Components\TextInput::make('key')
                ->label('Key (jangan diubah)')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Textarea::make('template')
                ->label('Isi Pesan')
                ->required()
                ->rows(10)
                ->helperText('Pakai {nama_placeholder} untuk bagian yang diganti otomatis — lihat daftar placeholder yang tersedia di bawah.'),

            Forms\Components\Placeholder::make('keterangan_placeholder')
                ->label('Placeholder yang tersedia')
                ->content(fn ($record) => $record?->keterangan_placeholder ?? '—'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('template')
                    ->limit(60)
                    ->wrap(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
