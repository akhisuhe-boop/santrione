<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Kas;
use App\Models\KategoriKas;
use App\Models\Rekening;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class WalletService
{
    // =========================
    // 🔧 HELPER KATEGORI (FINAL)
    // =========================
    protected function getKategori($nama, $tipe)
    {
        $kode = $tipe . '_' . Str::slug($nama, '_');

        $kategori = KategoriKas::where('kode', $kode)
            ->where('is_active', true)
            ->first();

        if (!$kategori) {

            // Sebelumnya method ini langsung nge-block (throw
            // ValidationException) kalau kategori spesifik (nama+tipe)
            // belum ada — masalahnya, kategori seperti "Penyesuaian
            // Saldo" cuma pernah dibuat untuk 1 arah (mis. 'keluar'
            // saja), jadi begitu ada penyesuaian ke arah SEBALIKNYA
            // (mis. saldo dinaikkan, butuh versi 'masuk'), user selalu
            // ketemu error meski tidak salah apa-apa. Sekarang
            // auto-create kategorinya (sama seperti pola yang dipakai
            // fitur e-Kantin), supaya operasional tidak macet.
            Log::info("Kategori kas belum ada, membuat otomatis", [
                'nama' => $nama,
                'kode' => $kode,
            ]);

            $kategori = KategoriKas::create([
                'nama' => $nama,
                'tipe' => $tipe,
                'is_active' => true,
            ]);
        }

        return $kategori->id;
    }

    // =========================
    // 🔧 HELPER REKENING
    // =========================
    protected function getRekeningWallet($lembagaId = null)
    {
        $query = Rekening::where('tipe', 'ewallet')
            ->where('is_active', true);

        if ($lembagaId) {
            $query->where('lembaga_id', $lembagaId);
        }

        $rekening = $query->first();

        if (!$rekening) {

            \Log::warning('Rekening e-wallet belum tersedia', ['lembaga_id' => $lembagaId]);

            \Filament\Notifications\Notification::make()
                ->title('Rekening Belum Disetting')
                ->body('Rekening E-Wallet belum dibuat/aktif untuk lembaga ini. Silakan tambahkan di menu Keuangan → Rekening.')
                ->danger()
                ->send();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'rekening' => 'Rekening E-Wallet belum tersedia untuk lembaga ini.',
            ]);
        }

        return $rekening->id;
    }

    // =========================
    // 🧠 HELPER SISWA INFO
    // =========================
    protected function getSiswaInfo($wallet)
    {
        $nama = $wallet->siswa->nama_lengkap ?? 'Siswa';

        $kelas = $wallet->siswa->kelas->nama
            ?? $wallet->siswa->kelas_nama
            ?? '-';

        return [
            'nama' => $nama,
            'kelas' => $kelas,
        ];
    }

    // =========================
    // ✏️ PENYESUAIAN SALDO MANUAL (dari halaman Edit Wallet)
    // =========================
    // PENTING: method ini yang MENGUBAH saldo (bukan Filament secara
    // langsung) — supaya perubahan saldo dan jejak pencatatannya
    // (WalletTransaction + Kas) SELALU atomic dalam 1 transaksi
    // database: kalau pencatatan gagal karena sebab apapun, saldo
    // ikut batal berubah juga (tidak pernah nyangkut di tengah
    // seperti yang sempat terjadi sebelumnya).
    public function applyAdjustment($wallet, $saldoBaru)
    {
        return DB::transaction(function () use ($wallet, $saldoBaru) {

            $wallet = Wallet::lockForUpdate()
                ->with('siswa.kelas')
                ->findOrFail($wallet->id);

            $diff = $saldoBaru - $wallet->saldo;

            if ($diff == 0) {
                return $wallet;
            }

            $siswa = $this->getSiswaInfo($wallet);

            $wallet->update(['saldo' => $saldoBaru]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'adjustment',
                'amount' => abs($diff),
                'status' => 'success',
                'description' => 'Penyesuaian saldo manual oleh admin (' . ($diff > 0 ? '+' : '-') . 'Rp ' . number_format(abs($diff), 0, ',', '.') . ')',
            ]);

            Kas::create([
                'tipe' => $diff > 0 ? 'masuk' : 'keluar',
                'kategori_id' => $this->getKategori('Penyesuaian Saldo', $diff > 0 ? 'masuk' : 'keluar'),
                'rekening_id' => $this->getRekeningWallet($wallet->siswa->lembaga_id ?? null),
                'nominal' => abs($diff),
                'sumber' => 'wallet',
                'tanggal' => now(),
                'keterangan' => 'Penyesuaian saldo - ' . $siswa['nama'] . ' - ' . $siswa['kelas'],
                'penanggung_jawab' => $siswa['nama'] . ' - ' . $siswa['kelas'],
                'lembaga_id' => $wallet->siswa->lembaga_id ?? null,
                'diinput_oleh' => auth()->user()->name ?? null,
            ]);

            return $wallet->fresh();
        });
    }

    /**
     * @deprecated Dipertahankan supaya kode lama yang masih manggil
     * logAdjustment() tidak fatal error — tapi method ini TIDAK LAGI
     * dipakai oleh alur Edit Wallet (lihat applyAdjustment() di atas).
     */
    public function logAdjustment($wallet, $diff)
    {
        if ($diff == 0) return;

        $this->applyAdjustment($wallet, $wallet->saldo + $diff);
    }

    // =========================
    // 💰 TOP UP WALLET
    // =========================
    public function topUp($wallet, $amount)
    {
        DB::transaction(function () use ($wallet, $amount) {

            $wallet = Wallet::lockForUpdate()
                ->with('siswa.kelas')
                ->find($wallet->id);

            if (!$wallet) {
                throw new \Exception('Wallet tidak ditemukan');
            }

            // 1. Tambah saldo
            $wallet->increment('saldo', $amount);

            // 2. Ambil data siswa
            $siswa = $this->getSiswaInfo($wallet);

            // 3. Catat transaksi wallet
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'topup',
                'amount' => $amount,
                'status' => 'success',
                'description' => 'Top up saldo',
            ]);

            // 4. Catat ke kas (MASUK)
            Kas::create([
                'tipe' => 'masuk',
                'kategori_id' => $this->getKategori('Top Up', 'masuk'),
                'rekening_id' => $this->getRekeningWallet($wallet->siswa->lembaga_id ?? null),
                'nominal' => $amount,
                'sumber' => 'wallet',
                'tanggal' => now(),
                'keterangan' => $siswa['nama'] . ' - ' . $siswa['kelas'],
                'penanggung_jawab' => $siswa['nama'] . ' - ' . $siswa['kelas'],
                'lembaga_id' => $wallet->siswa->lembaga_id ?? null,
                'diinput_oleh' => auth()->user()->name ?? null,
            ]);
        });
    }

    // =========================
    // 💸 APPROVE WITHDRAW
    // =========================
    public function approveWithdraw($withdraw)
    {
        DB::beginTransaction();

        try {

            $wallet = Wallet::lockForUpdate()
                ->with('siswa.kelas')
                ->find($withdraw->wallet_id);

            if (!$wallet) {
                throw new \Exception('Wallet tidak ditemukan');
            }

            // 1. Validasi saldo
            if ($wallet->saldo < $withdraw->amount) {
                throw new \Exception('Saldo tidak cukup');
            }

            // 2. Ambil data siswa
            $siswa = $this->getSiswaInfo($wallet);

            // 3. Kurangi saldo
            $wallet->decrement('saldo', $withdraw->amount);

            // 4. Catat transaksi wallet
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw',
                'amount' => $withdraw->amount,
                'status' => 'success',
                'description' => 'Penarikan saldo',
            ]);

            // 5. Update withdraw → SUCCESS
            $withdraw->update([
                'status' => 'approved',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // 6. Catat ke kas (KELUAR)
            Kas::create([
                'tipe' => 'keluar',
                'kategori_id' => $this->getKategori('Penarikan Saldo', 'keluar'),
                'rekening_id' => $this->getRekeningWallet($wallet->siswa->lembaga_id ?? null),
                'nominal' => $withdraw->amount,
                'sumber' => 'wallet',
                'tanggal' => now(),
                'keterangan' => 'Penarikan - ' . $siswa['nama'] . ' - ' . $siswa['kelas'],
                'penanggung_jawab' => $siswa['nama'] . ' - ' . $siswa['kelas'],
                'lembaga_id' => $wallet->siswa->lembaga_id ?? null,
                'diinput_oleh' => auth()->user()->name ?? null,
            ]);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            // 🔥 INI YANG PENTING BANGET
            $withdraw->update([
                'status' => 'failed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'catatan_admin' => $e->getMessage(),
            ]);

            throw $e; // optional (biar notif muncul)
        }
    }
    
}