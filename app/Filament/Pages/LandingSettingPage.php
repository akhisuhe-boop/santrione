<?php

namespace App\Filament\Pages;

use App\Models\LandingSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LandingSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Pengaturan Landing Page';

    protected static bool $isScopedToTenant = false;

    protected static string $view = 'filament.pages.landing-setting-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_platform_admin ?? false);
    }

    public function mount(): void
    {
        $this->form->fill(LandingSetting::current()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Brand')
                    ->schema([
                        Forms\Components\TextInput::make('brand_name')
                            ->required()
                            ->helperText('Contoh: "Qinara Apps" — kata pertama & sisanya otomatis diberi warna berbeda di logo.'),
                        Forms\Components\TextInput::make('badge_text')
                            ->label('Teks Badge di Hero'),
                        Forms\Components\TextInput::make('headline_baris1')->columnSpanFull(),
                        Forms\Components\TextInput::make('headline_baris2')->columnSpanFull(),
                        Forms\Components\Textarea::make('subheadline')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Media Sosial')
                    ->schema([
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp (format 62...)')
                            ->required(),
                        Forms\Components\TextInput::make('email_kontak')->email(),
                        Forms\Components\Textarea::make('whatsapp_pesan_default')
                            ->label('Pesan Default WhatsApp')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('alamat'),
                        Forms\Components\TextInput::make('ig_url')->label('Instagram URL'),
                        Forms\Components\TextInput::make('fb_url')->label('Facebook URL'),
                        Forms\Components\TextInput::make('yt_url')->label('YouTube URL'),
                        Forms\Components\TextInput::make('x_url')->label('X (Twitter) URL'),
                    ])->columns(2),

                Forms\Components\Section::make('Kartu Mockup Dashboard (Hero)')
                    ->description('Kalau gambar diisi, kartu dashboard rekaan di hero otomatis diganti gambar asli ini.')
                    ->schema([
                        Forms\Components\FileUpload::make('hero_mockup_gambar')
                            ->label('Gambar Mockup (opsional)')
                            ->image()
                            ->disk('r2-public')
                            ->directory('landing/hero')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hero_kpi_keuangan')
                            ->label('Angka Total Keuangan (contoh)')
                            ->helperText('Hanya dipakai jika gambar mockup di atas kosong.'),
                        Forms\Components\TextInput::make('hero_kpi_keuangan_growth')
                            ->label('Teks Pertumbuhan (contoh: +12% bulan ini)'),
                        Forms\Components\TextInput::make('hero_kpi_kehadiran_persen')
                            ->label('Persentase Kehadiran (contoh)')
                            ->numeric()
                            ->suffix('%'),
                    ])->columns(2),

                Forms\Components\Section::make('Statistik Ringkas')
                    ->schema([
                        Forms\Components\TextInput::make('social_proof_text')
                            ->label('Teks Social Proof (contoh: 120+ Pesantren)'),
                        Forms\Components\TextInput::make('stat_efisiensi')->label('Stat: Lebih Efisien'),
                        Forms\Components\TextInput::make('stat_modul')->label('Stat: Modul Terintegrasi'),
                        Forms\Components\TextInput::make('stat_akses')->label('Stat: Akses Real-time'),
                        Forms\Components\TextInput::make('stat_digitalisasi')->label('Stat: Digitalisasi'),
                    ])->columns(2),

                Forms\Components\Section::make('Footer')
                    ->schema([
                        Forms\Components\TextInput::make('footer_text')
                            ->label('Teks Copyright')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        LandingSetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Pengaturan landing page tersimpan')
            ->success()
            ->send();
    }
}
