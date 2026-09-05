<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\Pembayaran;
use App\Models\WalletTransaction;
use App\Services\DokuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook DOKU -- satu endpoint untuk SEMUA domain pembayaran (topup
 * wallet, tagihan/SPP, PPDB, langganan SaaS), dibedakan lewat prefix
 * reference_id, mengikuti pola persis yang sudah ada sebelumnya di
 * DuitkuController & XenditWebhookController:
 *   TOPUP-...   -> WalletTransaction (topup saldo wali)
 *   TAGIHAN-... -> Pembayaran (SPP dkk, dari WaliDashboardController)
 *   PPDB-...    -> Pembayaran (biaya pendaftaran, dari PpdbPembayaranController)
 *   SUB-...     -> SubscriptionPayment (billing Qinara -> Yayasan)
 *
 * PENTING: nama field body notifikasi ('order.invoice_number',
 * 'transaction.status', dst) mengikuti struktur yang terdokumentasi di
 * developers.doku.com untuk notifikasi Checkout/Virtual Account -- BELUM
 * diuji terhadap notifikasi sungguhan. Begitu sandbox aktif dan
 * notifikasi pertama masuk, cek payload asli di log
 * (Log::info di baris pertama method handle()) dan sesuaikan field di
 * bawah kalau berbeda.
 */
class DokuWebhookController extends Controller
{
    /**
     * "Token URL" -- endpoint yang DIPANGGIL BALIK oleh DOKU (arah
     * kebalikan dari DokuService::getAccessToken()) SEBELUM mereka
     * kirim notifikasi SNAP. Ini standar resmi DOKU (format persis
     * sama dengan respons Get Token B2B yang kita terima sendiri saat
     * MEMANGGIL DOKU -- cuma sekarang posisinya kebalikan: DOKU jadi
     * "client", kita jadi "server").
     *
     * DOKU menandatangani request ini pakai PRIVATE KEY MEREKA SENDIRI
     * -- kita verifikasi pakai DOKU PUBLIC KEY (dari Settings > API
     * Keys > "DOKU Public Key" di dashboard mereka, disalin ke
     * DOKU_PUBLIC_KEY di .env). Formula stringToSign PERSIS sama
     * dengan yang kita pakai di DokuService::getAccessToken(): 
     * X-CLIENT-KEY + "|" + X-TIMESTAMP.
     *
     * URL endpoint ini (https://.../webhooks/doku/token) didaftarkan
     * di dashboard DOKU: Settings > API Keys > Pengaturan SNAP >
     * "Edit Token URL".
     */
    public function tokenB2B(Request $request)
    {
        $clientKey = $request->header('X-CLIENT-KEY');
        $timestamp = $request->header('X-TIMESTAMP');
        $signature = $request->header('X-SIGNATURE');

        Log::info('DokuWebhookController::tokenB2B dipanggil', [
            'headers' => $request->headers->all(),
        ]);

        if (!$clientKey || !$timestamp || !$signature) {
            return response()->json([
                'responseCode' => '4007301',
                'responseMessage' => 'Missing mandatory header',
            ], 400);
        }

        $dokuPublicKey = config('services.doku.doku_public_key');

        if (blank($dokuPublicKey)) {
            Log::error('DokuWebhookController::tokenB2B: DOKU_PUBLIC_KEY belum diisi di .env');

            return response()->json([
                'responseCode' => '5007300',
                'responseMessage' => 'Internal Server Error',
            ], 500);
        }

        $stringToSign = $clientKey . '|' . $timestamp;
        $publicKeyResource = openssl_pkey_get_public($dokuPublicKey);

        if (!$publicKeyResource) {
            Log::error('DokuWebhookController::tokenB2B: DOKU_PUBLIC_KEY gagal di-parse OpenSSL');

            return response()->json([
                'responseCode' => '5007300',
                'responseMessage' => 'Internal Server Error',
            ], 500);
        }

        $signatureBinary = base64_decode($signature);
        $valid = openssl_verify($stringToSign, $signatureBinary, $publicKeyResource, OPENSSL_ALGO_SHA256) === 1;

        if (!$valid) {
            Log::warning('DokuWebhookController::tokenB2B: signature tidak valid', [
                'client_key' => $clientKey,
            ]);

            return response()->json([
                'responseCode' => '4017300',
                'responseMessage' => 'Unauthorized. Invalid Signature',
            ], 401);
        }

        // Signature valid -- terbitkan access token, simpan di cache
        // supaya bisa dicocokkan lagi saat DOKU pakai token ini untuk
        // panggil endpoint notifikasi (handle() di bawah).
        $token = Str::random(64);
        $expiresIn = 900; // detik, standar DOKU

        Cache::put('doku_incoming_token:' . $token, true, $expiresIn);

        Log::info('DokuWebhookController::tokenB2B sukses, token diterbitkan', [
            'client_key' => $clientKey,
        ]);

        return response()->json([
            'responseCode' => '2007300',
            'responseMessage' => 'Successful',
            'accessToken' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => $expiresIn,
            'additionalInfo' => '',
        ]);
    }

    public function handle(Request $request, DokuService $doku)
    {
        $rawBody = $request->getContent();

        Log::info('DokuWebhookController: notifikasi masuk', [
            'headers' => $request->headers->all(),
            'body' => $rawBody,
        ]);

        // Kalau notifikasi ini SNAP (ada Authorization: Bearer <token>),
        // jangan pakai verifikasi Non-SNAP (skema signature-nya beda
        // total, pasti gagal kalau dipaksakan) -- cukup cocokkan Bearer
        // token dengan yang PERNAH kita terbitkan lewat tokenB2B() di
        // atas. Field notifikasi SNAP yang PERSIS belum terkonfirmasi
        // dari dokumentasi publik -- begitu notifikasi SNAP pertama
        // masuk, log ini akan tunjukkan struktur aslinya, baru kita
        // pastikan verifikasi signature SNAP (kalau memang perlu
        // tambahan selain Bearer token) dan field body yang dipakai.
        $bearerToken = $request->bearerToken();

        if ($bearerToken) {
            $tokenValid = Cache::has('doku_incoming_token:' . $bearerToken);

            Log::info('DokuWebhookController: notifikasi SNAP terdeteksi (ada Bearer token)', [
                'token_cocok_dengan_yang_diterbitkan' => $tokenValid,
            ]);

            if (! $tokenValid) {
                Log::warning('DokuWebhookController: Bearer token notifikasi SNAP tidak dikenali');

                return response('INVALID TOKEN', 401);
            }

            // Token valid -- lewati verifikasi Non-SNAP di bawah,
            // langsung proses body notifikasi.
        } else {
            // DIPERBAIKI -- $request->header('X') mengembalikan STRING
            // (bukan array seperti $request->headers->all()['x']) --
            // lihat catatan lengkap bug ini di
            // DokuService::verifyNotificationSignature().
            $valid = $doku->verifyNotificationSignature(
                $request->header('Client-Id'),
                $request->header('Request-Id'),
                $request->header('Request-Timestamp'),
                $request->header('Signature'),
                $rawBody,
                $request->path() === '/' ? '/webhooks/doku' : '/' . ltrim($request->path(), '/')
            );

            if (! $valid) {
                Log::warning('DokuWebhookController: signature tidak valid', ['ip' => $request->ip()]);

                return response('INVALID SIGNATURE', 403);
            }
        }

        $payload = json_decode($rawBody, true) ?? [];

        $referenceId = data_get($payload, 'order.invoice_number')
            ?? data_get($payload, 'invoice_number')
            ?? data_get($payload, 'transaction.original_request_id');

        $status = strtoupper((string) (
            data_get($payload, 'transaction.status')
            ?? data_get($payload, 'order.status')
            ?? data_get($payload, 'status')
        ));

        if (! $referenceId) {
            return response('MISSING invoice_number', 400);
        }

        $sukses = in_array($status, ['SUCCESS', 'SETTLED', 'PAID', 'PROCESSED'], true);

        return match (true) {
            str_starts_with($referenceId, 'TOPUP-') => $this->handleTopup($referenceId, $sukses, $payload),
            str_starts_with($referenceId, 'TAGIHAN-') => $this->handlePembayaran($referenceId, $sukses, $payload),
            str_starts_with($referenceId, 'PPDB-') => $this->handlePembayaran($referenceId, $sukses, $payload),
            str_starts_with($referenceId, 'SUB-') => $this->handleSubscription($referenceId, $sukses, $payload),
            default => response('UNKNOWN reference prefix', 400),
        };
    }

    protected function handleTopup(string $referenceId, bool $sukses, array $payload)
    {
        $trx = WalletTransaction::where('reference_id', $referenceId)->first();

        if (! $trx) {
            return response('NOT FOUND', 404);
        }

        if ($trx->status === 'success') {
            return response('OK (already processed)');
        }

        DB::beginTransaction();

        try {
            if ($sukses) {
                $trx->update(['status' => 'success']);

                $wallet = $trx->wallet;

                if ($wallet) {
                    $wallet->increment('saldo', $trx->amount);
                }

                // DIPERBAIKI (lagi) -- fix yayasan_id sebelumnya TIDAK
                // EFEKTIF untuk topup: Wallet model TIDAK PUNYA kolom
                // lembaga_id sama sekali (cuma siswa_id & saldo -- lihat
                // komentar "Scoping lewat siswa.lembaga (tidak ada
                // lembaga_id langsung)" di Wallet.php), jadi
                // $trx->wallet->lembaga_id dan $trx->wallet->lembaga
                // SELALU null. Harus lewat wallet->siswa->lembaga.
                $lembagaWallet = $wallet?->siswa?->lembaga;

                Kas::create([
                    'tipe' => 'masuk',
                    'nominal' => $trx->amount,
                    'sumber' => 'doku',
                    'tanggal' => now(),
                    'keterangan' => 'Topup Wallet - ' . $trx->reference_id,
                    'rekening_id' => $trx->wallet->rekening_id ?? 1,
                    'kategori_id' => 1,
                    'lembaga_id' => $lembagaWallet?->id ?? 1,
                    'yayasan_id' => $lembagaWallet?->yayasan_id ?? null,
                ]);

                // DITAMBAHKAN -- top up sebelumnya tidak kirim notifikasi
                // WA sama sekali ke wali (murni fitur yang belum dibuat).
                if ($wallet?->siswa) {
                    NotificationService::sendTopup($wallet->siswa, $trx);
                }
            } else {
                // DIPERBAIKI -- dokumentasi resmi DOKU (Best Practice --
                // HTTP Notification): "if you are integrating with
                // Checkout, you must ignore the transaction.status
                // FAILED" -- karena di Checkout wali BISA ganti metode
                // pembayaran kalau yang pertama gagal/batal, notifikasi
                // FAILED bukan berarti transaksi ini final gagal.
                // Sebelumnya langsung ditandai 'failed' di sini, yang
                // bisa salah menutup transaksi yang wali masih bisa
                // selesaikan lewat metode lain. Biarkan tetap 'pending'
                // -- cuma catat di log untuk observability.
                Log::info('DokuWebhookController: notifikasi topup non-sukses diabaikan (Checkout retry-friendly)', [
                    'reference' => $referenceId,
                    'payload' => $payload,
                ]);
            }

            DB::commit();

            return response('OK');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('DokuWebhookController: error topup', [
                'reference' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dipakai bareng oleh Tagihan (SPP) & PPDB -- keduanya sama-sama
     * model Pembayaran, sama seperti DuitkuController::handleTagihanCallback()
     * sebelumnya. Update Tagihan + catat Kas otomatis ditangani
     * Pembayaran::booted() (sudah ada, tidak disentuh di sini).
     */
    protected function handlePembayaran(string $referenceId, bool $sukses, array $payload)
    {
        $pembayaran = Pembayaran::where('reference', $referenceId)->first();

        if (! $pembayaran) {
            return response('NOT FOUND', 404);
        }

        if ($pembayaran->status === 'sukses') {
            return response('OK (already processed)');
        }

        DB::beginTransaction();

        try {
            if ($sukses) {
                $pembayaran->status = 'sukses';
                $pembayaran->tanggal_bayar = now();
                $pembayaran->save();

                if ($pembayaran->siswa) {
                    NotificationService::sendPembayaran($pembayaran->siswa, $pembayaran);
                }

                // DITAMBAHKAN -- BUG ditemukan: afterPaid() (yang antara
                // lain memajukan status PPDB dari 'menunggu_pembayaran'
                // ke 'formulir', dan transisi tipe_sistem lain seperti
                // daftar_ulang_ppdb) sebelumnya CUMA dipanggil dari
                // action manual admin di PembayaranResource -- TIDAK
                // PERNAH dari webhook pembayaran gateway otomatis ini.
                // Akibatnya PPDB/wali yang bayar via DOKU tidak pernah
                // otomatis lanjut ke tahap berikutnya, harus nunggu
                // admin buka manual. Dipanggil di sini juga, PERSIS
                // syarat yang sama seperti flow admin: hanya kalau
                // tagihan benar-benar LUNAS (bukan pembayaran
                // sebagian/cicilan).
                $tagihan = $pembayaran->tagihan()->first();

                if ($tagihan && $tagihan->status === 'lunas') {
                    \App\Services\TagihanService::afterPaid($tagihan);
                }
            } else {
                // DIPERBAIKI -- lihat catatan di handleTopup() di atas
                // (dokumentasi resmi DOKU: abaikan transaction.status
                // FAILED untuk integrasi Checkout, wali masih bisa retry
                // metode lain).
                Log::info('DokuWebhookController: notifikasi pembayaran non-sukses diabaikan (Checkout retry-friendly)', [
                    'reference' => $referenceId,
                    'payload' => $payload,
                ]);
            }

            DB::commit();

            return response('OK');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('DokuWebhookController: error pembayaran', [
                'reference' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }

    protected function handleSubscription(string $referenceId, bool $sukses, array $payload)
    {
        $payment = \App\Models\SubscriptionPayment::where('gateway_order_id', $referenceId)->first();

        if (! $payment) {
            return response('NOT FOUND', 404);
        }

        if ($payment->status === 'berhasil') {
            return response('OK (already processed)');
        }

        DB::beginTransaction();

        try {
            $payment->update([
                'gateway_transaction_id' => data_get($payload, 'transaction.id') ?? null,
                'gateway_raw_response' => $payload,
            ]);

            if ($sukses) {
                $payment->update(['status' => 'berhasil']);

                $subscription = $payment->subscription;
                $yayasan = $subscription->yayasan;
                $statusSebelumnya = $yayasan->status;

                $subscription->update([
                    'status' => 'active',
                    'mulai_pada' => now(),
                    'berakhir_pada' => $subscription->isTahunan() ? now()->addYear() : now()->addMonth(),
                ]);

                $yayasan->update(['status' => 'active']);

                // Kalau plan yang baru dibayar ini "termasuk semua modul"
                // (mis. Paket Full), nyalakan semua modul di semua
                // Lembaga milik yayasan ini SEKARANG -- setelah
                // pembayaran beneran sukses, BUKAN lagi langsung waktu
                // tenant klik tombol "Aktifkan Paket Full" (celah
                // billing ditemukan 4 Sep 2026, lihat
                // Langganan::aktifkanPaketFull()).
                if ($subscription->plan?->termasuk_semua_modul) {

                    $modulSemua = \App\Models\ModulePrice::aktif()->get();

                    foreach ($yayasan->lembagas as $lembaga) {
                        foreach ($modulSemua as $mp) {
                            $existing = $lembaga->modules()->where('module_price_id', $mp->id)->first();

                            if ($existing) {
                                $existing->update(['is_active' => true, 'aktif_sejak' => now(), 'nonaktif_sejak' => null]);
                            } else {
                                $lembaga->modules()->create([
                                    'module_price_id' => $mp->id,
                                    'is_active' => true,
                                    'aktif_sejak' => now(),
                                ]);
                            }
                        }
                    }
                }

                if ($statusSebelumnya !== 'active') {
                    try {
                        NotificationService::sendAplikasiAktif($yayasan);
                    } catch (\Throwable $e) {
                        Log::error("DokuWebhookController: gagal kirim notif aplikasi aktif untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                    }
                }
            } else {
                // DIPERBAIKI -- lihat catatan di handleTopup() di atas.
                Log::info('DokuWebhookController: notifikasi subscription non-sukses diabaikan (Checkout retry-friendly)', [
                    'reference' => $referenceId,
                    'payload' => $payload,
                ]);
            }

            DB::commit();

            return response('OK');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('DokuWebhookController: error subscription', [
                'reference' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }
}
