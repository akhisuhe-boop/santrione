<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\RekeningUtamaLembagaResource\Pages;
use App\Models\Lembaga;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- halaman pemetaan terpusat untuk rekening UTAMA/default
 * tiap Lembaga (bukan kategori tambahan -- itu di
 * LembagaRekeningResource). Sebelumnya cuma bisa dihubungkan satu-satu
 * lewat tombol "Hubungkan Rekening Utama" di halaman Edit Lembaga
 * (panel admin/tenant, harus buka satu per satu). Resource ini
 * menampilkan SEMUA Lembaga lintas Yayasan sekaligus dalam satu tabel,
 * supaya gampang audit mana yang belum dihubungkan.
 *
 * Read-only + 1 action (tidak ada create/delete/edit-page tersendiri --
 * data Lembaga itu sendiri tetap dikelola dari panel admin/tenant
 * seperti biasa, resource ini murni untuk urusan pemetaan rekening
 * Disbursement).
 */
class RekeningUtamaLembagaResource extends BaseResource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationLabel = 'Rekening Utama Lembaga';
    protected static ?string $navigationGroup = 'Disbursement';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $modelLabel = 'Rekening Utama Lembaga';
    protected static ?string $pluralModelLabel = 'Rekening Utama Lembaga';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

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

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('yayasan.nama')
                    ->label('Yayasan')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('doku_sub_account_id')
                    ->label('Terdaftar di DOKU')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->doku_sub_account_id),

                Tables\Columns\TextColumn::make('rekeningTujuan.nama')
                    ->label('Rekening Bank Tujuan')
                    ->placeholder('Belum dihubungkan')
                    ->color(fn ($state) => $state ? null : 'danger')
                    ->description(fn ($record) => $record->rekeningTujuan
                        ? trim(($record->rekeningTujuan->bank ?? '').' '.($record->rekeningTujuan->no_rekening ?? ''))
                        : null),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('rekening_id')
                    ->label('Status Rekening Tujuan')
                    ->nullable()
                    ->trueLabel('Sudah dihubungkan')
                    ->falseLabel('Belum dihubungkan')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('rekening_id'),
                        false: fn ($query) => $query->whereNull('rekening_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('hubungkanRekening')
                    ->label(fn ($record) => $record->rekening_id ? 'Ubah Rekening Tujuan' : 'Hubungkan ke Rekening Bank')
                    ->icon('heroicon-o-link')
                    ->color(fn ($record) => $record->rekening_id ? 'gray' : 'warning')
                    ->disabled(fn ($record) => ! $record->doku_sub_account_id)
                    ->tooltip(fn ($record) => ! $record->doku_sub_account_id ? 'Lembaga ini belum terdaftar di DOKU' : null)
                    ->form([
                        Forms\Components\Select::make('rekening_id')
                            ->label('Rekening Bank Asli Tujuan')
                            ->helperText('Rekening bank sungguhan yang jadi tujuan pencairan untuk rekening DOKU utama/default Lembaga ini.')
                            ->options(fn ($record) => Rekening::where('lembaga_id', $record->id)
                                ->where('tipe', 'bank')
                                ->get()
                                ->mapWithKeys(fn ($r) => [$r->id => trim("{$r->nama} — {$r->bank} {$r->no_rekening}")]))
                            ->searchable()
                            ->required(),
                    ])
                    ->fillForm(fn ($record) => ['rekening_id' => $record->rekening_id])
                    ->action(function ($record, array $data) {
                        $record->update(['rekening_id' => $data['rekening_id']]);

                        \Filament\Notifications\Notification::make()
                            ->title('Rekening tujuan berhasil dihubungkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekeningUtamaLembagas::route('/'),
        ];
    }
}
