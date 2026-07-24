<?php

namespace App\Filament\Pages;

use App\Models\KantinProduk;
use App\Models\Siswa;
use App\Services\KantinService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KasirKantin extends Page
{
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $title = 'Kasir Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.kasir-kantin';

    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return (bool) Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN);
    }

    public ?int $lembaga_id = null;
    public ?int $siswa_id = null;
    public string $metode = 'wallet';
    public string $search = '';

    /** @var array<int, array{id:int,nama:string,harga:int,qty:int}> */
    public array $cart = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->lembaga_id = \App\Models\Lembaga::where('yayasan_id', $tenant?->id)
            ->value('id');
    }

    public function getProdukProperty()
    {
        return KantinProduk::query()
            ->where('lembaga_id', $this->lembaga_id)
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%"))
            ->orderBy('nama')
            ->get();
    }

    public function getSiswaOptionsProperty()
    {
        if (blank($this->search) && ! $this->siswa_id) {
            return collect();
        }

        return Siswa::query()
            ->where('lembaga_id', $this->lembaga_id)
            ->where('status_siswa', 'Aktif')
            ->get();
    }

    public function tambahKeKeranjang(int $produkId): void
    {
        $produk = KantinProduk::find($produkId);

        if (! $produk) {
            return;
        }

        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['qty']++;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => $produk->harga,
                'qty' => 1,
            ];
        }
    }

    public function kurangiKeranjang(int $produkId): void
    {
        if (! isset($this->cart[$produkId])) {
            return;
        }

        $this->cart[$produkId]['qty']--;

        if ($this->cart[$produkId]['qty'] <= 0) {
            unset($this->cart[$produkId]);
        }
    }

    public function hapusDariKeranjang(int $produkId): void
    {
        unset($this->cart[$produkId]);
    }

    public function getTotalProperty(): int
    {
        return collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['qty']);
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang masih kosong.')->warning()->send();
            return;
        }

        try {

            $items = collect($this->cart)
                ->map(fn ($item) => ['produk_id' => $item['id'], 'qty' => $item['qty']])
                ->values()
                ->all();

            $trx = app(KantinService::class)->checkout(
                $this->lembaga_id,
                $this->siswa_id,
                $this->metode,
                $items,
                null // kasir dicatat lewat 'diinput_oleh' di baris Kas; User admin panel tidak terhubung ke Pegawai
            );

            Notification::make()
                ->title('Transaksi berhasil — ' . $trx->kode)
                ->success()
                ->send();

            $this->cart = [];
            $this->siswa_id = null;

        } catch (\Illuminate\Validation\ValidationException $e) {

            Notification::make()
                ->title(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }
}
