<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\WalletTransaction;
use App\Services\DokuService;

class TopupController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('wallet')
            ->find(session('siswa_id'));

        if (!$siswa) {
            return redirect()->route('wali.login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        return view('wali.topup', [
            'siswa'  => $siswa,
            'wallet' => $siswa->wallet
        ]);
    }

    /**
     * Langkah 1: simpan nominal yang dipilih ke session, lalu arahkan
     * ke halaman pilih metode pembayaran -- alurnya disamakan dengan
     * pembayaran tagihan (pilih dulu apa yang mau dibayar/berapa,
     * BARU pilih metode, bukan digabung 1 form panjang).
     */
    public function pilihNominal(Request $request)
    {
        $amount = (int) ($request->custom_amount ?: $request->amount);

        if (!$amount || $amount < 10000) {
            return back()->with('error', 'Nominal minimal Rp 10.000');
        }

        session(['topup_amount' => $amount]);

        return redirect()->route('wali.topup.metode');
    }

    /**
     * Langkah 2: halaman pilih metode pembayaran (VA/QRIS/e-wallet/
     * minimarket), tampilan SAMA seperti wali/pembayaran/{tagihan}/doku
     * (resources/views/wali/topup-metode.blade.php meniru
     * wali/doku.blade.php).
     */
    public function showMetode()
    {
        $amount = session('topup_amount');

        if (!$amount) {
            return redirect()->route('wali.topup')->with('error', 'Silakan pilih nominal top up terlebih dahulu.');
        }

        return view('wali.topup-metode', ['amount' => $amount]);
    }

    public function store(Request $request, DokuService $doku)
    {
        $amount = (int) session('topup_amount');

        if (!$amount || $amount < 10000) {
            return redirect()->route('wali.topup')->with('error', 'Silakan pilih nominal top up terlebih dahulu.');
        }

        $request->validate([
            'payment_method' => 'required|in:VA,QRIS,DANA,SHOPEEPAY,ALFAMART,INDOMARET,OVO',
            'bank' => 'nullable|in:BCA,BNI,BRI,BSI,MANDIRI,BJB',
            'ovo_phone' => 'required_if:payment_method,OVO|nullable|string|min:9|max:15',
        ]);

        $channel = $request->payment_method;

        $siswa = Siswa::with('wallet')
            ->find(session('siswa_id'));

        if (!$siswa) {
            return back()->with('error', 'User tidak ditemukan');
        }

        if (!$siswa->wallet) {
            return back()->with('error', 'Wallet tidak ditemukan');
        }

        $wallet = $siswa->wallet;
        $customerName = $siswa->nama_lengkap;
        $customerEmail = DokuService::emailAman($siswa->email, $siswa->id);

        $feeAdmin = DokuService::hitungFeeTotal($amount, $channel);
        $amountCharged = $amount + $feeAdmin; // yang di-charge ke wali; saldo wallet tetap dikredit $amount penuh

        $reference = 'TOPUP-' . $siswa->id . '-' . time();
        session()->forget('topup_amount');

        $trx = WalletTransaction::create([
            'wallet_id'    => $wallet->id,
            'type'         => 'topup',
            'amount'       => $amount,
            'status'       => 'pending',
            'reference_id' => $reference,
            'gateway'      => 'doku',
            'description'  => 'Top Up Saldo via DOKU',
        ]);

        try {
            // DIGANTI -- pakai DOKU Checkout (Non-SNAP, 1 endpoint) untuk
            // semua channel, sama seperti WaliDashboardController::doku()
            // -- lihat catatan lengkap di sana soal alasan perubahan ini.
            $result = $doku->buatPaymentRequest(
                referenceId: $reference,
                amount: $amountCharged,
                customerName: $customerName,
                customerEmail: $customerEmail,
                judul: 'Top Up Saldo',
                channel: $channel,
                bank: $request->bank,
                callbackUrl: route('wali.topup'),
            );

            $paymentUrl = $result['response']['payment']['url'] ?? null;

            if (!$paymentUrl) {
                $trx->update(['status' => 'failed']);

                return back()->with('error', DokuService::pesanAman($result['error_messages'] ?? $result['message'] ?? null));
            }
        } catch (\Throwable $e) {
            $trx->update(['status' => 'failed']);

            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        return redirect()->away($paymentUrl);
    }

    public function status(string $reference)
    {
        $trx = WalletTransaction::where('reference_id', $reference)->first();

        abort_if(!$trx, 404);

        return response()->json([
            'status' => $trx->status,
        ]);
    }
}
