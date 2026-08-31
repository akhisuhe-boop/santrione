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

        // DIPERLUAS -- sebelumnya cuma VA & QRIS, sekarang channelnya
        // disamakan dengan WaliDashboardController::doku() /
        // TopupController::store() (DANA, ShopeePay, OVO, Alfamart,
        // Indomaret ikut ditambahkan) supaya UI "Metode Pembayaran"
        // PPDB konsisten dengan pembayaran tagihan/top up.
        $request->validate([
            'payment_method' => 'required|in:VA,QRIS,DANA,SHOPEEPAY,ALFAMART,INDOMARET,OVO',
            'bank' => 'nullable|in:BCA,BNI,BRI,BSI,MANDIRI,BJB',
            'ovo_phone' => 'required_if:payment_method,OVO|nullable|string|min:9|max:15',
        ]);

        $amount = (int) ($tagihan->nominal - $tagihan->nominal_terbayar);
        $referenceId = 'PPDB-' . $tagihan->id . '-' . time();
        $channel = $request->payment_method;
        // DITAMBAHKAN -- disamakan dengan WaliDashboardController::doku()
        // & TopupController::store(): biaya admin (fee Qinara + estimasi
        // fee DOKU per channel) sebelumnya TIDAK dikenakan sama sekali
        // untuk PPDB, sekarang disamakan.
        $feeAdmin = \App\Services\DokuService::hitungFeeTotal($amount, $channel);
        $amountCharged = $amount + $feeAdmin;

        $lembaga = $ppdb->lembaga;
        $customerName = $ppdb->nama_lengkap ?? $ppdb->nama ?? 'Pendaftar PPDB';
        $customerEmail = \App\Services\DokuService::emailAman($ppdb->email ?? null, $ppdb->wa_wali ?? $ppdb->id);

        $vaNumber = null;
        $qrString = null;
        $paymentCode = null;
        $redirectUrl = null;
        $howToPayPage = null;

        try {
            if ($channel === 'VA') {
                // Lihat catatan lengkap di WaliDashboardController::doku()
                // -- VA Non-SNAP (buatVaLangsung) TERBUKTI SUKSES di
                // sandbox, VA SNAP/DOKU Checkout Link TIDAK ANDAL.
                $result = $doku->buatVaLangsung(
                    referenceId: $referenceId,
                    amount: $amountCharged,
                    judul: $tagihan->judul,
                    customerName: $customerName,
                    customerEmail: $customerEmail,
                    dokuSubAccountId: $lembaga?->doku_sub_account_id,
                    splitRuleId: $lembaga?->doku_split_rule_id,
                );

                $vaNumber = $result['virtual_account_info']['virtual_account_number'] ?? null;
                $howToPayPage = $result['virtual_account_info']['how_to_pay_page'] ?? null;

                if (!$vaNumber) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['error']['message'] ?? null));
                }
            } elseif ($channel === 'QRIS') {
                // CATATAN: QRIS belum aktif di akun DOKU ini (proses
                // aktivasi oleh support DOKU) -- akan gagal sampai
                // aktivasi selesai, bukan bug kode.
                $result = $doku->buatQris(
                    referenceId: $referenceId,
                    amount: $amountCharged,
                );

                $qrString = $result['qrContent'] ?? $result['qrUrl'] ?? null;

                if (!$qrString) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['responseMessage'] ?? null));
                }
            } elseif (in_array($channel, ['DANA', 'SHOPEEPAY'], true)) {
                $result = $doku->buatEwalletSnap(
                    channel: $channel === 'DANA' ? 'EMONEY_DANA_SNAP' : 'EMONEY_SHOPEE_PAY_SNAP',
                    referenceId: $referenceId,
                    amount: $amountCharged,
                    returnUrl: route('ppdb.pembayaran'),
                    judul: $tagihan->judul,
                );

                $redirectUrl = $result['webRedirectUrl'] ?? null;

                if (!$redirectUrl) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['responseMessage'] ?? null));
                }
            } elseif (in_array($channel, ['ALFAMART', 'INDOMARET'], true)) {
                $result = $doku->buatOtc(
                    toko: $channel,
                    referenceId: $referenceId,
                    amount: $amountCharged,
                    customerName: $customerName,
                    customerEmail: $customerEmail,
                );

                $paymentCode = $result['online_to_offline_info']['payment_code'] ?? null;
                $howToPayPage = $result['online_to_offline_info']['how_to_pay_page'] ?? null;

                if (!$paymentCode) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['error']['message'] ?? null));
                }
            } else { // OVO
                $result = $doku->buatOvo(
                    referenceId: $referenceId,
                    amount: $amountCharged,
                    ovoId: $request->ovo_phone,
                );

                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'siswa_id' => $tagihan->siswa_id,
                    'nominal' => $amount,
                    'fee_admin' => $feeAdmin,
                    'metode' => 'gateway',
                    'gateway' => 'doku',
                    'status' => 'pending',
                    'reference' => $referenceId,
                ]);

                $statusOvo = $result['transaction']['status'] ?? null;

                return redirect()->route('ppdb.pembayaran')
                    ->with(
                        $statusOvo === 'SUCCESS' ? 'success' : 'error',
                        'Status OVO: ' . \App\Services\DokuService::pesanAman($result['message'] ?? null, 'Menunggu konfirmasi dari HP Anda')
                    );
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'siswa_id' => $tagihan->siswa_id,
            'nominal' => $amount,
            'fee_admin' => $feeAdmin,
            'metode' => 'gateway',
            'gateway' => 'doku',
            'status' => 'pending',
            'reference' => $referenceId,
        ]);

        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }

        return view('payment.checkout', [
            'layout' => 'ppdb.layout.ppdb',
            'namaLembaga' => $lembaga?->nama ?? $ppdb->lembaga?->yayasan?->nama ?? 'Qinara',
            'logo' => $lembaga?->logo ? \Storage::disk('r2-public')->url($lembaga->logo) : null,
            'referenceId' => $referenceId,
            'judul' => $tagihan->judul,
            'amount' => $amount,
            'feeAdmin' => $feeAdmin,
            'amountCharged' => $amountCharged,
            'channel' => $channel,
            'bankDipilih' => $request->bank,
            'vaNumber' => $vaNumber,
            'qrString' => $qrString,
            'paymentCode' => $paymentCode,
            'howToPayPage' => $howToPayPage,
            'countdownTo' => now()->addMinutes(in_array($channel, ['ALFAMART', 'INDOMARET'], true) ? 1440 : 60)->toIso8601String(),
            'statusUrl' => route('ppdb.pembayaran.doku.status', $referenceId),
            'successUrl' => route('ppdb.pembayaran'),
        ]);
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