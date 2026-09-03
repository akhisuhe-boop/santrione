<?php

namespace App\Filament\Pages;

use App\Models\Kantin;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
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
    protected static ?int $navigationSort = 4;

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

    // Semua kantin aktif di tenant ini -- kasir bisa beroperasi di
    // beberapa kantin (mis. beberapa outlet di 1 sekolah, atau kantin
    // bersama lintas lembaga). id => nama.
    public array $daftarKantin = [];

    // Kantin yang lagi dioperasikan kasir ini. Null = belum pilih (kalau
    // cuma ada 1 kantin aktif TANPA PIN, otomatis kepilih di mount()
    // tanpa perlu tanya kasir).
    public ?int $kantinTerpilih = null;

    // Kantin yang lagi menunggu PIN dikonfirmasi (beda dari
    // kantinTerpilih -- baru pindah ke kantinTerpilih setelah PIN-nya
    // benar). Null = tidak ada yang lagi diverifikasi.
    public ?int $kantinMenungguPin = null;
    public string $pinInput = '';

    // Data siswa yang lagi discan (null = belum ada siswa dipilih)
    public ?array $siswaTerpilih = null;

    // True = transaksi tunai tanpa kartu -- dipakai untuk SIAPAPUN
    // yang bukan siswa (guru, staf, pengunjung), tidak perlu identitas
    // khusus, semua diperlakukan sama & sama-sama kena limit tunai
    // harian kantin ini (diatur platform admin per kantin).
    public bool $modeTunai = false;

    // Batas & pemakaian transaksi TUNAI hari ini DI KANTIN INI (null =
    // tidak dibatasi) -- lihat Kantin->limit_tunai_kantin_harian, diatur
    // platform admin lewat KantinPengaturanResource.
    public ?int $limitTunaiHarian = null;
    public int $tunaiTerpakaiHariIni = 0;

    // Preview produk terakhir yang berhasil discan (buat feedback visual)
    public ?array $previewProduk = null;

    /** @var array<int, array{id:int,nama:string,harga:int,qty:int,gambar:?string}> */
    public array $cart = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $kantins = Kantin::where('yayasan_id', $tenant?->id)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $this->daftarKantin = $kantins->pluck('nama', 'id')->all();

        // Cuma 1 kantin aktif -- langsung dipakai TANPA tanya kasir dulu,
        // KECUALI kantin itu punya PIN (PIN tetap wajib diverifikasi
        // walau cuma ada 1 pilihan -- PIN bukan cuma soal navigasi, tapi
        // soal keamanan).
        if ($kantins->count() === 1) {

            $satuSatunya = $kantins->first();

            if (blank($satuSatunya->pin)) {
                $this->aktifkanKantin($satuSatunya->id);
            } else {
                $this->kantinMenungguPin = $satuSatunya->id;
            }
        }

        $this->muatLimitTunai();
    }

    public function pilihKantin(int $kantinId): void
    {
        if (! array_key_exists($kantinId, $this->daftarKantin)) {
            return;
        }

        $kantin = Kantin::find($kantinId);

        if (! $kantin) {
            return;
        }

        if (blank($kantin->pin)) {
            $this->aktifkanKantin($kantinId);
            return;
        }

        // Kantin ini punya PIN -- tampilkan layar input PIN dulu,
        // belum benar-benar diaktifkan.
        $this->kantinMenungguPin = $kantinId;
        $this->pinInput = '';
    }

    public function verifikasiPin(): void
    {
        if (! $this->kantinMenungguPin) {
            return;
        }

        $kantin = Kantin::find($this->kantinMenungguPin);

        if (! $kantin || ! $kantin->cekPin($this->pinInput)) {
            $this->pinInput = '';

            Notification::make()
                ->title('PIN salah')
                ->body('Coba lagi, atau hubungi admin kalau lupa PIN kantin ini.')
                ->danger()
                ->send();

            return;
        }

        $kantinId = $this->kantinMenungguPin;
        $this->kantinMenungguPin = null;
        $this->pinInput = '';

        $this->aktifkanKantin($kantinId);
    }

    /**
     * Batal verifikasi PIN, kembali ke layar pilih kantin.
     */
    public function batalPin(): void
    {
        $this->kantinMenungguPin = null;
        $this->pinInput = '';
    }

    /**
     * Benar-benar mengaktifkan 1 kantin sebagai yang dioperasikan kasir
     * ini -- dipanggil setelah lolos (atau tidak perlu) verifikasi PIN.
     */
    protected function aktifkanKantin(int $kantinId): void
    {
        $this->kantinTerpilih = $kantinId;
        $this->cart = [];
        $this->siswaTerpilih = null;
        $this->modeTunai = false;
        $this->previewProduk = null;

        $this->muatLimitTunai();
    }

    /**
     * Kembali ke layar pilih kantin -- dipakai kalau 1 device dipakai
     * bergantian buat beberapa kantin/outlet berbeda.
     */
    public function gantiKantin(): void
    {
        $this->kantinTerpilih = null;
        $this->kantinMenungguPin = null;
        $this->pinInput = '';
        $this->cart = [];
        $this->siswaTerpilih = null;
        $this->modeTunai = false;
        $this->previewProduk = null;
    }

    /**
     * Hitung ulang limit & pemakaian transaksi tunai hari ini DI KANTIN
     * INI -- dipanggil di mount()/pilihKantin() dan lagi setiap habis
     * checkout, supaya angkanya selalu akurat tanpa refresh manual.
     */
    protected function muatLimitTunai(): void
    {
        if (! $this->kantinTerpilih) {
            $this->limitTunaiHarian = null;
            $this->tunaiTerpakaiHariIni = 0;
            return;
        }

        $this->limitTunaiHarian = Kantin::whereKey($this->kantinTerpilih)
            ->value('limit_tunai_kantin_harian');

        if ($this->limitTunaiHarian === null) {
            $this->tunaiTerpakaiHariIni = 0;
            return;
        }

        $this->tunaiTerpakaiHariIni = KantinTransaksi::withoutGlobalScopes()
            ->where('kantin_id', $this->kantinTerpilih)
            ->where('metode', 'tunai')
            ->whereDate('tanggal', today())
            ->count();
    }

    protected function limitTunaiTercapai(): bool
    {
        return $this->limitTunaiHarian !== null
            && $this->tunaiTerpakaiHariIni >= $this->limitTunaiHarian;
    }

    /**
     * Dipanggil dari JS (html5-qrcode) tiap kali kamera berhasil baca
     * kode — dicoba dulu sebagai siswa, kalau tidak ketemu baru dicoba
     * sebagai produk. Guru/staf/pengunjung tidak perlu kartu -- pakai
     * tombol "Transaksi Tanpa Kartu (Tunai)".
     */
    public function handleScan(string $code): void
    {
        $code = trim($code);

        if ($code === '' || ! $this->kantinTerpilih) {
            return;
        }

        // Siswa dicari lintas lembaga (dalam 1 yayasan yang sama --
        // tenant scope global bawaan model yang menangani ini), BUKAN
        // dibatasi ke kantin tempat kasir ini berada -- siswa dari
        // lembaga manapun dalam yayasan yang sama boleh belanja di
        // kantin manapun.

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

        $produk = KantinProduk::where('kantin_id', $this->kantinTerpilih)
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
        $this->modeTunai = false;

        $terpakaiHariIni = KantinTransaksi::withoutGlobalScopes()
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

    public function gantiSiswa(): void
    {
        $this->siswaTerpilih = null;
        $this->modeTunai = false;
        $this->cart = [];
        $this->previewProduk = null;
    }

    /**
     * Mulai transaksi tunai tanpa kartu — dipakai untuk siapapun yang
     * bukan siswa (guru, staf, pengunjung). Tidak perlu scan kartu sama
     * sekali, langsung tunai, kena limit tunai harian kantin ini.
     */
    public function mulaiTransaksiTunai(): void
    {
        $this->muatLimitTunai();

        if ($this->limitTunaiTercapai()) {
            Notification::make()
                ->title('Limit transaksi tunai hari ini sudah tercapai')
                ->body("Sudah {$this->tunaiTerpakaiHariIni} dari {$this->limitTunaiHarian} transaksi tunai hari ini di kantin ini. Siswa tetap bisa pakai wallet seperti biasa.")
                ->warning()
                ->send();
            return;
        }

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
        if (! $this->kantinTerpilih) {
            Notification::make()->title('Pilih kantin dulu.')->warning()->send();
            return;
        }

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

            // Siswa -> bayar wallet, diatribusikan ke lembaga siswa.
            // Selain siswa (guru/staf/pengunjung) -> selalu tunai, tidak
            // diatribusikan ke lembaga manapun, dan kena limit tunai
            // harian kantin ini.
            if ($this->siswaTerpilih) {
                $metode = 'wallet';
                $siswaId = $this->siswaTerpilih['id'];
                $pegawaiId = null;
                $lembagaId = $this->siswaTerpilih['lembaga_id'];
                $saldoSebelum = $this->siswaTerpilih['saldo'];
            } else {
                $metode = 'tunai';
                $siswaId = null;
                $pegawaiId = null;
                $lembagaId = null;
                $saldoSebelum = null;

                // Jaring pengaman terakhir: cek ulang limit persis sebelum
                // checkout diproses (bukan cuma waktu tombol ditekan),
                // supaya tidak kebobolan kalau ada 2 sesi kasir jalan
                // bersamaan di kantin yang sama.
                $this->muatLimitTunai();

                if ($this->limitTunaiTercapai()) {
                    Notification::make()
                        ->title('Limit transaksi tunai hari ini sudah tercapai')
                        ->body('Transaksi ini tidak bisa diproses tunai.')
                        ->danger()
                        ->send();
                    return;
                }
            }

            $trx = app(KantinService::class)->checkout(
                $lembagaId,
                $this->kantinTerpilih,
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
