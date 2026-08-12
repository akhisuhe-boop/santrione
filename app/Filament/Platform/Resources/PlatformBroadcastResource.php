<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\PlatformBroadcastResource\Pages;
use App\Models\PlatformBroadcast;
use App\Models\Yayasan;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Broadcast pesan WA ke Yayasan (update info, pengumuman produk/
 * layanan Qinara) -- TERPISAH dari notifikasi tagihan otomatis
 * bulanan (lihat NotificationService::sendTagihanSubscription).
 *
 * Kirim langsung terjadi saat record dibuat (lihat
 * Pages\CreatePlatformBroadcast::handleRecordCreation) -- resource
 * ini sengaja tidak punya halaman Edit, karena broadcast yang sudah
 * terkirim adalah riwayat, bukan sesuatu yang diubah-ubah lagi.
 */
class PlatformBroadcastResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = PlatformBroadcast::class;
    protected static ?string $navigationGroup = 'Komunikasi';
    protected static ?string $navigationLabel = 'Notifikasi / Broadcast';
    protected static ?string $modelLabel = 'Broadcast';
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Isi Broadcast')
                ->schema([
                    Forms\Components\TextInput::make('judul')
                        ->label('Judul')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\Textarea::make('pesan')
                        ->label('Isi Pesan')
                        ->required()
                        ->rows(6)
                        ->helperText('Judul akan otomatis ditebalkan dan ditambah salam penutup "— Tim Qinara Apps" saat dikirim.'),
                ]),

            Forms\Components\Section::make('Target Penerima')
                ->schema([
                    Forms\Components\Select::make('target_tipe')
                        ->label('Kirim ke')
                        ->options([
                            'semua' => 'Semua Yayasan',
                            'status' => 'Berdasarkan Status',
                            'manual' => 'Pilih Manual',
                        ])
                        ->default('semua')
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('target_status')
                        ->label('Status Yayasan')
                        ->multiple()
                        ->options([
                            'trial' => 'Trial',
                            'active' => 'Active',
                            'suspended' => 'Suspended',
                            'cancelled' => 'Cancelled',
                        ])
                        ->visible(fn (Forms\Get $get) => $get('target_tipe') === 'status'),

                    Forms\Components\Select::make('target_yayasan_ids')
                        ->label('Pilih Yayasan')
                        ->multiple()
                        ->options(fn () => Yayasan::withoutGlobalScopes()->pluck('nama', 'id'))
                        ->searchable()
                        ->visible(fn (Forms\Get $get) => $get('target_tipe') === 'manual'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(PlatformBroadcast::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'terkirim' => 'success',
                        'gagal_sebagian' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('jumlah_penerima')
                    ->label('Target'),

                Tables\Columns\TextColumn::make('jumlah_berhasil')
                    ->label('Terkirim'),

                Tables\Columns\TextColumn::make('pengirim.name')
                    ->label('Dikirim oleh')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('dikirim_pada')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Riwayat Terpilih')
                    ->modalDescription('Ini cuma menghapus RIWAYAT broadcast (log), TIDAK menarik kembali pesan WA yang sudah terkirim ke tenant.'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformBroadcasts::route('/'),
            'create' => Pages\CreatePlatformBroadcast::route('/create'),
        ];
    }
}
