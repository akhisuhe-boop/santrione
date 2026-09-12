<?php

namespace App\Observers;

use App\Models\QinaraKasKategori;
use App\Models\QinaraKasTransaksi;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Log;

/**
 * DITAMBAHKAN -- setiap SubscriptionPayment (pembayaran client) yang
 * BERHASIL, otomatis dicatat sebagai Kas Masuk di pembukuan internal
 * Qinara. Dipasang sebagai Observer (bukan ditaruh di masing-masing
 * webhook/action) supaya SEMUA jalur yang bisa menandai pembayaran
 * "berhasil" tertangkap otomatis -- baik lewat webhook DOKU, webhook
 * Xendit, MAUPUN verifikasi manual transfer bank di panel Platform --
 * tanpa perlu mengubah/menduplikasi logic di 3 tempat itu.
 *
 * PENTING -- method di sini SENGAJA dibungkus try/catch dan tidak
 * pernah melempar exception ke pemanggil: kalau pencatatan kas gagal
 * karena sebab apa pun, itu TIDAK BOLEH menggagalkan/membatalkan
 * konfirmasi pembayaran client yang sebenarnya (itu jauh lebih
 * kritis). Error di sini hanya dicatat ke log.
 */
class SubscriptionPaymentObserver
{
    public function created(SubscriptionPayment $payment): void
    {
        if ($payment->status === 'berhasil') {
            $this->catatKasMasuk($payment);
        }
    }

    public function updated(SubscriptionPayment $payment): void
    {
        if ($payment->wasChanged('status') && $payment->status === 'berhasil') {
            $this->catatKasMasuk($payment);
        }
    }

    private function catatKasMasuk(SubscriptionPayment $payment): void
    {
        try {
            // Idempotent -- kalau sudah pernah tercatat (observer
            // somehow terpanggil 2x, atau retry webhook), jangan
            // dobel. Unique constraint di DB jadi jaring pengaman
            // kedua kalau ada race condition.
            if (QinaraKasTransaksi::where('subscription_payment_id', $payment->id)->exists()) {
                return;
            }

            $kategori = QinaraKasKategori::firstOrCreate(
                ['nama' => 'Pembayaran Langganan (Otomatis)'],
                ['tipe' => 'masuk', 'aktif' => true]
            );

            $yayasanNama = $payment->subscription?->yayasan?->nama ?? '-';

            QinaraKasTransaksi::create([
                'tanggal' => $payment->diverifikasi_pada ?? $payment->updated_at ?? now(),
                'tipe' => 'masuk',
                'kategori_id' => $kategori->id,
                'subscription_payment_id' => $payment->id,
                'nominal' => $payment->jumlah,
                'keterangan' => "Otomatis dari pembayaran #{$payment->id} — {$yayasanNama} (metode: {$payment->metode})",
            ]);
        } catch (\Throwable $e) {
            Log::error('QinaraKasTransaksi: gagal mencatat kas masuk otomatis dari pembayaran', [
                'subscription_payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
