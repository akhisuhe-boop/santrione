<?php

namespace App\Filament\Resources\LembagaResource\RelationManagers;

use App\Models\ModulePrice;
use App\Services\TenantBillingCalculator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Kelola modul add-on yang aktif untuk Lembaga ini (skema à la carte
 * — lihat dokumen "Skema Pembiayaan Qinara Apps" dan
 * App\Services\TenantBillingCalculator untuk rumus tagihannya).
 *
 * Menonaktifkan modul TIDAK menghapus barisnya (beda dari CheckboxList
 * ->relationship() bawaan Filament yang detach = delete) — is_active
 * diset false + nonaktif_sejak dicatat, supaya riwayat modul apa saja
 * yang pernah dipakai Lembaga ini tetap tersimpan untuk audit/laporan.
 */
class LembagaModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Modul Aktif';

    // Sama seperti Paket Langganan & Harga Modul — mengaktifkan/menonaktifkan
    // modul berpengaruh langsung ke tagihan, jadi hanya Platform Admin yang
    // boleh lihat & kelola tab ini, bukan staf sekolah biasa.
    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('module_price_id')
                    ->label('Modul')
                    ->options(
                        ModulePrice::aktif()
                            ->whereNotIn('id', $this->getOwnerRecord()->modules()->pluck('module_price_id'))
                            ->orderBy('urutan')
                            ->pluck('nama', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Cuma menampilkan modul yang belum pernah diaktifkan untuk Lembaga ini.'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('modulePrice'))
            ->columns([

                Tables\Columns\TextColumn::make('modulePrice.nama')
                    ->label('Modul'),

                Tables\Columns\TextColumn::make('modulePrice.harga_bulanan')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state, $record) => $record->modulePrice->is_gratis
                        ? 'GRATIS (fee dari wali murid)'
                        : 'Rp ' . number_format($state, 0, ',', '.') . '/bulan'),

                Tables\Columns\TextColumn::make('modulePrice.dibebankan_ke')
                    ->label('Dibebankan ke')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'wali_murid' ? 'Wali Murid' : 'Sekolah')
                    ->color(fn ($state) => $state === 'wali_murid' ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('aktif_sejak')
                    ->label('Aktif Sejak')
                    ->date('d M Y'),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Aktifkan Modul')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['is_active'] = true;
                        $data['aktif_sejak'] = now();

                        return $data;
                    }),

                Tables\Actions\Action::make('lihatEstimasi')
                    ->label('Lihat Estimasi Tagihan')
                    ->icon('heroicon-o-calculator')
                    ->color('gray')
                    ->modalHeading('Estimasi Tagihan Lembaga Ini')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function () {
                        $lembaga = $this->getOwnerRecord();
                        $hasil = app(TenantBillingCalculator::class)->hitungLembaga($lembaga->fresh());

                        return view('filament.lembaga.estimasi-tagihan', ['hasil' => $hasil]);
                    }),
            ])
            ->actions([

                Tables\Actions\Action::make('nonaktifkan')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_active)
                    ->requiresConfirmation()
                    ->modalDescription('Modul ini tidak akan ditagih lagi bulan depan untuk Lembaga ini. Riwayat aktivasinya tetap tersimpan.')
                    ->action(fn ($record) => $record->update([
                        'is_active' => false,
                        'nonaktif_sejak' => now(),
                    ])),

                Tables\Actions\Action::make('aktifkanKembali')
                    ->label('Aktifkan Lagi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->action(fn ($record) => $record->update([
                        'is_active' => true,
                        'aktif_sejak' => now(),
                        'nonaktif_sejak' => null,
                    ])),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Menghapus baris ini permanen (beda dari "Nonaktifkan" yang tetap menyimpan riwayat). Gunakan hanya kalau modul ini salah tambah.'),

            ])
            ->emptyStateHeading('Belum ada modul aktif')
            ->emptyStateDescription('Klik "Aktifkan Modul" untuk mulai menambahkan modul add-on ke Lembaga ini.');
    }
}
