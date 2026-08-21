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

    public function store(Request $request, DokuService $doku)
    {
        $amount = $request->custom_amount ?: $request->amount;

        if (!$amount || $amount < 10000) {
            return back()->with('error', 'Nominal minimal Rp 10.000');
        }

        $siswa = Siswa::with('wallet')
            ->find(session('siswa_id'));

        if (!$siswa) {
            return back()->with('error', 'User tidak ditemukan');
        }

        if (!$siswa->wallet) {
            return back()->with('error', 'Wallet tidak ditemukan');
        }

        $wallet = $siswa->wallet;

        $reference = 'TOPUP-' . $siswa->id . '-' . time();

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
            $result = $doku->buatPaymentRequest(
                referenceId: $reference,
                amount: (int) $amount,
                customerName: $siswa->nama_lengkap,
                customerEmail: \App\Services\DokuService::emailAman($siswa->email, $siswa->id),
                judul: 'Top Up Saldo',
                channel: 'QRIS'
            );
        } catch (\Throwable $e) {
            $trx->update(['status' => 'failed']);

            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        // Field ini SUDAH DIKONFIRMASI dari respons sandbox asli
        // (bukan lagi tebakan) -- DOKU Checkout membalas URL redirect
        // di response.payment.url.
        $paymentUrl = $result['response']['payment']['url'] ?? null;

        if (!$paymentUrl) {
            $trx->update(['status' => 'failed']);

            return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['error']['message'] ?? null, 'Gagal membuat pembayaran (URL tidak ditemukan di respons DOKU)'));
        }

        return redirect()->away($paymentUrl);
    }
}
