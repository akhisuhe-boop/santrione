<?php

namespace App\Filament\Pages;

use App\Models\NotificationTypeToggle;
use App\Support\NotificationType;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * Toggle aktif/nonaktif per jenis notifikasi, per Lembaga -- FONDASI
 * untuk jawab "sekolah bisa matikan notif tertentu sendiri" (absensi
 * on/off, keuangan on/off, dst). Grouped per kategori (Absensi, PPDB,
 * Keuangan, dst) supaya tidak jadi daftar 22 baris rata tanpa
 * struktur.
 *
 * CATATAN PENTING: toggle di sini BARU tersimpan ke database
 * (NotificationTypeToggle) -- method NotificationService yang
 * sebenarnya BELUM dicek ke toggle ini (konversi bertahap, lihat
 * commit sebelumnya). Jadi untuk sementara, mematikan toggle di sini
 * TIDAK langsung menghentikan pengiriman notifikasi terkait sampai
 * method NotificationService yang bersangkutan dikonversi.
 */
class PengaturanNotifikasi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Pengaturan Notifikasi';
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?string $title = 'Pengaturan Notifikasi WhatsApp';

    protected static string $view = 'filament.pages.pengaturan-notifikasi';

    public function getLembagas()
    {
        return Filament::getTenant()->lembagas;
    }

    public function getKatalogPerKategori(): array
    {
        return collect(NotificationType::all())
            ->map(fn ($item, $key) => array_merge($item, ['key' => $key]))
            ->groupBy('kategori')
            ->toArray();
    }

    public function isEnabled(int $lembagaId, string $key): bool
    {
        return NotificationTypeToggle::isEnabled($lembagaId, $key);
    }

    public function toggleNotifikasi(int $lembagaId, string $key): void
    {
        $aktifSekarang = NotificationTypeToggle::isEnabled($lembagaId, $key);
        NotificationTypeToggle::setEnabled($lembagaId, $key, ! $aktifSekarang);
    }
}
