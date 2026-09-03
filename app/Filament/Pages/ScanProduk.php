<?php

namespace App\Filament\Pages;

use App\Models\Kantin;
use App\Models\KantinProduk;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ScanProduk extends Page
{
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Scan Produk';
    protected static ?string $title = 'Scan Produk';
    protected static ?string $navigationIcon = 'heroicon-o-viewfinder-circle';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.scan-produk';

    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        if (! Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN)) {
            return false;
        }

        return parent::canAccess();
    }

    // Sama seperti Kasir -- kalau cuma ada 1 kantin aktif, langsung
    // dipakai tanpa perlu pilih dulu. Tidak pakai PIN di sini karena ini
    // alat input data/stok, bukan transaksi uang.
    public array $daftarKantin = [];
    public ?int $kantinTerpilih = null;

    // 'restock' | 'produk_baru' | null (belum ada yang discan)
    public ?string $mode = null;

    // Data produk yang KETEMU (mode restock)
    public ?array $produkDitemukan = null;
    public $jumlahTambahan = null;

    // Data produk BARU (mode produk_baru) -- barcode-nya sudah otomatis
    // terisi dari hasil scan, tidak perlu diketik ulang.
    public ?string $barcodeBaru = null;
    public $namaBaru = '';
    public $kategoriBaru = '';
    public $hargaBaru = null;
    public $stokAwalBaru = null;

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $kantins = Kantin::where('yayasan_id', $tenant?->id)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $this->daftarKantin = $kantins->pluck('nama', 'id')->all();

        if ($kantins->count() === 1) {
            $this->kantinTerpilih = $kantins->first()->id;
        }
    }

    public function pilihKantin(int $kantinId): void
    {
        if (! array_key_exists($kantinId, $this->daftarKantin)) {
            return;
        }

        $this->kantinTerpilih = $kantinId;
        $this->resetMode();
    }

    public function gantiKantin(): void
    {
        $this->kantinTerpilih = null;
        $this->resetMode();
    }

    protected function resetMode(): void
    {
        $this->mode = null;
        $this->produkDitemukan = null;
        $this->jumlahTambahan = null;
        $this->barcodeBaru = null;
        $this->namaBaru = '';
        $this->kategoriBaru = '';
        $this->hargaBaru = null;
        $this->stokAwalBaru = null;
    }

    /**
     * Dipanggil tiap kali barcode scanner/keyboard mengirim kode --
     * cek dulu apakah produk ini SUDAH ADA di kantin yang sedang aktif
     * (barcode wajib unik se-sistem, jadi cukup cari di kantin ini saja).
     */
    public function handleScan(string $code): void
    {
        $code = trim($code);

        if ($code === '' || ! $this->kantinTerpilih) {
            return;
        }

        $produk = KantinProduk::where('kantin_id', $this->kantinTerpilih)
            ->where('barcode', $code)
            ->first();

        if ($produk) {
            // SUDAH ADA -- mode restok, TIDAK bikin produk baru.
            $this->mode = 'restock';
            $this->produkDitemukan = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'kategori' => $produk->kategori,
                'harga' => $produk->harga,
                'stok_sekarang' => $produk->stok,
                'gambar' => $produk->gambar,
            ];
            $this->jumlahTambahan = null;
            return;
        }

        // Cek juga barcode ini sudah kepakai produk kantin LAIN mana
        // (barcode unik se-sistem) -- kasih tahu jelas kalau begitu,
        // supaya tidak bingung kenapa tidak bisa didaftarkan sebagai
        // produk baru.
        $punyaKantinLain = KantinProduk::where('barcode', $code)->first();

        if ($punyaKantinLain) {
            Notification::make()
                ->title('Barcode ini sudah dipakai produk lain')
                ->body("\"{$punyaKantinLain->nama}\" di kantin \"{$punyaKantinLain->kantin?->nama}\" — 1 barcode cuma boleh 1 produk se-sistem.")
                ->warning()
                ->send();
            return;
        }

        // BELUM ADA SAMA SEKALI -- mode produk baru, barcode-nya sudah
        // otomatis keisi dari hasil scan.
        $this->mode = 'produk_baru';
        $this->barcodeBaru = $code;
        $this->namaBaru = '';
        $this->kategoriBaru = '';
        $this->hargaBaru = null;
        $this->stokAwalBaru = null;
    }

    public function simpanRestock(): void
    {
        if (! $this->produkDitemukan) {
            return;
        }

        $jumlah = (int) $this->jumlahTambahan;

        if ($jumlah <= 0) {
            Notification::make()->title('Isi jumlah yang masuk dulu (lebih dari 0).')->warning()->send();
            return;
        }

        $produk = KantinProduk::find($this->produkDitemukan['id']);

        if (! $produk) {
            Notification::make()->title('Produk tidak ditemukan lagi -- mungkin sudah dihapus.')->danger()->send();
            $this->resetMode();
            return;
        }

        $stokBaru = ($produk->stok ?? 0) + $jumlah;
        $produk->update(['stok' => $stokBaru]);

        Notification::make()
            ->title('Stok berhasil ditambah — ' . $produk->nama)
            ->body("Stok sekarang: {$stokBaru}")
            ->success()
            ->send();

        $this->resetMode();
    }

    public function simpanProdukBaru(): void
    {
        if (! $this->barcodeBaru) {
            return;
        }

        if (blank($this->namaBaru) || blank($this->hargaBaru)) {
            Notification::make()->title('Nama dan Harga wajib diisi.')->warning()->send();
            return;
        }

        $produk = KantinProduk::create([
            'kantin_id' => $this->kantinTerpilih,
            'barcode' => $this->barcodeBaru,
            'nama' => $this->namaBaru,
            'kategori' => $this->kategoriBaru ?: null,
            'harga' => (int) preg_replace('/[^0-9]/', '', (string) $this->hargaBaru),
            'stok' => filled($this->stokAwalBaru) ? (int) $this->stokAwalBaru : null,
            'is_active' => true,
        ]);

        Notification::make()
            ->title('Produk baru berhasil didaftarkan — ' . $produk->nama)
            ->success()
            ->send();

        $this->resetMode();
    }

    public function batal(): void
    {
        $this->resetMode();
    }
}
