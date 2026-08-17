<?php

namespace App\Filament\Platform\Pages;

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
    protected static ?string $title = 'Pengaturan Landing Page';

    protected static string $view = 'filament.platform.pages.landing-setting-page';

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
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('r2-public')
                            ->directory('landing/brand')
                            ->imageEditor()
                            ->columnSpanFull()
                            ->helperText('Kosongkan untuk pakai logo teks bawaan.'),
                        Forms\Components\TextInput::make('brand_name')
                            ->required()
                            ->helperText('Dipakai sebagai teks alternatif kalau logo belum diupload.'),
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

                Forms\Components\Section::make('Legalitas Badan Hukum')
                    ->description('Ditampilkan di kolom "Kontak Resmi" pada footer, supaya calon client lebih percaya. Isi hanya nomor yang sudah resmi terbit -- kosongkan yang belum ada, baris itu otomatis tidak ditampilkan.')
                    ->schema([
                        Forms\Components\TextInput::make('footer_legalitas')
                            ->label('Nama Badan Hukum')
                            ->placeholder('Contoh: PT Qinara Indonesia'),
                        Forms\Components\TextInput::make('nomor_nib')
                            ->label('Nomor Induk Berusaha (NIB)'),
                        Forms\Components\TextInput::make('nomor_akta')
                            ->label('Nomor Akta / AHU'),
                    ])->columns(3),

                Forms\Components\Section::make('Kartu Hero (Gambar Mockup)')
                    ->description('Prioritas tampil di hero: Gambar Mockup (kalau diisi) > kartu dashboard bawaan.')
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

                Forms\Components\Section::make('Section Video "Kenali Lebih Dekat"')
                    ->description('Muncul sebagai section tersendiri (besar, center) di antara "Tinggalkan Sistem Manual" dan "Solusi Terpadu". Kosongkan URL Video untuk menyembunyikan section ini sepenuhnya.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_video_url')
                            ->label('URL Video (opsional)')
                            ->url()
                            ->columnSpanFull()
                            ->helperText('Isi salah satu: link embed YouTube (dari tombol Share > Embed, formatnya https://www.youtube.com/embed/xxxxx), atau URL file video .mp4 langsung.'),
                        Forms\Components\Textarea::make('video_deskripsi')
                            ->label('Deskripsi Video')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Muncul di atas video, di bawah judul "Kenali ... Lebih Dekat".'),
                    ]),

                Forms\Components\Section::make('Statistik Ringkas')
                    ->schema([
                        Forms\Components\TextInput::make('social_proof_text')
                            ->label('Teks Social Proof (contoh: 120+ Lembaga Pendidikan Islam)'),
                        Forms\Components\TextInput::make('stat_efisiensi')->label('Stat: Lebih Efisien'),
                        Forms\Components\TextInput::make('stat_modul')->label('Stat: Modul Terintegrasi'),
                        Forms\Components\TextInput::make('stat_akses')->label('Stat: Akses Real-time'),
                        Forms\Components\TextInput::make('stat_digitalisasi')->label('Stat: Digitalisasi'),
                    ])->columns(2),

                Forms\Components\Section::make('Tracking & Analytics')
                    ->description('Kosongkan yang tidak dipakai -- kode tracking hanya dimuat kalau ID-nya diisi.')
                    ->schema([
                        Forms\Components\TextInput::make('meta_pixel_id')
                            ->label('Meta (Facebook) Pixel ID')
                            ->helperText('Dari Meta Events Manager, contoh: 1234567890123456'),
                        Forms\Components\TextInput::make('tiktok_pixel_id')
                            ->label('TikTok Pixel ID')
                            ->helperText('Dari TikTok Events Manager, contoh: CXXXXXXXXXXXXXXXXXXX'),
                        Forms\Components\TextInput::make('google_ads_id')
                            ->label('Google Ads / Google Tag ID')
                            ->helperText('Contoh: AW-1234567890 atau G-XXXXXXXXXX'),
                    ])->columns(3),

                Forms\Components\Section::make('Promo & Countdown Timer')
                    ->description('Banner urgensi di atas Harga -- TIDAK mengubah harga asli di Billing & Harga, cuma tampilan diskon sementara yang dihitung otomatis dari harga asli. Kalau tanggal berakhir sudah lewat, banner otomatis hilang sendiri (baik togglenya masih nyala atau tidak).')
                    ->schema([
                        Forms\Components\Toggle::make('promo_aktif')
                            ->label('Aktifkan Promo')
                            ->live(),
                        Forms\Components\TextInput::make('promo_teks')
                            ->label('Teks Promo')
                            ->placeholder('Contoh: Diskon Spesial Peluncuran!')
                            ->visible(fn (Forms\Get $get) => $get('promo_aktif')),
                        Forms\Components\TextInput::make('promo_persen')
                            ->label('Persen Diskon')
                            ->numeric()->minValue(1)->maxValue(90)
                            ->suffix('%')
                            ->visible(fn (Forms\Get $get) => $get('promo_aktif')),
                        Forms\Components\DateTimePicker::make('promo_berakhir_pada')
                            ->label('Promo Berakhir Pada')
                            ->native(false)
                            ->visible(fn (Forms\Get $get) => $get('promo_aktif')),
                    ])->columns(2),

                Forms\Components\Section::make('Diskon Tampilan Tahunan')
                    ->description('Persen "hemat" yang ditampilkan di toggle Bulanan/Tahunan pada section Harga. CATATAN PENTING: ini murni tampilan di landing page -- sistem billing/pembayaran belum benar-benar memproses langganan tahunan, jadi orang yang klik "Coba Gratis 14 Hari" tetap masuk alur pendaftaran yang sama seperti biasa apa pun toggle yang mereka pilih.')
                    ->schema([
                        Forms\Components\TextInput::make('tahunan_diskon_persen')
                            ->label('Persen Hemat (Tahunan)')
                            ->numeric()->minValue(0)->maxValue(90)
                            ->suffix('%')
                            ->default(15),
                    ]),

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
