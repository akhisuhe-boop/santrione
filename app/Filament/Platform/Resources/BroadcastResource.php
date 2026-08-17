<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\BroadcastResource\Pages;
use App\Models\Broadcast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class BroadcastResource extends BaseResource
{
    protected static ?string $model = Broadcast::class;
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Broadcast Terjadwal';
    protected static ?string $modelLabel = 'Broadcast Terjadwal';
    protected static ?string $pluralModelLabel = 'Broadcast Terjadwal';
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

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
        // Broadcast yang sudah terkirim tidak boleh diedit lagi -- cuma
        // yang masih terjadwal.
        return (bool) auth()->user()?->is_platform_admin && $record?->status === 'terjadwal';
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Isi Broadcast')
                ->schema([
                    Forms\Components\TextInput::make('judul')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Cuma buat identifikasi internal, tidak ikut terkirim.'),
                    Forms\Components\Textarea::make('pesan')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull()
                        ->helperText('Ini isi pesan WA yang benar-benar terkirim ke penerima.'),
                ])->columns(1),

            Forms\Components\Section::make('Target Penerima')
                ->schema([
                    Forms\Components\CheckboxList::make('target_types')
                        ->label('Kirim ke')
                        ->options(Broadcast::TARGET_TYPES)
                        ->required()
                        ->columns(2)
                        ->helperText('Boleh pilih lebih dari satu -- misal "Yayasan (Trial)" + "Semua Lead" sekaligus dalam 1 broadcast yang sama.'),
                ]),

            Forms\Components\Section::make('Jadwal Pengiriman')
                ->schema([
                    Forms\Components\DateTimePicker::make('jadwal_kirim')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->helperText('Biarkan waktu sekarang untuk kirim secepatnya (diproses otomatis dalam beberapa menit), atau pilih tanggal/jam di masa depan untuk dijadwalkan.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('target_types')
                    ->label('Target')
                    ->formatStateUsing(fn (Broadcast $record) => $record->targetLabels())
                    ->wrap(),
                Tables\Columns\TextColumn::make('jadwal_kirim')->label('Jadwal')->dateTime('d M Y, H:i')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Broadcast::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'terjadwal' => 'warning',
                        'terkirim' => 'success',
                        'gagal' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('jumlah_terkirim')->label('Terkirim ke'),
                Tables\Columns\TextColumn::make('dikirim_pada')->label('Dikirim Pada')->dateTime('d M Y, H:i')->placeholder('-'),
            ])
            ->defaultSort('jadwal_kirim', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Broadcast::STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('kirim_sekarang')
                    ->label('Kirim Sekarang')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Broadcast $record) => $record->status === 'terjadwal')
                    ->requiresConfirmation()
                    ->action(function (Broadcast $record) {
                        \Illuminate\Support\Facades\Artisan::call('crm:kirim-broadcast-terjadwal', [
                            '--broadcast_id' => $record->id,
                        ]);

                        Notification::make()
                            ->title('Broadcast diproses, cek status beberapa saat lagi.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'edit' => Pages\EditBroadcast::route('/{record}/edit'),
        ];
    }
}
