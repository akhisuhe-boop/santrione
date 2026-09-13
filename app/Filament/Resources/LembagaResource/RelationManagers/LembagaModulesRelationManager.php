<?php

namespace App\Filament\Resources\LembagaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * DIUBAH (12 Sep 2026, skema harga per-Yayasan) -- tab ini SEKARANG
 * READ-ONLY. Sebelumnya di sini bisa toggle modul PER-LEMBAGA satu-
 * satu + preview estimasi tagihan per-Lembaga (hitungLembaga()).
 *
 * Kenapa dihapus: skema baru mengaktifkan modul di level YAYASAN
 * (satu toggle di halaman Checkout otomatis berlaku ke SEMUA Lembaga
 * se-Yayasan, lihat Checkout::toggleModule()). Membiarkan tab ini
 * tetap bisa toggle per-Lembaga akan membuat data Lembaga-Lembaga
 * dalam satu Yayasan bisa BERBEDA modul aktifnya lagi -- bertentangan
 * dengan asumsi TenantBillingCalculator yang sekarang cuma mengecek
 * Lembaga PERTAMA milik Yayasan (mengasumsikan semua Lembaga selalu
 * tersinkron). hitungLembaga() sendiri juga sudah dihapus dari
 * TenantBillingCalculator (diganti hitungYayasan()), jadi tombol
 * "Lihat Estimasi" lama akan error kalau dibiarkan.
 *
 * Tabel & relasi `lembaga_modules` TIDAK diubah strukturnya -- tab
 * ini cuma tampilan, kelola modul yang sebenarnya lewat halaman
 * Checkout Langganan Yayasan.
 */
class LembagaModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Modul Aktif';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('modulePrice')->where('is_active', true))
            ->columns([

                Tables\Columns\TextColumn::make('modulePrice.nama')
                    ->label('Modul'),

                Tables\Columns\TextColumn::make('modulePrice.harga_per_siswa')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state, $record) => $record->modulePrice->is_gratis
                        ? 'GRATIS (fee dari wali murid)'
                        : 'Rp ' . number_format($state, 0, ',', '.') . '/siswa/bulan'),

                Tables\Columns\TextColumn::make('modulePrice.dibebankan_ke')
                    ->label('Dibebankan ke')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'wali_murid' ? 'Wali Murid' : 'Sekolah')
                    ->color(fn ($state) => $state === 'wali_murid' ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('aktif_sejak')
                    ->label('Aktif Sejak')
                    ->date('d M Y'),

            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada modul aktif')
            ->emptyStateDescription('Kelola modul untuk seluruh Yayasan lewat halaman Checkout Langganan (menu Langganan → Bayar / Kelola Langganan).');
    }
}
