<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Xendit -- INI yang membuat "tidak ada konfirmasi manual"
 * jadi nyata: begitu wali bayar QRIS, Xendit panggil endpoint ini
 * otomatis, Pembayaran langsung ditandai sukses, dan
 * Pembayaran::booted() (sudah ada di model, tidak disentuh di sini)
 * otomatis update Tagihan + catat Kas.
 *
 * PENTING: payload di bawah (nama field 'status', 'reference_id',
 * dst) mengikuti format webhook Payment Request Xendit yang
 * terdokumentasi -- BELUM diuji terhadap webhook sungguhan (lihat
 * catatan di XenditService). Kalau format aslinya beda, cek
 * Log Webhook di dashboard Xendit untuk lihat payload asli, sesuaikan
 * di sini.
 */
class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->header('x-callback-token');
        $expected = config('services.xendit.webhook_token');

        if (! $expected || $token !== $expected) {
            Log::warning('XenditWebhookController: token tidak valid', ['ip' => $request->ip()]);

            return response('INVALID TOKEN', 403);
        }

        $referenceId = $request->input('reference_id') ?? $request->input('data.reference_id');
        $status = $request->input('status') ?? $request->input('data.status');

        if (! $referenceId) {
            return response('MISSING reference_id', 400);
        }

        $pembayaran = Pembayaran::where('reference', $referenceId)->first();

        if (! $pembayaran) {
            Log::warning('XenditWebhookController: Pembayaran tidak ditemukan', ['reference_id' => $referenceId]);

            return response('NOT FOUND', 404);
        }

        // Idempotent -- webhook Xendit bisa terkirim lebih dari sekali
        // untuk event yang sama, jangan proses ulang kalau sudah sukses.
        if ($pembayaran->status === 'sukses') {
            return response('OK (already processed)');
        }

        DB::beginTransaction();

        try {
            $suksesStatuses = ['SUCCEEDED', 'PAID', 'COMPLETED'];

            if (in_array(strtoupper((string) $status), $suksesStatuses, true)) {
                $pembayaran->status = 'sukses';
                $pembayaran->tanggal_bayar = now();
                $pembayaran->save();

                NotificationService::sendPembayaran($pembayaran->siswa, $pembayaran);
            } else {
                $pembayaran->update(['status' => 'gagal']);
            }

            DB::commit();

            return response('OK');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('XenditWebhookController: error memproses webhook', [
                'reference_id' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }
}
