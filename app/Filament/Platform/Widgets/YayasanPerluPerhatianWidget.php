<?php

namespace App\Filament\Platform\Widgets;

use App\Models\Yayasan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Yayasan yang butuh tindak lanjut platform admin: sudah di-suspend
 * (nunggak/trial habis tanpa lanjut), atau trial-nya akan habis dalam
 * 7 hari ke depan (kesempatan follow-up sebelum otomatis di-suspend
 * oleh command subscription:check-expired).
 */
class YayasanPerluPerhatianWidget extends BaseWidget
{
    protected static ?string $heading = 'Yayasan Perlu Perhatian';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Yayasan::withoutGlobalScopes()
                    ->where(function ($q) {
                        $q->where('status', 'suspended')
                            ->orWhere(function ($q2) {
                                $q2->where('status', 'trial')
                                    ->whereNotNull('trial_ends_at')
                                    ->where('trial_ends_at', '<=', now()->addDays(7))
                                    ->where('trial_ends_at', '>', now());
                            });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Yayasan'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'suspended' ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial Berakhir')
                    ->date('d M Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Kontak')
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\Action::make('masuk')
                    ->label('Masuk sebagai Yayasan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Yayasan $record) => rtrim(config('app.url'), '/') . '/admin/' . $record->slug)
                    ->openUrlInNewTab(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Semua aman')
            ->emptyStateDescription('Tidak ada Yayasan suspended atau trial yang segera habis.');
    }
}
