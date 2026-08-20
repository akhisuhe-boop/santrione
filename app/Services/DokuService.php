<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Integrasi DOKU -- Checkout/Direct API (untuk terima pembayaran VA/QRIS/
 * e-wallet) + Sub-Account (untuk routing dana ke Lembaga & split fee Qinara).
 *
 * PENTING -- BACA SEBELUM DEPLOY KE PRODUCTION (mengikuti konvensi yang
 * sudah dipakai di XenditService/MidtransService sebelumnya di aplikasi
 * ini): kode ini dibangun mengikuti struktur endpoint & algoritma
 * signature yang DIDOKUMENTASIKAN RESMI oleh DOKU (dicek langsung dari
 * developers.doku.com & dashboard.doku.com/docs, bukan asumsi), TAPI
 * belum pernah dijalankan sungguhan ke sandbox DOKU (lingkungan
 * development tidak ada akses jaringan ke domain itu).
 *
 * Dua hal WAJIB diverifikasi ke tim onboarding/sales DOKU begitu kredensial
 * sandbox didapat, sebelum go-live:
 *
 * 1. Endpoint "Create Payment" (Checkout/Direct API) di bawah pakai skema
 *    signature Non-SNAP (Client-Id + Secret-Key + HMAC-SHA256) -- ini
 *    skema yang paling terdokumentasi lengkap (lihat generateSignature()),
 *    termasuk contoh kode PHP resmi dari DOKU yang jadi rujukan langsung
 *    method ini.
 * 2. Endpoint "Register Sub Account V2" (registerSubAccount() di bawah)
 *    di dokumentasi terbaru DOKU memakai skema SNAP (X-PARTNER-ID,
 *    X-TIMESTAMP, X-SIGNATURE, X-EXTERNAL-ID, Authorization: Bearer
 *    <token OAuth>) yang BEDA dari skema Non-SNAP di atas -- perlu
 *    konfirmasi ke DOKU apakah akun Qinara diarahkan ke Sub-Account V1
 *    (Non-SNAP, searah dengan Checkout) atau V2 (SNAP, perlu tambahan
 *    endpoint OAuth token dulu). SELAMA proses aktivasi Sub-Account oleh
 *    sales DOKU belum selesai, method registerSubAccount() TIDAK akan
 *    berfungsi -- tandai TODO ini di tracker kalian.
 *
 * Endpoint & payload merujuk dokumentasi resmi:
 * https://developers.doku.com/ (Get Started -> Accept Payments -> DOKU Checkout / Direct API)
 * https://developers.doku.com/getting-started-with-doku-api/signature-component/non-snap
 * https://docs.doku.com/wallet-as-a-service/sub-account
 */
class DokuService
{
    protected function clientId(): string
    {
        $id = config('services.doku.client_id');

        if (blank($id)) {
            throw new RuntimeException('DOKU_CLIENT_ID belum diisi di .env');
        }

        return $id;
    }

    protected function secretKey(): string
    {
        $key = config('services.doku.secret_key');

        if (blank($key)) {
            throw new RuntimeException('DOKU_SECRET_KEY belum diisi di .env');
        }

        return $key;
    }

    protected function baseUrl(): string
    {
        return config('services.doku.is_production')
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Implementasi PERSIS algoritma "Generate Signature" (Non-SNAP) dari
     * dokumentasi resmi DOKU -- lihat contoh kode PHP resmi mereka di
     * https://developers.doku.com/getting-started-with-doku-api/signature-component/non-snap/best-practice
     *
     * Digest = base64(sha256(raw JSON body)).
     * Komponen signature disusun baris-per-baris dengan "\n" (TANPA "\n"
     * di akhir), lalu di-HMAC-SHA256 pakai Secret Key, di-base64, dan
     * diberi prefix "HMACSHA256=".
     */
    protected function generateDigest(string $rawBody): string
    {
        return base64_encode(hash('sha256', $rawBody, true));
    }

    protected function generateSignature(
        string $requestId,
        string $requestTimestamp,
        string $requestTarget,
        ?string $digest
    ): string {
        $componentSignature = "Client-Id:{$this->clientId()}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$requestTimestamp}";

        // Request GET/DELETE tanpa body tidak menyertakan baris Digest
        // (sesuai dokumentasi resmi -- lihat catatan "If body not send
        // when access API with HTTP method GET/DELETE").
        if ($digest !== null) {
            $componentSignature .= "\nRequest-Target:{$requestTarget}\nDigest:{$digest}";
        } else {
            $componentSignature .= "\nRequest-Target:{$requestTarget}";
        }

        $hmac = base64_encode(hash_hmac('sha256', $componentSignature, $this->secretKey(), true));

        return 'HMACSHA256=' . $hmac;
    }

    protected function requestTimestamp(): string
    {
        // Format wajib DOKU: ISO 8601 dengan "Z" (UTC), contoh
        // 2020-08-11T08:45:42Z -- lihat sample resmi di dokumentasi.
        return now('UTC')->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * POST helper yang otomatis menghitung Digest + Signature sesuai
     * skema Non-SNAP resmi DOKU, untuk endpoint Checkout/Direct API.
     */
    protected function post(string $path, array $body): array
    {
        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $requestId = (string) Str::uuid();
        $timestamp = $this->requestTimestamp();
        $digest = $this->generateDigest($rawBody);
        $signature = $this->generateSignature($requestId, $timestamp, $path, $digest);

        $response = Http::withHeaders([
            'Client-Id' => $this->clientId(),
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->withBody($rawBody, 'application/json')
            ->post($this->baseUrl() . $path);

        if ($response->failed()) {
            Log::error('DokuService: request gagal', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal memanggil DOKU API (' . $path . '): ' . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Daftarkan Lembaga sebagai Sub-Account DOKU (Standard type -- Lembaga
     * bisa login DOKU Dashboard sendiri untuk lihat saldo/riwayat, sesuai
     * rekomendasi DOKU untuk merchant yang ingin transparansi ke pihak
     * ketiga -- cocok untuk kebutuhan trust yayasan/pesantren).
     *
     * PERLU DIVERIFIKASI (lihat catatan class di atas): endpoint & skema
     * auth Sub-Account V2 register kemungkinan SNAP, bukan Non-SNAP
     * seperti method post() di atas -- method ini masih pakai pola yang
     * sama untuk konsistensi, SESUAIKAN begitu tim DOKU konfirmasi skema
     * yang berlaku untuk akun Qinara.
     */
    public function registerSubAccount(\App\Models\Lembaga $lembaga): array
    {
        $result = $this->post('/sub-account/v2.0/register', [
            'account' => [
                'email' => $lembaga->yayasan?->email ?? "lembaga{$lembaga->id}@qinaraindonesia.id",
                'type' => 'STANDARD',
                'name' => $lembaga->nama,
            ],
        ]);

        $lembaga->update([
            'doku_sub_account_id' => $result['account']['id'] ?? $result['id'] ?? null,
            'doku_status' => 'menunggu_verifikasi',
            'payment_gateway' => 'doku',
        ]);

        return $result;
    }

    /**
     * Buat payment request (Checkout API) untuk 1 tagihan wali murid,
     * dengan dana dirutekan ke Sub-Account Lembaga via
     * `additional_info.account.id` -- mekanisme routing sub-account yang
     * didokumentasikan resmi DOKU untuk skema "Accept Payment".
     *
     * $channel: 'VA' (pilih $bank, mis. BCA/BNI/BRI/MANDIRI/BSI) atau
     * 'QRIS' atau 'EWALLET' ($bank diisi kode e-wallet: OVO/DANA/SHOPEEPAY/
     * LINKAJA).
     *
     * Split fee Qinara: DOKU merutekan NOMINAL PENUH ke sub-account
     * Lembaga terlebih dulu (bukan split di titik pembayaran seperti
     * Xendit) -- fee Qinara ditarik BELAKANGAN lewat Split Rule /
     * Debit API dari saldo sub-account (lihat catatan di
     * `splitFeeDariSubAccount()` di bawah). Ini beda arsitektur dari
     * XenditService (yang split langsung saat payment_request dibuat) --
     * WAJIB dikonfirmasi ke tim DOKU apakah "Split Rules" bisa
     * dikonfigurasi untuk potong otomatis di titik masuk (mirip Xendit),
     * atau memang harus 2 langkah (masuk penuh -> baru ditarik). Kalau
     * ternyata bisa 1 langkah, method ini perlu ditambah parameter
     * 'split_rule' di payload sesuai dokumentasi yang dikonfirmasi.
     */
    public function buatPaymentRequest(
        string $referenceId,
        int $amount,
        string $customerName,
        string $customerEmail,
        string $judul,
        string $channel = 'QRIS',
        ?string $bank = null,
        ?string $dokuSubAccountId = null,
        ?string $notificationPath = null
    ): array {
        $order = [
            'invoice_number' => $referenceId,
            'amount' => $amount,
            'callback_url' => url('/wali/keuangan'),
        ];

        $body = [
            'order' => $order,
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
            ],
        ];

        // Routing ke Sub-Account Lembaga -- lihat catatan method di atas
        // soal split fee.
        if ($dokuSubAccountId) {
            $body['additional_info'] = [
                'account' => ['id' => $dokuSubAccountId],
            ];
        }

        $path = match (strtoupper($channel)) {
            'VA' => '/doku-virtual-account/v2/payment-code',
            'QRIS' => '/checkout/v1/payment', // QRIS dinamis lewat DOKU Checkout, redirect page menampilkan QR
            'EWALLET' => '/checkout/v1/payment',
            default => '/checkout/v1/payment',
        };

        if (strtoupper($channel) === 'VA' && $bank) {
            $body['virtual_account_info'] = [
                'expired_time' => 60,
                'reusable_status' => false,
                'info1' => 'Qinara - ' . $judul,
            ];
            $body['virtual_account_bank'] = $bank; // sesuaikan nama field persis dari respons sandbox pertama
        }

        $result = $this->post($path, $body);

        Log::info('DokuService::buatPaymentRequest sukses', [
            'reference' => $referenceId,
            'channel' => $channel,
        ]);

        return $result;
    }

    /**
     * Verifikasi signature notifikasi (webhook) DOKU -- implementasi
     * PERSIS contoh resmi DOKU (Best Practice -- HTTP Notification),
     * termasuk perhitungan Digest dari raw notification body.
     */
    public function verifyNotificationSignature(
        array $headers,
        string $rawBody,
        string $notificationPath
    ): bool {
        $clientId = $headers['client-id'] ?? $headers['Client-Id'] ?? null;
        $requestId = $headers['request-id'] ?? $headers['Request-Id'] ?? null;
        $timestamp = $headers['request-timestamp'] ?? $headers['Request-Timestamp'] ?? null;
        $signature = $headers['signature'] ?? $headers['Signature'] ?? null;

        if (! $clientId || ! $requestId || ! $timestamp || ! $signature) {
            return false;
        }

        $digest = $this->generateDigest($rawBody);

        $raw = "Client-Id:{$clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$timestamp}\n"
            . "Request-Target:{$notificationPath}\n"
            . "Digest:{$digest}";

        $expected = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $raw, $this->secretKey(), true));

        return hash_equals($expected, $signature);
    }
}
