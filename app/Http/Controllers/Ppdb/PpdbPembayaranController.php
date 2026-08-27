<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use App\Models\Pembayaran;

class PpdbPembayaranController extends Controller
{
    /**
     * Halaman pembayaran PPDB
     */
    public function index()
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        $tagihan = Tagihan::with([
                'jenisTagihan',
                'rekening',
            ])
            ->where('ppdb_id', $ppdb->id)
            ->latest()
            ->first();

        // Fallback untuk pendaftar lama yang belum sempat punya tagihan
        // otomatis (didaftarkan sebelum fitur ini ada, atau Jenis Tagihan
        // "Biaya Pendaftaran PPDB" baru diisi setelah pendaftar itu daftar).
        if (!$tagihan) {

            $tagihan = Tagihan::pastikanTagihanPendaftaranPpdb($ppdb);

            abort_if(!$tagihan, 404, 'Tagihan biaya pendaftaran belum tersedia. Silakan hubungi admin sekolah.');

            $tagihan->load(['jenisTagihan', 'rekening']);
        }

        $isCicilan = optional($tagihan->jenisTagihan)->is_cicilan ?? false;

        $sisaTagihan = max(
            $tagihan->nominal - $tagihan->nominal_terbayar,
            0
        );

        return view('ppdb.pembayaran', [
            'ppdb'         => $ppdb,
            'yayasan'      => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
            'tagihan'      => $tagihan,
            'isCicilan'    => $isCicilan,
            'sisaTagihan'  => $sisaTagihan,
        ]);
    }

    /**
     * Halaman DOKU
     */
    public function showDokuForm(Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        return view('ppdb.doku', [
            'ppdb' => $ppdb,
            'yayasan' => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
            'tagihan' => $tagihan,
        ]);
    }

    /**
     * Proses DOKU -- checkout custom (VA universal / QRIS), branding
     * sekolah/yayasan sendiri, TANPA redirect ke domain DOKU. Sama
     * persis polanya dengan WaliDashboardController::doku(), cuma beda
     * prefix reference_id ('PPDB-' bukan 'TAGIHAN-') supaya
     * DokuWebhookController bisa membedakan keduanya, dan beda sumber
     * identitas pembayar (Ppdb, bukan Siswa -- pendaftar PPDB belum
     * tentu sudah jadi Siswa).
     */
    public function doku(Request $request, Tagihan $tagihan, \App\Services\DokuService $doku)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        $request->validate([
            'payment_method' => 'required|in:VA,QRIS',
        ]);

        $amount = (int) ($tagihan->nominal - $tagihan->nominal_terbayar);
        $referenceId = 'PPDB-' . $tagihan->id . '-' . time();
        $channel = $request->payment_method;

        $lembaga = $ppdb->lembaga;

        try {
            // DIGANTI -- pakai DOKU Checkout (Non-SNAP, 1 endpoint) untuk
            // semua channel, sama seperti WaliDashboardController::doku()
            // -- lihat catatan lengkap di sana soal alasan perubahan ini.
            $result = $doku->buatPaymentRequest(
                referenceId: $referenceId,
                amount: $amount,
                customerName: $ppdb->nama_lengkap ?? $ppdb->nama ?? 'Pendaftar PPDB',
                customerEmail: \App\Services\DokuService::emailAman($ppdb->email ?? null, $ppdb->wa_wali ?? $ppdb->id),
                judul: $tagihan->judul,
                channel: $channel,
                dokuSubAccountId: $lembaga?->doku_sub_account_id,
                callbackUrl: route('ppdb.pembayaran'),
            );

            $paymentUrl = $result['response']['payment']['url'] ?? null;

            if (!$paymentUrl) {
                return back()->with('error', \App\Services\DokuService::pesanAman($result['error_messages'] ?? $result['message'] ?? null));
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'siswa_id' => $tagihan->siswa_id,
            'nominal' => $amount,
            'metode' => 'gateway',
            'gateway' => 'doku',
            'status' => 'pending',
            'reference' => $referenceId,
        ]);

        return redirect()->away($paymentUrl);
    }

    public function statusDoku(string $reference)
    {
        $pembayaran = Pembayaran::where('reference', $reference)->first();

        abort_if(!$pembayaran, 404);

        return response()->json([
            'status' => $pembayaran->status,
        ]);
    }
}