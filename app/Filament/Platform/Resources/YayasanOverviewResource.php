<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\YayasanOverviewResource\Pages;
use App\Models\Yayasan;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Daftar SEMUA Yayasan lintas-platform, read-only (create/edit/delete
 * Yayasan tetap lewat panel Yayasan/route pendaftaran yang sudah ada
 * — resource ini murni untuk MEMANTAU + pintu masuk cepat ke panel
 * Yayasan mana pun untuk keperluan debug/bantu maintenance data,
 * tanpa perlu hafal/tebak URL tenant satu-satu.
 */
class YayasanOverviewResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Yayasan::class;
    protected static ?string $navigationLabel = 'Daftar Yayasan';
    protected static ?string $modelLabel = 'Yayasan';
    protected static ?string $pluralModelLabel = 'Yayasan';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = -10;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Yayasan::withoutGlobalScopes()->withCount(['lembagas']))
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->label('Yayasan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'trial' => 'info',
                        'suspended' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('lembagas_count')
                    ->label('Jml. Lembaga')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial Berakhir')
                    ->date('d M Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('estimasi_tagihan')
                    ->label('Estimasi Tagihan/Bulan')
                    ->state(function (Yayasan $record) {
                        $hasil = app(\App\Services\TenantBillingCalculator::class)->hitungYayasan($record);

                        return 'Rp ' . number_format($hasil['total'], 0, ',', '.');
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('masukSebagaiYayasan')
                    ->label('Masuk sebagai Yayasan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Yayasan $record) => rtrim(config('app.url'), '/') . '/admin/' . $record->slug)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYayasanOverviews::route('/'),
        ];
    }
}
