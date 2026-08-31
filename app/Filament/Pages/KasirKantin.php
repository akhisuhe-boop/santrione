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

        if (! Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN)) {
            return false;
        }

        return parent::canAccess();
    }

    public ?int $lembaga_id = null;

    // Data siswa yang lagi discan (null = belum ada siswa dipilih)
    public ?array $siswaTerpilih = null;

    // True = transaksi tunai tanpa kartu siswa (guru, pengunjung, dll)
    public bool $modeTunai = false;

    // Preview produk terakhir yang berhasil discan (buat feedback visual)
    public ?array $previewProduk = null;

    /** @var array<int, array{id:int,nama:string,harga:int,qty:int,gambar:?string}> */
    public array $cart = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->lembaga_id = \App\Models\Lembaga::where('yayasan_id', $tenant?->id)
            ->value('id');
    }

    /**
     * Dipanggil dari JS (html5-qrcode) tiap kali kamera berhasil baca
     * kode — dicoba dulu sebagai siswa, kalau tidak ketemu baru dicoba
     * sebagai produk. Jadi kasir tinggal scan APAPUN (kartu siswa atau
     * label produk) tanpa perlu pilih mode dulu.
     */
    public function handleScan(string $code): void
    {
        $code = trim($code);

        if ($code === '') {
            return;
        }

        $siswa = Siswa::with(['wallet', 'kelas', 'lembaga'])
            ->where('lembaga_id', $this->lembaga_id)
            ->where(function ($q) use ($code) {
                $q->where('nis', $code)
                    ->orWhere('nisn', $code)
                    ->orWhere('rfid_code', $code)
                    ->orWhere('qr_code', $code);
            })
            ->first();

        if ($siswa) {
            $this->pilihSiswa($siswa);
            return;
        }

        $produk = KantinProduk::where('lembaga_id', $this->lembaga_id)
            ->where('barcode', $code)
            ->first();

        if ($produk) {
            $this->scanProduk($produk);
            return;
        }

        Notification::make()
            ->title('Kode tidak dikenali')
            ->body('Tidak ada siswa atau produk dengan kode: ' . $code)
            ->warning()
            ->send();
    }

    protected function pilihSiswa(Siswa $siswa): void
    {
        $terpakaiHariIni = \App\Models\KantinTransaksi::withoutGlobalScopes()
            ->where('siswa_id', $siswa->id)
            ->where('metode', 'wallet')
            ->whereDate('tanggal', today())
            ->sum('total');

        $this->siswaTerpilih = [
            'id' => $siswa->id,
            'nama' => $siswa->nama_lengkap,
            'nis' => $siswa->nis,
            'foto' => $siswa->foto,
            'kelas' => $siswa->kelas->nama ?? '-',
            'lembaga' => $siswa->lembaga->nama ?? '-',
            'saldo' => $siswa->wallet->saldo ?? 0,
            'limit_harian' => $siswa->limit_harian_kantin,
            'terpakai_hari_ini' => $terpakaiHariIni,
        ];
    }

    public function gantiSiswa(): void
    {
        $this->siswaTerpilih = null;
        $this->modeTunai = false;
        $this->cart = [];
        $this->previewProduk = null;
    }

    /**
     * Mulai transaksi tunai tanpa kartu siswa — dipakai untuk guru,
     * pengunjung, atau siapa pun yang tidak punya kartu/wallet siswa.
     */
    public function mulaiTransaksiTunai(): void
    {
        $this->siswaTerpilih = null;
        $this->modeTunai = true;
        $this->cart = [];
        $this->previewProduk = null;
    }

    protected function scanProduk(KantinProduk $produk): void
    {
        if (! $produk->is_active) {
            Notification::make()->title($produk->nama . ' sedang tidak dijual.')->warning()->send();
            return;
        }

        if ($produk->habisStok()) {
            Notification::make()->title($produk->nama . ' stoknya habis.')->danger()->send();
            return;
        }

        if (isset($this->cart[$produk->id])) {
            $this->cart[$produk->id]['qty']++;
        } else {
            $this->cart[$produk->id] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => $produk->harga,
                'qty' => 1,
                'gambar' => $produk->gambar,
            ];
        }

        $this->previewProduk = [
            'nama' => $produk->nama,
            'harga' => $produk->harga,
            'gambar' => $produk->gambar,
            'stok' => $produk->stok,
        ];
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

    public function tambahKeranjang(int $produkId): void
    {
        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['qty']++;
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
        if (! $this->siswaTerpilih && ! $this->modeTunai) {
            Notification::make()->title('Scan kartu siswa dulu, atau pilih transaksi tunai tanpa kartu.')->warning()->send();
            return;
        }

        if (empty($this->cart)) {
            Notification::make()->title('Keranjang masih kosong.')->warning()->send();
            return;
        }

        try {

            $items = collect($this->cart)
                ->map(fn ($item) => ['produk_id' => $item['id'], 'qty' => $item['qty']])
                ->values()
                ->all();

            // Ada siswa terpilih -> bayar pakai wallet siswa.
            // Tidak ada siswa (guru/tamu) -> selalu tunai.
            $metode = $this->siswaTerpilih ? 'wallet' : 'tunai';

            $trx = app(KantinService::class)->checkout(
                $this->lembaga_id,
                $this->siswaTerpilih['id'] ?? null,
                $metode,
                $items,
                null
            );

            Notification::make()
                ->title('Transaksi berhasil — ' . $trx->kode)
                ->body($metode === 'wallet'
                    ? 'Sisa saldo: Rp ' . number_format($this->siswaTerpilih['saldo'] - $this->total, 0, ',', '.')
                    : 'Total tunai diterima: Rp ' . number_format($this->total, 0, ',', '.'))
                ->success()
                ->send();

            $this->cart = [];
            $this->previewProduk = null;
            $this->siswaTerpilih = null; // siap buat siswa/transaksi berikutnya
            $this->modeTunai = false;

        } catch (\Illuminate\Validation\ValidationException $e) {

            Notification::make()
                ->title(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }
}
