<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LembagaNotificationTemplateResource\Pages;
use App\Models\Lembaga;
use App\Models\LembagaNotificationTemplate;
use App\Support\NotificationType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Redaksi notifikasi KUSTOM per Lembaga -- kalau tidak ada baris di
 * sini untuk suatu jenis, sistem pakai redaksi default (hardcode di
 * NotificationService). Sekolah cukup bikin baris di sini untuk jenis
 * yang MAU diubah redaksinya saja, tidak perlu isi semua 22 jenis.
 */
class LembagaNotificationTemplateResource extends BaseResource
{
    protected static ?string $model = LembagaNotificationTemplate::class;
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?string $navigationLabel = 'Template Notifikasi Sekolah';
    protected static ?string $modelLabel = 'Template Notifikasi';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Target Redaksi')
                ->schema([
                    Forms\Components\Select::make('lembaga_id')
                        ->label('Lembaga')
                        ->options(fn () => Lembaga::pluck('nama', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('key')
                        ->label('Jenis Notifikasi')
                        ->options(fn () => collect(NotificationType::all())->mapWithKeys(
                            fn ($item, $key) => [$key => $item['nama'] . ' (' . $item['kategori'] . ')']
                        ))
                        ->required()
                        ->searchable()
                        ->live(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Isi Pesan')
                ->schema([
                    Forms\Components\Placeholder::make('placeholder_tersedia')
                        ->label('Placeholder yang tersedia untuk jenis ini')
                        ->content(function (Forms\Get $get) {
                            $key = $get('key');

                            if (blank($key)) {
                                return 'Pilih "Jenis Notifikasi" dulu di atas untuk lihat placeholder yang tersedia.';
                            }

                            return NotificationType::all()[$key]['placeholder'] ?? '—';
                        }),

                    Forms\Components\Textarea::make('template')
                        ->label('Redaksi Kustom')
                        ->required()
                        ->rows(8)
                        ->helperText('Salin persis nama placeholder di atas (termasuk kurung kurawal), sisanya bebas ditulis sesuai gaya sekolah.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Jenis Notifikasi')
                    ->formatStateUsing(fn (string $state) => NotificationType::all()[$state]['nama'] ?? $state)
                    ->badge(),

                Tables\Columns\TextColumn::make('template')
                    ->limit(60)
                    ->wrap(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLembagaNotificationTemplates::route('/'),
            'create' => Pages\CreateLembagaNotificationTemplate::route('/create'),
            'edit' => Pages\EditLembagaNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
