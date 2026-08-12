<?php

namespace App\Filament\Platform\Pages;

use App\Models\PlatformWhatsappSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Ganti pengaturan WA Qinara dari .env ke database -- bisa diedit
 * dari panel tanpa perlu SSH. WhatsappService::sendPlatform() baca
 * dari sini duluan, fallback ke .env (config/services.php) kalau
 * baris ini kosong -- jadi tidak breaking untuk server yang sudah
 * terlanjur isi .env sebelumnya.
 */
class PengaturanWhatsapp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Pengaturan WhatsApp';
    protected static ?string $navigationGroup = 'Komunikasi';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Pengaturan WhatsApp Qinara';

    protected static string $view = 'filament.platform.pages.pengaturan-whatsapp';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = PlatformWhatsappSetting::current();
        $this->form->fill($setting->only(['api_url', 'token', 'sender']));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Kredensial Xsender — Nomor WA Resmi Qinara')
                    ->description('Dipakai untuk SEMUA notifikasi platform (tagihan langganan, broadcast, reminder trial) ke tenant. Bukan nomor WA sekolah manapun.')
                    ->schema([
                        TextInput::make('api_url')
                            ->label('API URL')
                            ->default('https://xsender.id/id/send-message')
                            ->required(),

                        TextInput::make('token')
                            ->label('Kunci API (Token)')
                            ->password()
                            ->revealable()
                            ->required(),

                        TextInput::make('sender')
                            ->label('Nomor WhatsApp Device')
                            ->placeholder('628xxxxxxxx')
                            ->required(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = PlatformWhatsappSetting::current();
        $setting->update($this->form->getState());

        Notification::make()
            ->title('Pengaturan WhatsApp tersimpan')
            ->success()
            ->send();
    }

    public function testKirim(): void
    {
        $data = $this->form->getState();

        if (blank($data['sender'] ?? null)) {
            Notification::make()->title('Isi nomor pengirim dulu')->danger()->send();

            return;
        }

        // Simpan dulu supaya sendPlatform() pakai nilai TERBARU dari
        // form (bukan yang lama tersimpan di database).
        $this->save();

        $berhasil = \App\Services\WhatsappService::sendPlatform(
            $data['sender'],
            'Ini pesan uji dari Pengaturan WhatsApp Qinara Platform. Kalau pesan ini masuk, kredensial sudah benar.'
        );

        if ($berhasil) {
            Notification::make()->title('Pesan uji berhasil dikirim')->success()->send();
        } else {
            Notification::make()->title('Gagal mengirim pesan uji')->body('Cek log server untuk detail error.')->danger()->send();
        }
    }
}
