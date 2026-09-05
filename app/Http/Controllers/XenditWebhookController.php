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

        // Webhook Invoice (dipakai billing langganan Yayasan->Qinara,
        // lihat XenditSubscriptionService) -- field 'external_id' &
        // 'status' PAID/EXPIRED, beda struktur dari webhook Payment
        // Request (split payment wali) di bawah.
        $externalId = $request->input('external_id');

        if ($externalId && str_starts_with((string) $externalId, 'SUB-')) {
            return $this->handleSubscriptionInvoice($request, $externalId);
        }

        return $this->handlePaymentRequestSplit($request);
    }

    /**
     * Webhook Invoice untuk billing langganan -- mengikuti pola
     * PERSIS DuitkuController::handleSubscriptionCallback(), cuma
     * beda nama field status (Xendit: 'PAID', Duitku: resultCode
     * '00') dan field ID (external_id vs merchantOrderId).
     */
    protected function handleSubscriptionInvoice(Request $request, string $externalId)
    {
        $payment = \App\Models\SubscriptionPayment::where('gateway_order_id', $externalId)->first();

        if (! $payment) {
            Log::warning('XenditWebhookController: SubscriptionPayment tidak ditemukan', ['external_id' => $externalId]);

            return response('NOT FOUND', 404);
        }

        if ($payment->status === 'berhasil') {
            return response('OK (already processed)');
        }

        DB::beginTransaction();

        try {
            $payment->update([
                'gateway_transaction_id' => $request->input('id'),
                'gateway_raw_response' => $request->all(),
            ]);

            $status = strtoupper((string) $request->input('status'));

            if ($status === 'PAID' || $status === 'SETTLED') {

                $payment->update(['status' => 'berhasil']);

                $subscription = $payment->subscription;
                $yayasan = $subscription->yayasan;

                // Cek status SEBELUM update -- supaya notifikasi "aplikasi
                // aktif" cuma terkirim saat AKTIVASI PERTAMA (trial->active
                // atau suspended->active), BUKAN tiap perpanjangan bulanan
                // rutin (yang sudah punya notifikasi tagihan sendiri).
                $statusSebelumnya = $yayasan->status;

                $subscription->update([
                    'status' => 'active',
                    'mulai_pada' => now(),
                    'berakhir_pada' => $subscription->isTahunan() ? now()->addYear() : now()->addMonth(),
                ]);

                $yayasan->update(['status' => 'active']);

                // Kalau plan yang baru dibayar ini "termasuk semua modul"
                // (mis. Paket Full), nyalakan semua modul di semua
                // Lembaga milik yayasan ini SEKARANG -- setelah pembayaran
                // beneran sukses, BUKAN lagi langsung waktu tenant klik
                // tombol "Aktifkan Paket Full" (celah billing ditemukan
                // 4 Sep 2026, lihat Langganan::aktifkanPaketFull()).
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
                        \App\Services\NotificationService::sendAplikasiAktif($yayasan);
                    } catch (\Throwable $e) {
                        Log::error("XenditWebhookController: gagal kirim notif aplikasi aktif untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                    }
                }

            } else {
                $payment->update(['status' => 'gagal']);
            }

            DB::commit();

            return response('OK');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('XenditWebhookController: error memproses webhook invoice langganan', [
                'external_id' => $externalId,
                'message' => $e->getMessage(),
            ]);

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Webhook Payment Request (split payment wali murid) -- logic
     * SEBELUMNYA di method handle(), dipindah ke sini tanpa diubah
     * saat menambah cabang Invoice di atas.
     */
    protected function handlePaymentRequestSplit(Request $request)
    {
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
