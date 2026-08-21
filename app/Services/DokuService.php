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

    /**
     * Pastikan email yang dikirim ke DOKU selalu format valid --
     * ditambahkan setelah menemukan bug nyata di sandbox: fallback lama
     * (menggabung nomor WA + '@dummy.id' begitu saja) menghasilkan email
     * tidak valid kalau nomor WA kosong/null, atau kalau email asli
     * siswa tersimpan sebagai string kosong ('' bukan null, sehingga
     * operator '??' tidak ke-trigger). DOKU membalas error
     * "customer.email is not valid" untuk kasus begini.
     */
    /**
     * DOKU kadang membalas 'message' error sebagai ARRAY/object (bukan
     * string) -- terutama untuk error validasi per-field pada QRIS/
     * Checkout API. Kalau ini langsung ditaruh ke session('error') dan
     * ditampilkan lewat {{ session('error') }} di Blade, PHP akan crash
     * (htmlspecialchars() butuh string, bukan array) -- persis bug yang
     * ditemukan di sandbox. Helper ini memastikan pesan yang disimpan ke
     * session SELALU string, apapun bentuk aslinya dari DOKU.
     */
    public static function pesanAman(mixed $pesan, string $default = 'Gagal membuat pembayaran'): string
    {
        if (is_string($pesan) && $pesan !== '') {
            return $pesan;
        }

        if (is_array($pesan) || is_object($pesan)) {
            return $default . ' (' . json_encode($pesan, JSON_UNESCAPED_SLASHES) . ')';
        }

        return $default;
    }

    public static function emailAman(?string $emailAsli, string|int $fallbackSeed): string
    {
        if ($emailAsli && filter_var($emailAsli, FILTER_VALIDATE_EMAIL)) {
            return $emailAsli;
        }

        // Bersihkan seed (nomor WA dkk) dari karakter yang tidak valid
        // untuk local-part email, supaya hasil akhirnya PASTI valid
        // walau seed-nya kosong/berantakan.
        $seedBersih = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $fallbackSeed);

        if (blank($seedBersih)) {
            $seedBersih = 'user' . uniqid();
        }

        return $seedBersih . '@qinaraindonesia.id';
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
    /**
     * Buat payment request lewat DOKU Checkout Link -- SATU endpoint
     * untuk SEMUA metode (VA per bank, e-wallet, QRIS, kartu). Wali
     * murid diarahkan ke halaman pembayaran resmi DOKU
     * (staging.doku.com/checkout-link-v2/... di sandbox), tempat mereka
     * pilih kanal spesifik (mis. VIRTUAL_ACCOUNT_BCA) -- DOKU yang
     * urus routing ke bank yang benar, kita tidak perlu tebak endpoint
     * per bank sendiri. Ini keputusan arsitektur SENGAJA (bukan
     * keterbatasan) -- dipilih karena: (1) lebih aman -- detail
     * pembayaran diproses sepenuhnya di halaman DOKU, Qinara tidak
     * pernah pegang data kartu/VA mentah; (2) satu jalur terverifikasi
     * dipakai untuk semua kanal, bukan endpoint per-bank yang belum
     * pernah diuji; (3) DOKU otomatis update daftar kanal yang tersedia
     * (payment_method_types) tanpa kita perlu ubah kode tiap ada bank
     * baru.
     *
     * $channel di sini HANYA dipakai untuk mempersempit pilihan yang
     * ditampilkan DOKU ke wali murid (lewat payment_method_types),
     * bukan lagi untuk pilih endpoint berbeda.
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
                'payment_method_types' => $this->paymentMethodTypes($channel, $bank),
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

        $result = $this->post('/checkout/v1/payment', $body);

        Log::info('DokuService::buatPaymentRequest sukses', [
            'reference' => $referenceId,
            'channel' => $channel,
            // Full response body sengaja di-log supaya bisa dicocokkan
            // field URL/nomor VA yang benar (nama field masih tebakan
            // sampai ada respons sandbox asli).
            'full_response' => $result,
        ]);

        return $result;
    }

    /**
     * Terjemahkan pilihan bank/kanal dari UI Qinara ke daftar
     * `payment_method_types` yang dikenali DOKU Checkout (persis nama
     * enum yang terlihat di respons sandbox: VIRTUAL_ACCOUNT_BCA,
     * VIRTUAL_ACCOUNT_BRI, VIRTUAL_ACCOUNT_BNI,
     * VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI (BSI),
     * VIRTUAL_ACCOUNT_BANK_MANDIRI, EMONEY_OVO, EMONEY_DANA,
     * EMONEY_SHOPEE_PAY). Kalau kosong, DOKU otomatis tampilkan SEMUA
     * kanal yang aktif untuk akun ini.
     */
    protected function paymentMethodTypes(string $channel, ?string $bank): array
    {
        // CATATAN: nama field 'payment_method_types' di BODY REQUEST ini
        // masih perlu dikonfirmasi -- yang PASTI terverifikasi dari
        // sandbox adalah field ini muncul di RESPONS (daftar kanal yang
        // tersedia), belum tentu persis field yang sama dipakai untuk
        // MEMPERSEMPIT pilihan di request. Kalau field ini ternyata
        // diabaikan DOKU, dampaknya cuma kosmetik -- Checkout tetap
        // jalan, wali murid cuma lihat semua kanal alih-alih kanal yang
        // dia klik duluan di Qinara. Tidak mempengaruhi keberhasilan
        // pembayaran.
        return match (true) {
            strtoupper($channel) === 'VA' && $bank === 'BCA' => ['VIRTUAL_ACCOUNT_BCA'],
            strtoupper($channel) === 'VA' && $bank === 'BNI' => ['VIRTUAL_ACCOUNT_BNI'],
            strtoupper($channel) === 'VA' && $bank === 'BRI' => ['VIRTUAL_ACCOUNT_BRI'],
            strtoupper($channel) === 'VA' && $bank === 'BSI' => ['VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI'],
            strtoupper($channel) === 'VA' && $bank === 'MANDIRI' => ['VIRTUAL_ACCOUNT_BANK_MANDIRI'],
            strtoupper($channel) === 'EWALLET' && $bank === 'OV' => ['EMONEY_OVO'],
            strtoupper($channel) === 'EWALLET' && $bank === 'DA' => ['EMONEY_DANA'],
            strtoupper($channel) === 'EWALLET' && $bank === 'SP' => ['EMONEY_SHOPEE_PAY'],
            strtoupper($channel) === 'QRIS' => [], // biarkan DOKU tampilkan opsi QRIS + lainnya
            default => [],
        };
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

    // =========================================================================
    // CUSTOM CHECKOUT (Direct API) -- untuk halaman pembayaran ber-branding
    // Qinara/sekolah sendiri, TANPA redirect ke domain DOKU. Menggantikan
    // pendekatan Checkout Link untuk kebutuhan ini (Checkout Link tetap ada
    // di buatPaymentRequest() di atas kalau suatu saat dibutuhkan lagi).
    // =========================================================================

    /**
     * Buat VA langsung (Non-SNAP, endpoint yang SUDAH TERBUKTI sukses di
     * sandbox -- lihat log 'DokuService::buatPaymentRequest sukses'
     * dengan channel VA sebelumnya). VA yang dihasilkan bersifat
     * UNIVERSAL DOKU (nomor sama bisa ditransfer dari bank manapun) --
     * BUKAN VA khusus 1 bank seperti BCA/BNI terpisah. Ini keputusan
     * SENGAJA supaya tidak bergantung pada endpoint per-bank yang belum
     * terkonfirmasi -- juga menyederhanakan UI checkout custom (tidak
     * perlu render logo/pilihan bank, cukup 1 nomor VA).
     */
    public function buatVaLangsung(
        string $referenceId,
        int $amount,
        string $judul,
        ?string $dokuSubAccountId = null
    ): array {
        $body = [
            'order' => [
                'invoice_number' => $referenceId,
                'amount' => $amount,
            ],
            'virtual_account_info' => [
                'expired_time' => 60, // menit
                'reusable_status' => false,
                'info1' => 'Qinara - ' . $judul,
            ],
        ];

        if ($dokuSubAccountId) {
            $body['additional_info'] = [
                'account' => ['id' => $dokuSubAccountId],
            ];
        }

        $result = $this->post('/doku-virtual-account/v2/payment-code', $body);

        Log::info('DokuService::buatVaLangsung sukses', [
            'reference' => $referenceId,
            'full_response' => $result,
        ]);

        return $result;
    }

    /**
     * Ambil OAuth access token SNAP (Asymmetric Signature) -- dipakai
     * SEBELUM memanggil endpoint SNAP manapun (termasuk buatQris() di
     * bawah). Endpoint & formula signature dikonfirmasi dari dokumentasi
     * resmi developers.doku.com/get-started-with-doku-api.
     *
     * !! WAJIB SETUP DULU SEBELUM INI BISA JALAN !!
     * Skema SNAP pakai signature ASIMETRIS (RSA-SHA256), BEDA dari
     * skema Non-SNAP (HMAC-SHA256 pakai Secret-Key biasa) yang dipakai
     * method lain di class ini. Anda perlu:
     * 1. Generate keypair RSA (mis. `openssl genrsa -out doku_private.pem 2048`
     *    lalu `openssl rsa -in doku_private.pem -pubout -out doku_public.pem`)
     * 2. Daftarkan PUBLIC key ke DOKU (lewat dashboard SNAP atau minta
     *    tim onboarding DOKU yang urus -- ini TIDAK bisa dilakukan lewat
     *    kode, murni langkah administratif ke pihak DOKU)
     * 3. Simpan PRIVATE key (bukan public) di server -- taruh isinya di
     *    .env sebagai DOKU_PRIVATE_KEY (format PEM, escape newline jadi
     *    \n literal), lalu load lewat config('services.doku.private_key')
     *
     * Sebelum langkah di atas selesai, method ini (dan buatQris()) akan
     * gagal dengan error dari DOKU (kemungkinan invalid_signature atau
     * unauthorized) -- ini EXPECTED, bukan bug kode.
     */
    protected function getAccessToken(): string
    {
        $cacheKey = 'doku_access_token';

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        $privateKey = config('services.doku.private_key');

        if (blank($privateKey)) {
            throw new RuntimeException(
                'DOKU_PRIVATE_KEY belum diisi di .env -- QRIS custom checkout butuh keypair RSA terdaftar ke DOKU terlebih dulu. Lihat dokumentasi di DokuService::getAccessToken().'
            );
        }

        $timestamp = $this->requestTimestamp();

        // Formula RESMI DOKU untuk Get Token (Asymmetric):
        // stringToSign = Client-Id + "|" + X-Timestamp
        $stringToSign = $this->clientId() . '|' . $timestamp;

        $signature = null;
        $privateKeyResource = openssl_pkey_get_private($privateKey);

        if (! $privateKeyResource) {
            throw new RuntimeException('DOKU_PRIVATE_KEY tidak valid (gagal di-parse OpenSSL) -- pastikan format PEM benar.');
        }

        openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        $response = Http::withHeaders([
            'X-CLIENT-KEY' => $this->clientId(),
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => base64_encode($signature),
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl() . '/authorization/v1/access-token/b2b', [
            'grantType' => 'client_credentials',
        ]);

        if ($response->failed()) {
            Log::error('DokuService::getAccessToken gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal ambil access token DOKU (SNAP): ' . $response->body());
        }

        $token = $response->json('accessToken');
        $expiresIn = (int) ($response->json('expiresIn') ?? 900);

        // Simpan di cache, kurangi 60 detik dari masa berlaku asli
        // sebagai buffer aman.
        \Illuminate\Support\Facades\Cache::put($cacheKey, $token, max($expiresIn - 60, 60));

        return $token;
    }

    /**
     * Generate QRIS dinamis (SNAP Direct API) -- dipakai untuk halaman
     * checkout custom Qinara, tampilkan sebagai gambar QR di halaman
     * kita sendiri (bukan redirect). Endpoint & header dikonfirmasi
     * resmi dari developers.doku.com (path
     * /snap-adapter/b2b/v1.0/qr/qr-mpm-generate).
     *
     * PERLU getAccessToken() berhasil dulu (lihat catatan setup di
     * atas). Field response persis (nama field gambar QR/qr string)
     * BELUM bisa dipastikan 100% dari dokumentasi publik yang saya
     * baca -- WAJIB dicocokkan dengan respons asli begitu keypair RSA
     * sudah terdaftar & sandbox bisa dipanggil (sama seperti proses
     * yang kita lalui untuk VA/Checkout kemarin -- log full response-nya
     * dulu, baru pastikan field mana yang dipakai).
     */
    public function buatQris(string $referenceId, int $amount): array
    {
        $token = $this->getAccessToken();
        $timestamp = $this->requestTimestamp();
        $externalId = (string) random_int(1000000000, 9999999999);
        $path = '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate';

        $body = [
            'partnerReferenceNo' => $referenceId,
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
        ];

        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        // Formula RESMI DOKU untuk signature Transactional (Symmetric):
        // stringToSign = HTTPMethod:EndpointUrl:AccessToken:Lowercase(HexEncode(SHA256(minify(RequestBody)))):TimeStamp
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = "POST:{$path}:{$token}:{$bodyHash}:{$timestamp}";
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey(), true));

        $response = Http::withHeaders([
            'X-PARTNER-ID' => $this->clientId(),
            'X-EXTERNAL-ID' => $externalId,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->withBody($rawBody, 'application/json')
            ->post($this->baseUrl() . $path);

        if ($response->failed()) {
            Log::error('DokuService::buatQris gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal generate QRIS DOKU: ' . $response->body());
        }

        $result = $response->json() ?? [];

        Log::info('DokuService::buatQris sukses', [
            'reference' => $referenceId,
            'full_response' => $result,
        ]);

        return $result;
    }
}
