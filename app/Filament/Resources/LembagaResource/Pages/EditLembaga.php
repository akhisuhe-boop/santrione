<?php

namespace App\Filament\Resources\LembagaResource\Pages;

use App\Filament\Resources\LembagaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLembaga extends EditRecord
{
    protected static string $resource = LembagaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('daftarkanXendit')
                ->label(fn () => $this->record->xendit_account_holder_id ? 'Terdaftar di Xendit' : 'Daftarkan ke Xendit')
                ->icon('heroicon-o-building-library')
                ->color(fn () => $this->record->xendit_account_holder_id ? 'success' : 'primary')
                ->disabled(fn () => (bool) $this->record->xendit_account_holder_id)
                ->visible(fn () => (bool) auth()->user()?->is_platform_admin)
                ->requiresConfirmation()
                ->modalDescription('Lembaga ini akan didaftarkan sebagai sub-account Xendit untuk menerima split payment dari pembayaran wali murid. Pastikan email Lembaga sudah terisi dengan benar.')
                ->action(function () {
                    try {
                        app(\App\Services\XenditService::class)->daftarkanSubAccount($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil didaftarkan ke Xendit')
                            ->body('Status: menunggu verifikasi Xendit.')
                            ->success()
                            ->send();

                        $this->record->refresh();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal mendaftarkan ke Xendit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('daftarkanDoku')
                ->label(fn () => ($this->record->doku_split_rule_id && $this->record->doku_split_rule_id_flat) ? 'Terdaftar di DOKU (Sub-Account + Split)' : 'Daftarkan ke DOKU')
                ->icon('heroicon-o-building-library')
                ->color(fn () => ($this->record->doku_split_rule_id && $this->record->doku_split_rule_id_flat) ? 'success' : 'primary')
                ->disabled(fn () => (bool) ($this->record->doku_split_rule_id && $this->record->doku_split_rule_id_flat))
                ->visible(fn () => (bool) auth()->user()?->is_platform_admin)
                ->requiresConfirmation()
                ->modalDescription('Lembaga ini akan didaftarkan sebagai Sub-Account V2 DOKU, lalu dibuatkan 2 Split Rule (persentase & flat-cap, dipilih otomatis per transaksi sesuai nominal). Pastikan DOKU_PLATFORM_ACCOUNT_NO sudah diisi di .env.')
                ->action(function () {
                    try {
                        $doku = app(\App\Services\DokuService::class);
                        $doku->registerSubAccount($this->record);
                        $doku->buatSplitRule($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil didaftarkan ke DOKU')
                            ->body('Sub-Account + Split Rule aktif. Status: menunggu verifikasi DOKU.')
                            ->success()
                            ->send();

                        $this->record->refresh();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal mendaftarkan ke DOKU')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // DIUBAH -- sebelumnya khusus PPDB saja. Sekarang generic:
            // admin isi sendiri kategori (bebas, harus sama persis
            // dengan yang diisi di field "Kategori Rekening DOKU" pada
            // Jenis Tagihan terkait -- lihat JenisTagihanResource).
            Actions\Action::make('daftarkanRekeningLain')
                ->label('+ Daftarkan Rekening Kategori Lain')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->visible(fn () => (bool) auth()->user()?->is_platform_admin && (bool) $this->record->doku_sub_account_id)
                ->form([
                    \Filament\Forms\Components\TextInput::make('kategori')
                        ->label('Kategori')
                        ->helperText('Slug bebas, huruf kecil & underscore saja (mis. "ppdb", "uang_gedung", "seragam"). Harus PERSIS sama dengan yang diisi di field "Kategori Rekening DOKU" pada Jenis Tagihan terkait.')
                        ->required()
                        ->rule('regex:/^[a-z0-9_]+$/')
                        ->validationMessages(['regex' => 'Hanya huruf kecil, angka, dan underscore.'])
                        ->unique(table: \App\Models\LembagaRekening::class, column: 'kategori', modifyRuleUsing: fn ($rule) => $rule->where('lembaga_id', $this->record->id)),
                    \Filament\Forms\Components\TextInput::make('nama')
                        ->label('Label (opsional)')
                        ->placeholder('mis. Rekening PPDB Al-Mubarok')
                        ->maxLength(128),
                ])
                ->requiresConfirmation()
                ->modalDescription('Daftarkan rekening DOKU TERPISAH untuk kategori ini (child dari sub-account utama). Setelah ini, tandai Jenis Tagihan yang sesuai dengan kategori yang sama persis di field "Kategori Rekening DOKU".')
                ->action(function (array $data) {
                    try {
                        app(\App\Services\DokuService::class)->registerRekening($this->record, $data['kategori'], $data['nama'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title("Rekening '{$data['kategori']}' berhasil didaftarkan")
                            ->success()
                            ->send();

                        $this->record->refresh();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal mendaftarkan rekening')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
