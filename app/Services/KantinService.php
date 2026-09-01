<?php

namespace App\Services;

use App\Models\Kas;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiItem;
use App\Models\KategoriKas;
use App\Models\Rekening;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KantinService
{
    /**
     * @param  ?int  $lembagaId  Lembaga yang jadi pemilik transaksi ini
     *                           untuk keperluan pelaporan kas -- BUKAN
     *                           lembaga tempat kasir/produknya berada,
     *                           tapi lembaga milik SI PEMBELI (siswa atau
     *                           pegawai). Null untuk pengunjung umum
     *                           (tidak diatribusikan ke lembaga manapun).
     * @param  ?int  $siswaId  Diisi kalau pembeli siswa (bayar wallet).
     * @param  ?int  $pegawaiId  Diisi kalau pembeli guru/staf -- SELALU
     *                           bayar tunai (fitur wallet pegawai
     *                           ditunda), cuma buat atribusi identitas &
     *                           laporan. Siswa dan pegawai tidak pernah
     *                           diisi berbarengan.
     * @param  array<int, array{produk_id: int, qty: int}>  $items
     */
    public function checkout(
        ?int $lembagaId,
        ?int $siswaId,
        ?int $pegawaiId,
        string $metode, // 'wallet' | 'tunai'
        array $items,
        ?int $kasirId = null
    ): KantinTransaksi {

        return DB::transaction(function () use ($lembagaId, $siswaId, $pegawaiId, $metode, $items, $kasirId) {

            $produkIds = collect($items)->pluck('produk_id');

            $produkList = KantinProduk::whereIn('id', $produkIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $lineItems = [];

            foreach ($items as $item) {

                $produk = $produkList->get($item['produk_id']);

                if (! $produk) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }

                if (! $produk->is_active) {
                    throw ValidationException::withMessages(['items' => "{$produk->nama} sedang tidak tersedia."]);
                }

                $qty = max(1, (int) $item['qty']);

                if ($produk->stok !== null && $produk->stok < $qty) {
                    throw ValidationException::withMessages(['items' => "Stok {$produk->nama} tidak cukup."]);
                }

                $subtotal = $produk->harga * $qty;
                $total += $subtotal;

                $lineItems[] = [
                    'produk' => $produk,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if ($total <= 0) {
                throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
            }

            $wallet = null;

            if ($metode === 'wallet') {

                // Wallet HANYA untuk siswa. Guru/staf & pengunjung selalu
                // tunai (lihat KasirKantin::checkout()) -- fitur wallet
                // pegawai ditunda dulu, belum dipakai.
                if (! $siswaId) {
                    throw ValidationException::withMessages(['siswa' => 'Pembayaran wallet wajib pilih siswa.']);
                }

                $wallet = Wallet::where('siswa_id', $siswaId)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    throw ValidationException::withMessages(['wallet' => 'Siswa ini belum punya wallet.']);
                }

                $this->assertDalamLimitHarian($siswaId, $total);

                if ($wallet->saldo < $total) {
                    throw ValidationException::withMessages(['wallet' => 'Saldo wallet tidak cukup.']);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $trx = KantinTransaksi::create([
                'lembaga_id' => $lembagaId,
                'siswa_id' => $siswaId,
                'pegawai_id' => $pegawaiId,
                'wallet_id' => $wallet?->id,
                'metode' => $metode,
                'total' => $total,
                'kasir_id' => $kasirId,
                'tanggal' => now(),
            ]);

            foreach ($lineItems as $line) {

                KantinTransaksiItem::create([
                    'kantin_transaksi_id' => $trx->id,
                    'kantin_produk_id' => $line['produk']->id,
                    'nama_produk' => $line['produk']->nama,
                    'harga_satuan' => $line['produk']->harga,
                    'qty' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);

                if ($line['produk']->stok !== null) {
                    $line['produk']->decrement('stok', $line['qty']);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | POTONG WALLET (kalau bayar wallet)
            |--------------------------------------------------------------------------
            */

            if ($wallet) {

                $wallet->decrement('saldo', $total);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'kantin',
                    'amount' => $total,
                    'status' => 'success',
                    'description' => 'Belanja Kantin - ' . $trx->kode,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CATAT KE KAS (pemasukan kantin)
            |--------------------------------------------------------------------------
            */

            $penanggungJawab = $trx->siswa?->nama_lengkap
                ?? $trx->pegawai?->nama
                ?? 'Umum (Pengunjung)';

            $kas = Kas::create([
                'tipe' => 'masuk',
                'kategori_id' => $this->getKategoriKantin(),
                'rekening_id' => $this->getRekeningKantin($lembagaId),
                'nominal' => $total,
                'sumber' => 'kantin',
                'tanggal' => now(),
                'keterangan' => 'Penjualan Kantin - ' . $trx->kode,
                'penanggung_jawab' => $penanggungJawab,
                // Pengunjung umum (tanpa siswa/pegawai) tidak diatribusikan
                // ke lembaga manapun -- lembaga_id sengaja dibiarkan null.
                'lembaga_id' => $lembagaId,
                'diinput_oleh' => auth()->user()->name ?? 'Sistem',
            ]);

            $trx->update(['kas_id' => $kas->id]);

            return $trx->load('items');
        });
    }

    /**
     * Cek total belanja kantin siswa HARI INI (via wallet) + transaksi
     * yang mau dibuat ini, tidak boleh melebihi limit_harian_kantin
     * (kalau di-set null oleh wali, berarti tidak dibatasi).
     */
    protected function assertDalamLimitHarian(int $siswaId, int $totalTransaksiIni): void
    {
        $siswa = \App\Models\Siswa::find($siswaId);

        if (! $siswa || $siswa->limit_harian_kantin === null) {
            return;
        }

        $sudahBelanjaHariIni = KantinTransaksi::where('siswa_id', $siswaId)
            ->where('metode', 'wallet')
            ->whereDate('tanggal', today())
            ->sum('total');

        if (($sudahBelanjaHariIni + $totalTransaksiIni) > $siswa->limit_harian_kantin) {

            $sisa = max(0, $siswa->limit_harian_kantin - $sudahBelanjaHariIni);

            throw ValidationException::withMessages([
                'limit' => "Melebihi limit belanja harian. Sisa limit hari ini: Rp " . number_format($sisa, 0, ',', '.'),
            ]);
        }
    }

    protected function getKategoriKantin(): int
    {
        $kategori = KategoriKas::where('kode', 'masuk_penjualan_kantin')
            ->where('is_active', true)
            ->first();

        if (! $kategori) {

            Log::warning('Kategori kas "Penjualan Kantin" belum dibuat, membuat otomatis.');

            $kategori = KategoriKas::create([
                'nama' => 'Penjualan Kantin',
                'tipe' => 'masuk',
                'is_active' => true,
            ]);
        }

        return $kategori->id;
    }

    protected function getRekeningKantin(?int $lembagaId): int
    {
        $rekening = null;

        // Coba dulu rekening e-wallet spesifik lembaga si pembeli.
        if ($lembagaId) {
            $rekening = Rekening::where('tipe', 'ewallet')
                ->where('is_active', true)
                ->where('lembaga_id', $lembagaId)
                ->first();
        }

        // Fallback: tidak ada rekening spesifik lembaga (atau transaksi
        // pengunjung umum yang memang tidak punya lembaga) -- uangnya
        // tetap harus masuk ke rekening e-wallet yang aktif, jadi pakai
        // rekening e-wallet aktif manapun yang ada di yayasan ini.
        if (! $rekening) {
            $rekening = Rekening::where('tipe', 'ewallet')
                ->where('is_active', true)
                ->first();
        }

        if (! $rekening) {
            throw ValidationException::withMessages([
                'rekening' => 'Rekening E-Wallet belum tersedia. Buat dulu di menu Keuangan > Rekening.',
            ]);
        }

        return $rekening->id;
    }
}
