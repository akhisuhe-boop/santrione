<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\Pembayaran;
use App\Models\WalletTransaction;
use App\Services\DokuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function handle(Request $request, DokuService $doku)
    {
        $rawBody = $request->getContent();

        Log::info('DokuWebhookController: notifikasi masuk', [
            'headers' => $request->headers->all(),
            'body' => $rawBody,
        ]);

        $valid = $doku->verifyNotificationSignature(
            $request->headers->all(),
            $rawBody,
            $request->path() === '/' ? '/webhooks/doku' : '/' . ltrim($request->path(), '/')
        );

        if (! $valid) {
            Log::warning('DokuWebhookController: signature tidak valid', ['ip' => $request->ip()]);

            return response('INVALID SIGNATURE', 403);
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

                Kas::create([
                    'tipe' => 'masuk',
                    'nominal' => $trx->amount,
                    'sumber' => 'doku',
                    'tanggal' => now(),
                    'keterangan' => 'Topup Wallet - ' . $trx->reference_id,
                    'rekening_id' => $trx->wallet->rekening_id ?? 1,
                    'kategori_id' => 1,
                    'lembaga_id' => $trx->wallet->lembaga_id ?? 1,
                ]);
            } else {
                $trx->update(['status' => 'failed']);
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
            } else {
                $pembayaran->update(['status' => 'gagal']);
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

                if ($statusSebelumnya !== 'active') {
                    try {
                        NotificationService::sendAplikasiAktif($yayasan);
                    } catch (\Throwable $e) {
                        Log::error("DokuWebhookController: gagal kirim notif aplikasi aktif untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                    }
                }
            } else {
                $payment->update(['status' => 'gagal']);
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
