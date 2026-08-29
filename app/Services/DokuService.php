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
    /**
     * Hitung fee admin Qinara: persentase dari nominal tagihan, DIBATASI
     * cap maksimum -- supaya adil untuk tagihan kecil (SPP bulanan,
     * proporsional) maupun tagihan besar (PPDB/uang pangkal, tidak
     * melonjak tanpa batas). Konfigurasi di .env: DOKU_FEE_PERSEN
     * (default 0.75) dan DOKU_FEE_CAP (default Rp10.000).
     *
     * Nominal ini DITAMBAHKAN ke jumlah yang di-charge ke wali murid
     * (order.amount ke DOKU) -- BUKAN dipotong dari tagihan. Sekolah
     * tetap terima 100% nominal tagihan asli; fee ini murni tambahan
     * di atasnya. Lihat catatan lengkap di migration
     * add_fee_admin_to_pembayarans_table -- fee dicatat di kolom
     * terpisah `fee_admin`, TIDAK ikut masuk hitungan pelunasan
     * tagihan (nominal_terbayar).
     */
    public static function hitungFee(int $nominalTagihan): int
    {
        $persen = (float) config('services.doku.fee_persen', 0.75);
        $cap = (int) config('services.doku.fee_cap', 10000);

        $fee = (int) round($nominalTagihan * $persen / 100);

        return min($fee, $cap);
    }

    /**
     * Estimasi fee DOKU sendiri untuk channel tertentu -- DIKONFIRMASI
     * RESMI oleh tim DOKU: "sistem DOKU tidak memiliki fitur otomatis
     * untuk membebankan biaya transaksi langsung kepada pelanggan.
     * Biaya layanan akan dipotong dari settlement yang dikirimkan ke
     * rekening Anda." Artinya kalau tidak ditambahkan manual di sini,
     * fee ini akan otomatis MAKAN MARGIN QINARA saat settlement --
     * bukan cuma tidak tampil ke wali, tapi benar-benar mengurangi
     * uang yang diterima Qinara tanpa kita sadari.
     */
    public static function hitungFeeDoku(int $nominalDicharge, string $channel): int
    {
        $config = config('services.doku.fee_doku.' . strtoupper($channel));

        if (!$config) {
            return 0;
        }

        $flat = (int) ($config['flat'] ?? 0);
        $persen = (float) ($config['persen'] ?? 0);

        return $flat + (int) round($nominalDicharge * $persen / 100);
    }

    /**
     * Fee TOTAL yang ditampilkan ke wali murid sebagai "Biaya Admin" --
     * gabungan fee Qinara (hitungFee()) + estimasi fee DOKU
     * (hitungFeeDoku()), SATU angka saja (sesuai keputusan: wali tidak
     * perlu lihat 2 baris biaya admin terpisah yang membingungkan).
     * Ini yang dipakai untuk hitung $amountCharged ke DOKU, BUKAN
     * hitungFee() sendirian lagi.
     */
    public static function hitungFeeTotal(int $nominalTagihan, string $channel): int
    {
        $feeQinara = self::hitungFee($nominalTagihan);

        // Fee DOKU dihitung dari nominal TAGIHAN + fee Qinara (mendekati
        // nominal akhir yang akan di-charge) -- pendekatan iteratif
        // sederhana, cukup akurat untuk komponen persen yang kecil.
        $feeDoku = self::hitungFeeDoku($nominalTagihan + $feeQinara, $channel);

        return $feeQinara + $feeDoku;
    }

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

        $response = Http::timeout(25)->withHeaders([
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
        ?string $callbackUrl = null
    ): array {
        // DIPERBAIKI -- dicocokkan persis dengan skema resmi
        // developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration:
        // order.currency & order.auto_redirect ditambahkan (auto_redirect
        // ditandai Mandatory di dokumentasi resmi, sebelumnya tidak
        // dikirim sama sekali).
        $order = [
            'amount' => $amount,
            'invoice_number' => $referenceId,
            'currency' => 'IDR',
            'callback_url' => $callbackUrl ?? url('/wali/keuangan'),
            'callback_url_result' => $callbackUrl ?? url('/wali/keuangan'),
            'auto_redirect' => true,
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
            'full_response' => $result,
        ]);

        return $result;
    }

    /**
     * Terjemahkan pilihan bank/kanal dari UI Qinara ke daftar
     * `payment_method_types` yang dikenali DOKU Checkout -- DIPERBAIKI &
     * DILENGKAPI, dicocokkan persis dengan daftar enum resmi di
     * developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration
     * (contoh body "Full Request" mereka). Sebelumnya cuma menangani VA
     * + sebagian e-wallet; ALFAMART/INDOMARET/OVO/QRIS belum dipetakan
     * sama sekali (jatuh ke default kosong).
     */
    protected function paymentMethodTypes(string $channel, ?string $bank): array
    {
        return match (strtoupper($channel)) {
            'VA' => match (strtoupper((string) $bank)) {
                'BCA' => ['VIRTUAL_ACCOUNT_BCA'],
                'BNI' => ['VIRTUAL_ACCOUNT_BNI'],
                'BRI' => ['VIRTUAL_ACCOUNT_BRI'],
                'BSI' => ['VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI'],
                'MANDIRI' => ['VIRTUAL_ACCOUNT_BANK_MANDIRI'],
                'BJB' => ['VIRTUAL_ACCOUNT_BNC'], // BJB tidak ada di daftar resmi Checkout -- BNC dipakai sbg VA universal DOKU terdekat, WAJIB dicek ulang kalau bank BJB dipakai
                default => ['VIRTUAL_ACCOUNT_DOKU'], // VA universal DOKU kalau bank tidak dipilih/dikenali
            },
            'QRIS' => ['QRIS'],
            'DANA' => ['EMONEY_DANA'],
            'SHOPEEPAY' => ['EMONEY_SHOPEEPAY'],
            'OVO' => ['EMONEY_OVO'],
            'ALFAMART' => ['ONLINE_TO_OFFLINE_ALFA'],
            'INDOMARET' => ['ONLINE_TO_OFFLINE_INDOMARET'],
            default => [], // kosong -- DOKU tampilkan semua kanal aktif
        };
    }

    /**
     * Verifikasi signature notifikasi (webhook) DOKU -- implementasi
     * PERSIS contoh resmi DOKU (Best Practice -- HTTP Notification),
     * termasuk perhitungan Digest dari raw notification body.
     *
     * DIPERBAIKI -- BUG BESAR ditemukan: parameter $headers sebelumnya
     * diisi langsung dari `$request->headers->all()` di
     * DokuWebhookController, yang di Laravel mengembalikan SETIAP nilai
     * header sebagai ARRAY (mis. ['client-id' => ['BRN-0216-...']]),
     * BUKAN string. Saat di-interpolasi ke string
     * ("Client-Id:{$clientId}"), PHP diam-diam mengubah array itu jadi
     * literal teks "Array" -- jadi signature yang kita hitung SELALU
     * salah, verifikasi SELALU gagal, dan SETIAP notifikasi Non-SNAP
     * dari DOKU (termasuk VA universal yang baru terbukti sukses)
     * ditolak 403 sebelum sempat memproses pembayaran. Method ini
     * sekarang menerima 4 string langsung (dipanggil dengan
     * $request->header('Client-Id') dkk di controller, yang SUDAH
     * mengembalikan string, bukan array) supaya tidak ada lagi celah
     * seperti ini.
     */
    public function verifyNotificationSignature(
        ?string $clientId,
        ?string $requestId,
        ?string $timestamp,
        ?string $signature,
        string $rawBody,
        string $notificationPath
    ): bool {
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
        string $customerName,
        string $customerEmail,
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
            // WAJIB -- endpoint VA langsung (beda dari Checkout Link)
            // menolak request tanpa customer, konfirmasi dari respons
            // error sandbox: {"error":{"message":"customer is required"}}
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
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
     * VA SNAP per bank -- ENDPOINT & HEADER SUDAH TERKONFIRMASI RESMI
     * dari developers.doku.com (bukan tebakan): 1 endpoint yang sama
     * dipakai untuk SEMUA bank (BCA, BNI, BRI, Mandiri, BNC, BTN,
     * Permata, Danamon), dibedakan lewat field `partnerServiceId` di
     * body, BUKAN path URL berbeda per bank seperti dugaan awal.
     *
     * !! WAJIB SETUP DULU SEBELUM BISA DITES !!
     * `partnerServiceId` itu KODE KHUSUS per bank yang DOKU terbitkan
     * ke akun Anda (mirip nomor kanal), BUKAN sesuatu yang bisa kita
     * karang sendiri -- WAJIB minta ke tim onboarding/sales DOKU:
     * "Mohon partnerServiceId untuk VA SNAP channel BCA, BNI, BRI,
     * Mandiri untuk akun sandbox kami (Client-Id: ...)". Isi hasilnya
     * ke config/services.php -> doku.va_snap_partner_service_id.
     *
     * Field body & response BELUM 100% dipastikan lengkap dari
     * dokumentasi publik (cuma sebagian field yang terlihat: trxId,
     * totalAmount, virtualAccountTrxType, expiredDate) -- WAJIB
     * dicocokkan lagi begitu sandbox bisa dipanggil beneran (pola yang
     * sama seperti channel lain sebelumnya: log full response dulu,
     * baru pastikan field yang dipakai).
     */
    /**
     * Peta kode bank internal Qinara -> nama channel resmi DOKU yang
     * dipakai di field `additionalInfo.channel` -- KONFIRMASI dari
     * respons error sandbox: field ini WAJIB diisi
     * ("Invalid Mandatory Field {additionalInfo.channel}"). Nama
     * channel per bank diambil dari daftar `payment_method_types` yang
     * SUDAH TERKONFIRMASI muncul di respons DOKU Checkout sebelumnya
     * (VIRTUAL_ACCOUNT_BCA, VIRTUAL_ACCOUNT_BANK_MANDIRI, dst) --
     * KECUALI BJB yang belum pernah terlihat eksplisit di respons
     * manapun, jadi nama channel-nya masih TEBAKAN mengikuti pola
     * penamaan bank lain (VIRTUAL_ACCOUNT_BANK_{NAMA}), WAJIB
     * dicocokkan kalau BJB dites dan gagal lagi.
     */
    protected function vaSnapChannelName(string $bankKode): string
    {
        return match (strtoupper($bankKode)) {
            'BCA' => 'VIRTUAL_ACCOUNT_BCA',
            'BNI' => 'VIRTUAL_ACCOUNT_BNI',
            'BRI' => 'VIRTUAL_ACCOUNT_BRI',
            'MANDIRI' => 'VIRTUAL_ACCOUNT_BANK_MANDIRI',
            'BSI' => 'VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI',
            'BJB' => 'VIRTUAL_ACCOUNT_BANK_BJB', // TEBAKAN -- belum terkonfirmasi, cek kalau gagal
            default => 'VIRTUAL_ACCOUNT_DOKU',
        };
    }

    public function buatVaSnap(
        string $bankKode,
        string $referenceId,
        int $amount,
        string $customerName,
        string $customerEmail,
        string $customerPhone
    ): array {
        $partnerServiceId = config('services.doku.va_snap_partner_service_id.' . strtoupper($bankKode));

        if (blank($partnerServiceId)) {
            throw new RuntimeException(
                "partnerServiceId untuk bank {$bankKode} belum diisi di config/services.php -- minta ke tim DOKU dulu (lihat catatan lengkap di DokuService::buatVaSnap())."
            );
        }

        $token = $this->getAccessToken();
        $timestamp = $this->requestTimestamp();
        $externalId = (string) random_int(1000000000, 9999999999);
        // DIPERBAIKI -- dicek ulang langsung ke developers.doku.com:
        // path v1 (tanpa ".1") adalah dokumentasi versi LAMA yang sudah
        // dipindah ke bagian "archive" dan memakai skema
        // virtualAccountTrxType NUMERIK ("1"=Closed / "2"=Open, BUKAN
        // "C"/"O"). Body di bawah ini sudah ditulis mengikuti skema
        // v1.1 yang aktif sekarang ("C"/"O"/"V" -- lihat
        // virtualAccountTrxType di bawah), jadi path-nya WAJIB v1.1
        // juga -- itu sumber error "Invalid Field Format
        // {virtualAccountTrxType}" sebelumnya: body v1.1 dikirim ke
        // endpoint v1.
        $path = '/virtual-accounts/bi-snap-va/v1.1/transfer-va/create-va';

        $partnerServiceIdPadded = str_pad($partnerServiceId, 8, ' ', STR_PAD_LEFT);
        // DIPERBAIKI -- dikonfirmasi LANGSUNG oleh tim support DOKU
        // (setelah kirim contoh error sandbox kita): customerNo
        // SEBELUMNYA di-hardcode '0' untuk SEMUA transaksi -- ini
        // penyebab "Feature Not Allowed [Identifier for BIN = 'xxxxx'
        // is not configured properly]", karena kombinasi
        // partnerServiceId+customerNo yang SAMA dipakai berulang-ulang
        // dianggap tidak valid. customerNo WAJIB unik per transaksi --
        // dipakai angka detik+microdetik saat ini (numerik murni,
        // pendek, hampir pasti tidak bentrok antar transaksi).
        $customerNo = (string) ((int) (microtime(true) * 1000) % 1000000000);

        $body = [
            // partnerServiceId WAJIB persis 8 karakter menurut standar
            // BI-SNAP -- kalau kurang dari 8 digit, DIBERI SPASI DI
            // KIRI (padding). Dikonfirmasi dari respons error sandbox:
            // "Invalid Field Format {partnerServiceId}" saat dikirim
            // tanpa padding (cuma 5 digit, "19008").
            'partnerServiceId' => $partnerServiceIdPadded,
            'customerNo' => $customerNo,
            // virtualAccountNo = partnerServiceId YANG SUDAH DI-PAD (8
            // karakter) digabung customerNo MENTAH (tanpa padding
            // sendiri) -- formula ini dikonfirmasi tim support DOKU
            // sendiri, cuma nilai customerNo-nya yang tadinya salah
            // (lihat catatan di atas).
            'virtualAccountNo' => $partnerServiceIdPadded . $customerNo,
            'virtualAccountName' => $customerName,
            'virtualAccountEmail' => $customerEmail,
            'virtualAccountPhone' => $customerPhone,
            'trxId' => $referenceId,
            'totalAmount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            // feeAmount -- field WAJIB menurut contoh resmi DOKU (tidak
            // ada di dokumentasi publik yang saya baca sebelumnya).
            // Diisi 0 karena fee Qinara sudah kita hitung & masukkan
            // sendiri ke $amount (lihat DokuService::hitungFeeTotal())
            // -- field ini murni untuk fitur fee DOKU sendiri kalau
            // mereka mau potong otomatis, yang TIDAK kita pakai.
            'feeAmount' => [
                'value' => '0.00',
                'currency' => 'IDR',
            ],
            'virtualAccountTrxType' => 'C', // Closed Amount -- nominal tetap, tidak bisa diubah pembayar
            'expiredDate' => now()->addHour()->toIso8601String(),
            'additionalInfo' => [
                'channel' => $this->vaSnapChannelName($bankKode),
                'virtualAccountConfig' => [
                    'reusableStatus' => false, // sesuai VA lain di aplikasi -- 1 VA cuma untuk 1 transaksi
                ],
            ],
        ];

        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = "POST:{$path}:{$token}:{$bodyHash}:{$timestamp}";
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey(), true));

        $response = Http::timeout(25)->withHeaders([
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $this->clientId(),
            'X-EXTERNAL-ID' => $externalId,
            'CHANNEL-ID' => 'H2H',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->withBody($rawBody, 'application/json')
            ->post($this->baseUrl() . $path);

        if ($response->failed()) {
            Log::error('DokuService::buatVaSnap gagal', [
                'bank' => $bankKode,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal membuat VA SNAP DOKU: ' . $response->body());
        }

        $result = $response->json() ?? [];

        // virtualAccountNo yang kita KIRIM (bukan yang dibalas DOKU) --
        // ini nilai PASTI, sudah kita tentukan sendiri di body, jadi
        // tidak perlu tebak-tebak lagi field respons untuk nomor VA
        // yang ditampilkan ke wali murid.
        $result['_qinara_virtual_account_no'] = $body['virtualAccountNo'];

        Log::info('DokuService::buatVaSnap sukses', [
            'bank' => $bankKode,
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

        $response = Http::timeout(25)->withHeaders([
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
        // DITAMBAHKAN -- dicek ulang ke developers.doku.com: merchantId
        // & terminalId itu MANDATORY di body qr-mpm-generate (lihat
        // contoh resmi mereka), sebelumnya tidak dikirim sama sekali di
        // sini, jadi request pasti ditolak DOKU (mandatory field
        // hilang) sebelum sempat urusan signature/access token.
        $merchantId = config('services.doku.qris_merchant_id');
        $terminalId = config('services.doku.qris_terminal_id');

        if (blank($merchantId) || blank($terminalId)) {
            throw new RuntimeException(
                'DOKU_QRIS_MERCHANT_ID / DOKU_QRIS_TERMINAL_ID belum diisi di .env -- minta ke tim DOKU dulu (lihat catatan di DokuService::buatQris()).'
            );
        }

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
            'merchantId' => $merchantId,
            'terminalId' => $terminalId,
        ];

        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        // Formula RESMI DOKU untuk signature Transactional (Symmetric):
        // stringToSign = HTTPMethod:EndpointUrl:AccessToken:Lowercase(HexEncode(SHA256(minify(RequestBody)))):TimeStamp
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = "POST:{$path}:{$token}:{$bodyHash}:{$timestamp}";
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey(), true));

        $response = Http::timeout(25)->withHeaders([
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

    /**
     * DANA & ShopeePay (SNAP, satu endpoint dipakai untuk keduanya --
     * dibedakan lewat additionalInfo.channel). Hasilnya link
     * "webRedirectUrl" -- customer diarahkan ke app DANA/ShopeePay untuk
     * approve pembayaran, lalu balik lagi ke Qinara. Endpoint &
     * struktur body dikonfirmasi resmi dari
     * developers.doku.com/.../e-wallet/dana & /shopeepay.
     *
     * $channel: 'EMONEY_DANA_SNAP' atau 'EMONEY_SHOPEE_PAY_SNAP'.
     *
     * PERLU getAccessToken() berhasil dulu -- lihat catatan setup
     * keypair RSA di getAccessToken().
     */
    public function buatEwalletSnap(string $channel, string $referenceId, int $amount, string $returnUrl, string $judul = 'Pembayaran Qinara'): array
    {
        $token = $this->getAccessToken();
        $timestamp = $this->requestTimestamp();
        $externalId = (string) random_int(1000000000, 9999999999);
        $path = '/direct-debit/core/v1/debit/payment-host-to-host';

        // DIPERBAIKI -- dicocokkan dengan contoh "format request yang
        // benar" yang dikirim LANGSUNG oleh tim support DOKU (bukan
        // cuma dokumentasi publik lagi):
        // 1. `validUpTo` (batas waktu link bayar) sebelumnya HILANG
        //    total dari body -- ditambahkan (1 jam dari sekarang).
        // 2. `urlParam` sebelumnya dikirim sebagai OBJEK biasa --
        //    contoh resmi dari support DOKU membungkusnya jadi ARRAY
        //    berisi 1 objek. Diperbaiki mengikuti persis.
        // 3. `additionalInfo.orderTitle` sebelumnya tidak dikirim sama
        //    sekali, padahal WAJIB khusus untuk DANA (dikonfirmasi
        //    support). Diisi judul tagihan/topup.
        // 4. `supportDeepLinkCheckoutUrl` ditambahkan sesuai contoh
        //    (khusus DANA -- aman dikirim juga untuk ShopeePay, DOKU
        //    akan abaikan kalau tidak relevan).
        $body = [
            'partnerReferenceNo' => $referenceId,
            'validUpTo' => now()->addHour()->toIso8601String(),
            'pointOfInitiation' => 'mweb', // mobile web (browser) -- bukan 'app' karena Qinara bukan native app
            'urlParam' => [
                [
                    'url' => $returnUrl,
                    'type' => 'PAY_RETURN',
                    'isDeepLink' => 'Y',
                ],
            ],
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'additionalInfo' => [
                'channel' => $channel,
                'orderTitle' => $judul,
                'supportDeepLinkCheckoutUrl' => 'true',
            ],
        ];

        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = "POST:{$path}:{$token}:{$bodyHash}:{$timestamp}";
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey(), true));

        $response = Http::timeout(25)->withHeaders([
            'X-PARTNER-ID' => $this->clientId(),
            'X-EXTERNAL-ID' => $externalId,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->withBody($rawBody, 'application/json')
            ->post($this->baseUrl() . $path);

        if ($response->failed()) {
            Log::error('DokuService::buatEwalletSnap gagal', [
                'channel' => $channel,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal membuat pembayaran e-wallet DOKU: ' . $response->body());
        }

        $result = $response->json() ?? [];

        Log::info('DokuService::buatEwalletSnap sukses', [
            'reference' => $referenceId,
            'channel' => $channel,
            'full_response' => $result,
        ]);

        return $result;
    }

    /**
     * Alfamart / Indomaret (Over The Counter -- O2O) -- Non-SNAP, skema
     * & struktur body SAMA PERSIS dengan buatVaLangsung() yang sudah
     * terbukti sukses (order/customer/online_to_offline_info), cuma
     * beda endpoint & nama field respons (payment_code, bukan
     * virtual_account_number). Struktur body dikonfirmasi resmi dari
     * contoh developers.doku.com/.../convenience-store/indomaret.
     *
     * $toko: 'INDOMARET' atau 'ALFAMART'.
     *
     * CATATAN: path endpoint Alfamart BELUM 100% dikonfirmasi dari
     * dokumentasi publik yang saya baca (Indomaret sudah, contoh
     * lengkap dengan body & respons) -- saya samakan polanya mengikuti
     * konvensi penamaan DOKU yang konsisten di semua channel lain
     * (`{channel}-online-to-offline/v2/payment-code`). Kalau ternyata
     * beda utnuk Alfamart, cek log error & sesuaikan path di bawah.
     */
    public function buatOtc(
        string $toko,
        string $referenceId,
        int $amount,
        string $customerName,
        string $customerEmail
    ): array {
        $path = match (strtoupper($toko)) {
            'INDOMARET' => '/indomaret-online-to-offline/v2/payment-code',
            'ALFAMART' => '/alfa-online-to-offline/v2/payment-code',
            default => throw new RuntimeException('Toko tidak dikenali: ' . $toko),
        };

        $body = [
            'order' => [
                'invoice_number' => $referenceId,
                'amount' => $amount,
            ],
            'online_to_offline_info' => [
                'expired_time' => 1440, // menit -- 24 jam, wajar untuk bayar di minimarket (tidak instan seperti VA)
                'reusable_status' => false,
                'info' => 'Pembayaran Qinara',
            ],
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
            ],
        ];

        $result = $this->post($path, $body);

        Log::info('DokuService::buatOtc sukses', [
            'toko' => $toko,
            'reference' => $referenceId,
            'full_response' => $result,
        ]);

        return $result;
    }

    /**
     * OVO Push Payment -- BEDA TOTAL dari semua method di atas: bukan
     * "generate kode lalu customer bayar sendiri kapan saja", tapi
     * DOKU langsung PUSH notifikasi ke aplikasi OVO milik nomor HP yang
     * dikasih, customer approve dari HP-nya, dan API ini BLOCKING
     * (nunggu) sampai customer approve/tolak/timeout -- maksimal 70
     * detik sesuai dokumentasi resmi DOKU. Makanya timeout HTTP
     * di-set lebih panjang dari method lain (75 detik, bukan 25).
     *
     * Skema signature juga beda -- BUKAN HMAC seperti method lain,
     * tapi checksum SHA256 sederhana dari beberapa field yang
     * digabung, sesuai contoh resmi:
     * sha256(amount + client_id + invoice_number + ovo_id + secret_key)
     *
     * PERLU nomor HP yang terdaftar di OVO ($ovoId, format
     * "081234567890") -- makanya UI checkout untuk OVO WAJIB minta
     * input nomor HP dulu sebelum panggil method ini, beda dari
     * VA/QRIS/OTC yang langsung generate tanpa input tambahan.
     */
    public function buatOvo(string $referenceId, int $amount, string $ovoId): array
    {
        $checksum = hash('sha256', $amount . $this->clientId() . $referenceId . $ovoId . $this->secretKey());

        $body = [
            'client' => ['id' => $this->clientId()],
            'order' => [
                'invoice_number' => $referenceId,
                'amount' => $amount,
            ],
            'ovo_info' => ['ovo_id' => $ovoId],
            'security' => ['check_sum' => $checksum],
        ];

        $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        // PERLU DIVERIFIKASI: path endpoint persis untuk OVO Push
        // Payment belum dikonfirmasi 100% dari dokumentasi publik yang
        // saya baca -- ini tebakan berdasar konvensi penamaan channel
        // lain (ovo-emoney/v2/payment). Sesuaikan begitu dapat respons
        // asli sandbox.
        $response = Http::timeout(75)->withHeaders([
            'Content-Type' => 'application/json',
        ])->withBody($rawBody, 'application/json')
            ->post($this->baseUrl() . '/ovo-emoney/v2/payment');

        if ($response->failed()) {
            Log::error('DokuService::buatOvo gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal membuat pembayaran OVO: ' . $response->body());
        }

        $result = $response->json() ?? [];

        Log::info('DokuService::buatOvo sukses', [
            'reference' => $referenceId,
            'full_response' => $result,
        ]);

        return $result;
    }
}
