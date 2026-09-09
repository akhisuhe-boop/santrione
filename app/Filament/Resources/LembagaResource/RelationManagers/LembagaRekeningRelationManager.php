<?php

namespace App\Filament\Resources\LembagaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Menampilkan (read-only, murni untuk visibilitas) rekening DOKU
 * kategori khusus yang sudah didaftarkan untuk Lembaga ini -- lihat
 * Lembaga::rekeningUntuk() untuk logika pemilihannya, dan action
 * "+ Daftarkan Rekening Kategori Lain" di EditLembaga untuk cara
 * menambahnya. Rekening "default"/utama Lembaga TIDAK muncul di sini
 * (itu tersimpan langsung di kolom doku_* tabel `lembagas`, dikelola
 * lewat tombol "Daftarkan ke DOKU").
 */
class LembagaRekeningRelationManager extends RelationManager
{
    protected static string $relationship = 'rekenings';

    protected static ?string $title = 'Rekening Kategori Khusus';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kategori')
            ->columns([
                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Label')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('doku_sub_account_id')
                    ->label('Profile ID DOKU')
                    ->copyable(),

                Tables\Columns\TextColumn::make('doku_account_no')
                    ->label('Account No'),

                Tables\Columns\IconColumn::make('split_siap')
                    ->label('Split Rule')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) ($record->doku_split_rule_id && $record->doku_split_rule_id_flat)),

                Tables\Columns\TextColumn::make('doku_status')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('rekeningTujuan.nama')
                    ->label('Rekening Bank Tujuan')
                    ->placeholder('Belum dihubungkan')
                    ->color(fn ($state) => $state ? null : 'danger')
                    ->description(fn ($record) => $record->rekeningTujuan
                        ? trim(($record->rekeningTujuan->bank ?? '').' '.($record->rekeningTujuan->no_rekening ?? ''))
                        : 'Uang kategori ini belum ada tujuan pencairan yang jelas'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('hubungkanRekening')
                    ->label(fn ($record) => $record->rekening_id ? 'Ubah Rekening Tujuan' : 'Hubungkan ke Rekening Bank')
                    ->icon('heroicon-o-link')
                    ->color(fn ($record) => $record->rekening_id ? 'gray' : 'warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('rekening_id')
                            ->label('Rekening Bank Asli Tujuan')
                            ->helperText('Pilih rekening bank sungguhan (dari menu Keuangan > Input Rekening) yang jadi tujuan pencairan untuk kategori DOKU ini. Ini murni penanda referensi -- belum mencairkan uang apa pun, cuma persiapan untuk fitur pencairan yang akan dibangun.')
                            ->options(fn ($record) => \App\Models\Rekening::where('lembaga_id', $record->lembaga_id)
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

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Menghapus baris ini HANYA menghapus catatan di sistem kita -- sub-account & split rule di DOKU TIDAK ikut terhapus. Tagihan dengan kategori ini otomatis fallback ke rekening utama Lembaga setelah dihapus.'),
            ])
            ->emptyStateHeading('Belum ada rekening kategori khusus')
            ->emptyStateDescription('Semua tagihan Lembaga ini masuk ke rekening utama. Klik "+ Daftarkan Rekening Kategori Lain" di atas untuk memisahkan kategori tertentu (mis. PPDB).');
    }
}
