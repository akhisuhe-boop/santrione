<?php

namespace App\Filament\Pages;

use App\Models\KantinProduk;
use App\Models\Pegawai;
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

    // Lembaga tempat kasir ini beroperasi -- dipakai untuk membatasi
    // katalog PRODUK yang bisa dijual di kasir ini. Siswa/guru yang
    // scan kartu TIDAK dibatasi ke lembaga ini (lihat handleScan) --
    // mereka bisa dari lembaga manapun dalam 1 yayasan yang sama,
    // supaya 1 kasir/kantin bisa melayani kantin bersama lintas
    // lembaga (mis. 1 kompleks yayasan dengan beberapa unit sekolah).
    public ?int $lembaga_id = null;

    // Data siswa yang lagi discan (null = belum ada siswa dipilih)
    public ?array $siswaTerpilih = null;

    // Data guru/staf yang lagi discan (null = belum ada dipilih)
    public ?array $guruTerpilih = null;

    // True = transaksi tunai tanpa kartu -- KHUSUS pengunjung umum
    // (bukan siswa/guru, yang selalu pakai kartu masing-masing).
    public bool $modeTunai = false;

    // Batas & pemakaian transaksi TUNAI hari ini di kasir ini (null =
    // tidak dibatasi). Dipakai untuk mencegah kasir menjadikan tunai
    // sebagai jalan pintas rutin buat siswa/guru yang malas scan kartu
    // -- lihat kolom Lembaga->limit_tunai_kantin_harian.
    public ?int $limitTunaiHarian = null;
    public int $tunaiTerpakaiHariIni = 0;

    // Preview produk terakhir yang berhasil discan (buat feedback visual)
    public ?array $previewProduk = null;

    /** @var array<int, array{id:int,nama:string,harga:int,qty:int,gambar:?string}> */
    public array $cart = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->lembaga_id = \App\Models\Lembaga::where('yayasan_id', $tenant?->id)
            ->value('id');

        $this->muatLimitTunai();
    }

    /**
     * Hitung ulang limit & pemakaian transaksi tunai hari ini -- dipanggil
     * di mount() dan lagi setiap habis checkout, supaya angkanya selalu
     * akurat tanpa perlu refresh halaman manual.
     */
    protected function muatLimitTunai(): void
    {
        $this->limitTunaiHarian = \App\Models\Lembaga::whereKey($this->lembaga_id)
            ->value('limit_tunai_kantin_harian');

        if ($this->limitTunaiHarian === null) {
            $this->tunaiTerpakaiHariIni = 0;
            return;
        }

        // Transaksi tunai (pengunjung) tidak punya lembaga_id (sengaja
        // dikosongkan, lihat KantinService) -- jadi dihitung lewat item
        // yang dibeli, yang produknya pasti berasal dari katalog lembaga
        // kasir ini (scan produk sudah dibatasi ke $this->lembaga_id).
        $this->tunaiTerpakaiHariIni = \App\Models\KantinTransaksi::withoutGlobalScopes()
            ->where('metode', 'tunai')
            ->whereDate('tanggal', today())
            ->whereHas('items.produk', fn ($q) => $q->where('lembaga_id', $this->lembaga_id))
            ->count();
    }

    protected function limitTunaiTercapai(): bool
    {
        return $this->limitTunaiHarian !== null
            && $this->tunaiTerpakaiHariIni >= $this->limitTunaiHarian;
    }

    /**
     * Cek ulang limit tunai PERSIS sebelum checkout diproses (bukan cuma
     * waktu guru/pengunjung dipilih), supaya tidak kebobolan kalau ada
     * 2 sesi kasir jalan bersamaan di lembaga yang sama. Return true
     * kalau boleh lanjut, false kalau harus dibatalkan (notifikasi sudah
     * dikirim di dalam sini).
     */
    protected function pastikanBolehTunai(): bool
    {
        $this->muatLimitTunai();

        if ($this->limitTunaiTercapai()) {
            Notification::make()
                ->title('Limit transaksi tunai hari ini sudah tercapai')
                ->body('Transaksi ini tidak bisa diproses tunai.')
                ->danger()
                ->send();
            return false;
        }

        return true;
    }

    /**
     * Dipanggil dari JS (html5-qrcode) tiap kali kamera berhasil baca
     * kode — dicoba dulu sebagai siswa, lalu sebagai guru/staf, kalau
     * tidak ketemu baru dicoba sebagai produk. Jadi kasir tinggal scan
     * APAPUN (kartu siswa, kartu guru/staf, atau label produk) tanpa
     * perlu pilih mode dulu.
     */
    public function handleScan(string $code): void
    {
        $code = trim($code);

        if ($code === '') {
            return;
        }

        // Siswa/guru dicari lintas lembaga (dalam 1 yayasan yang sama --
        // tenant scope global bawaan model yang menangani ini), BUKAN
        // dibatasi ke lembaga tempat kasir ini berada. Lihat catatan
        // di properti $lembaga_id.

        $siswa = Siswa::with(['wallet', 'kelas', 'lembaga'])
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

        $guru = Pegawai::where(function ($q) use ($code) {
                $q->where('niy', $code)
                    ->orWhere('rfid', $code)
                    ->orWhere('qr_code', $code);
            })
            ->first();

        if ($guru) {
            $this->pilihGuru($guru);
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
            ->body('Tidak ada siswa, guru/staf, atau produk dengan kode: ' . $code)
            ->warning()
            ->send();
    }

    protected function pilihSiswa(Siswa $siswa): void
    {
        $this->guruTerpilih = null;
        $this->modeTunai = false;

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
            'lembaga_id' => $siswa->lembaga_id,
            'saldo' => $siswa->wallet->saldo ?? 0,
            'limit_harian' => $siswa->limit_harian_kantin,
            'terpakai_hari_ini' => $terpakaiHariIni,
        ];
    }

    protected function pilihGuru(Pegawai $guru): void
    {
        $this->muatLimitTunai();

        if ($this->limitTunaiTercapai()) {
            Notification::make()
                ->title('Limit transaksi tunai hari ini sudah tercapai')
                ->body("Guru/staf juga bayar tunai (belum ada wallet), jadi ikut kena limit. Sudah {$this->tunaiTerpakaiHariIni} dari {$this->limitTunaiHarian} transaksi tunai hari ini.")
                ->warning()
                ->send();
            return;
        }

        $this->siswaTerpilih = null;
        $this->modeTunai = false;

        $lembagaUtama = $guru->lembagaUtama();

        $this->guruTerpilih = [
            'id' => $guru->id,
            'nama' => $guru->nama,
            'niy' => $guru->niy,
            'foto' => $guru->foto,
            'lembaga' => $lembagaUtama?->nama ?? '-',
            'lembaga_id' => $lembagaUtama?->id,
        ];
    }

    public function gantiSiswa(): void
    {
        $this->siswaTerpilih = null;
        $this->guruTerpilih = null;
        $this->modeTunai = false;
        $this->cart = [];
        $this->previewProduk = null;
    }

    /**
     * Mulai transaksi tunai tanpa kartu — KHUSUS pengunjung umum (bukan
     * siswa/guru; siswa pakai kartu sendiri dibayar wallet, guru/staf
     * pakai kartu sendiri tapi dibayar tunai juga — lihat pilihGuru()).
     */
    public function mulaiTransaksiTunai(): void
    {
        $this->muatLimitTunai();

        if ($this->limitTunaiTercapai()) {
            Notification::make()
                ->title('Limit transaksi tunai hari ini sudah tercapai')
                ->body("Sudah {$this->tunaiTerpakaiHariIni} dari {$this->limitTunaiHarian} transaksi tunai hari ini. Pembeli selain pengunjung wajib pakai kartu/wallet.")
                ->warning()
                ->send();
            return;
        }

        $this->siswaTerpilih = null;
        $this->guruTerpilih = null;
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
        if (! $this->siswaTerpilih && ! $this->guruTerpilih && ! $this->modeTunai) {
            Notification::make()->title('Scan kartu siswa/guru dulu, atau pilih transaksi pengunjung tanpa kartu.')->warning()->send();
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

            // Siswa -> bayar wallet, diatribusikan ke lembaga siswa.
            // Guru/staf -> SELALU tunai (belum ada wallet pegawai), tapi
            // tetap tercatat identitasnya & diatribusikan ke lembaga guru,
            // dan ikut kena limit tunai harian yang sama dengan pengunjung.
            // Pengunjung -> tunai, tidak diatribusikan ke lembaga manapun.
            if ($this->siswaTerpilih) {
                $metode = 'wallet';
                $siswaId = $this->siswaTerpilih['id'];
                $pegawaiId = null;
                $lembagaId = $this->siswaTerpilih['lembaga_id'];
                $saldoSebelum = $this->siswaTerpilih['saldo'];
            } elseif ($this->guruTerpilih) {
                $metode = 'tunai';
                $siswaId = null;
                $pegawaiId = $this->guruTerpilih['id'];
                $lembagaId = $this->guruTerpilih['lembaga_id'];
                $saldoSebelum = null;

                if (! $this->pastikanBolehTunai()) {
                    return;
                }
            } else {
                $metode = 'tunai';
                $siswaId = null;
                $pegawaiId = null;
                $lembagaId = null;
                $saldoSebelum = null;

                if (! $this->pastikanBolehTunai()) {
                    return;
                }
            }

            $trx = app(KantinService::class)->checkout(
                $lembagaId,
                $siswaId,
                $pegawaiId,
                $metode,
                $items,
                null
            );

            Notification::make()
                ->title('Transaksi berhasil — ' . $trx->kode)
                ->body($metode === 'wallet'
                    ? 'Sisa saldo: Rp ' . number_format($saldoSebelum - $this->total, 0, ',', '.')
                    : 'Total tunai diterima: Rp ' . number_format($this->total, 0, ',', '.'))
                ->success()
                ->send();

            $this->cart = [];
            $this->previewProduk = null;
            $this->siswaTerpilih = null; // siap buat pembeli berikutnya
            $this->guruTerpilih = null;
            $this->modeTunai = false;

            $this->muatLimitTunai(); // refresh counter tunai kalau transaksi ini tunai

        } catch (\Illuminate\Validation\ValidationException $e) {

            Notification::make()
                ->title(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }
}
