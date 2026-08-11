<?php

namespace App\Services;

use App\Models\Lembaga;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi Xendit for Platforms (xenPlatform) -- split payment,
 * sub-account per Lembaga, QRIS sebagai channel default (fee paling
 * murah untuk nominal kecil, dibanding VA yang flat Rp4-6rb).
 *
 * PENTING -- BACA SEBELUM DEPLOY KE PRODUCTION:
 * Kode ini dibangun mengikuti struktur endpoint/payload yang
 * didokumentasikan Xendit, TAPI belum pernah dijalankan sungguhan ke
 * api.xendit.co (lingkungan development tidak punya akses jaringan
 * ke domain itu). WAJIB diuji di sandbox Xendit dulu -- kalau ada
 * field/response yang beda dari yang diasumsikan di sini, itu wajar,
 * laporkan pesan error API-nya supaya bisa disesuaikan.
 *
 * Endpoint & payload merujuk dokumentasi resmi:
 * https://developers.xendit.co/api-reference/#xenplatform
 */
class XenditService
{
    protected function secretKey(): string
    {
        $key = config('services.xendit.secret_key');

        if (! $key) {
            throw new \RuntimeException('XENDIT_SECRET_KEY belum diisi di .env');
        }

        return $key;
    }

    protected function authHeader(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey() . ':'),
        ];
    }

    /**
     * Daftarkan Lembaga sebagai sub-account (Account Holder) di
     * Xendit. Dipanggil sekali saat onboarding -- hasilnya
     * xendit_account_holder_id disimpan di Lembaga, dipakai terus
     * untuk semua transaksi split berikutnya.
     */
    public function daftarkanSubAccount(Lembaga $lembaga): array
    {
        $response = Http::withHeaders($this->authHeader())
            ->post('https://api.xendit.co/v2/account_holders', [
                'type' => 'MANAGED',
                'email' => $lembaga->yayasan?->email ?? "lembaga{$lembaga->id}@qinaraindonesia.id",
                'country' => 'ID',
                'business_profile' => [
                    'business_name' => $lembaga->nama,
                    'business_type' => 'EDUCATION',
                ],
            ]);

        if ($response->failed()) {
            Log::error('XenditService::daftarkanSubAccount gagal', [
                'lembaga_id' => $lembaga->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Gagal mendaftarkan sub-account Xendit: ' . $response->body());
        }

        $result = $response->json();

        $lembaga->update([
            'xendit_account_holder_id' => $result['id'] ?? null,
            'xendit_status' => 'menunggu_verifikasi',
        ]);

        return $result;
    }

    /**
     * Buat payment request (QRIS default) untuk 1 Tagihan, dengan
     * split rule: porsi Lembaga (sub-account) + porsi fee Qinara
     * (akun utama), sesuai fee_persen di config.
     */
    public function buatPaymentRequest(Tagihan $tagihan, Lembaga $lembaga, string $channel = 'QRIS'): array
    {
        if (! $lembaga->xendit_account_holder_id) {
            throw new \RuntimeException("Lembaga {$lembaga->nama} belum terdaftar sebagai sub-account Xendit.");
        }

        $amount = (int) ($tagihan->nominal - $tagihan->nominal_terbayar);
        $feePersen = (float) config('services.xendit.fee_persen', 1.0);
        $feeQinara = (int) round($amount * $feePersen / 100);
        $porsiLembaga = $amount - $feeQinara;

        $referenceId = 'TAGIHAN-' . $tagihan->id . '-' . time();

        $payload = [
            'reference_id' => $referenceId,
            'amount' => $amount,
            'currency' => 'IDR',
            'country' => 'ID',
            'payment_method' => [
                'type' => 'QR_CODE',
                'reusability' => 'ONE_TIME_USE',
                'qr_code' => [
                    'channel_code' => 'DANA', // QRIS dinamis lintas-channel di Xendit direpresentasikan lewat QR_CODE payment method
                ],
            ],
            'routes' => [
                [
                    'destination_account_id' => $lembaga->xendit_account_holder_id,
                    'amount' => $porsiLembaga,
                ],
                [
                    'destination_account_id' => config('services.xendit.main_account_id'),
                    'amount' => $feeQinara,
                ],
            ],
        ];

        $response = Http::withHeaders($this->authHeader())
            ->post('https://api.xendit.co/payment_requests', $payload);

        if ($response->failed()) {
            Log::error('XenditService::buatPaymentRequest gagal', [
                'tagihan_id' => $tagihan->id,
                'lembaga_id' => $lembaga->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Gagal membuat payment request Xendit: ' . $response->body());
        }

        $result = $response->json();

        \App\Models\Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'siswa_id' => $tagihan->siswa_id,
            'nominal' => $amount,
            'metode' => 'xendit',
            'status' => 'pending',
            'reference' => $referenceId,
        ]);

        return $result;
    }
}
