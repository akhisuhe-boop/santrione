<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;

use App\Filament\Platform\Resources\LembagaRekeningResource\Pages;
use App\Models\LembagaRekening;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DITAMBAHKAN -- pasangan dari RekeningUtamaLembagaResource, tapi
 * untuk rekening KATEGORI TAMBAHAN (child sub-account, mis. "ppdb",
 * "uang_gedung") -- bukan rekening utama/default Lembaga. Sebelumnya
 * cuma bisa dilihat/diatur lewat tab "Rekening Kategori Khusus" di
 * halaman Edit Lembaga (satu per satu). Resource ini menampilkan
 * SEMUA kategori tambahan lintas Lembaga & Yayasan sekaligus.
 *
 * Dikelompokkan di navigationGroup yang sama ('Pencairan Dana') dengan
 * RekeningUtamaLembagaResource supaya keduanya bersebelahan di
 * sidebar -- satu tempat terpusat untuk urusan pemetaan rekening
 * Disbursement, meski secara teknis 2 resource terpisah (rekening
 * utama & kategori tambahan punya struktur data yang beda, tidak
 * digabung paksa jadi 1 tabel supaya tetap solid & mudah dirawat).
 */
class LembagaRekeningResource extends BaseResource
{
    protected static ?string $model = LembagaRekening::class;
    protected static ?string $navigationLabel = 'Rekening Kategori Khusus';
    protected static ?string $navigationGroup = 'Pencairan Dana';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Rekening Kategori Khusus';
    protected static ?string $pluralModelLabel = 'Rekening Kategori Khusus';

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
        // Pendaftaran kategori baru tetap lewat "+ Daftarkan Rekening
        // Kategori Lain" di Edit Lembaga (perlu panggil API DOKU
        // registerRekening() dulu) -- resource ini murni untuk
        // pemetaan rekening bank yang SUDAH terdaftar.
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
                Tables\Columns\TextColumn::make('lembaga.yayasan.nama')
                    ->label('Yayasan')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lembaga.nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori DOKU')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Label')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('split_siap')
                    ->label('Split Rule')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) ($record->doku_split_rule_id && $record->doku_split_rule_id_flat)),

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
                    ->form([
                        Forms\Components\Select::make('rekening_id')
                            ->label('Rekening Bank Asli Tujuan')
                            ->helperText('Pilih rekening bank sungguhan (dari menu Keuangan > Input Rekening) yang jadi tujuan pencairan untuk kategori DOKU ini.')
                            ->options(fn ($record) => Rekening::where('lembaga_id', $record->lembaga_id)
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
            ->defaultSort('lembaga.nama')
            ->emptyStateHeading('Belum ada rekening kategori khusus')
            ->emptyStateDescription('Kategori tambahan didaftarkan lewat tombol "+ Daftarkan Rekening Kategori Lain" di halaman Edit Lembaga (panel admin).');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLembagaRekenings::route('/'),
        ];
    }
}
