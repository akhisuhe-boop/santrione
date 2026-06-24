<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\WalletTransaction;
use App\Http\Controllers\DuitkuController;

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

    public function store(Request $request)
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
            'description'  => 'Top Up Saldo via Duitku',
        ]);

        $duitku = app(DuitkuController::class);

        $paymentUrl = $duitku->createTopup([
            'amount'    => $amount,
            'customer'  => $siswa->nama_lengkap,
            'email'     => $siswa->email ?? 'demo@mail.com',
            'reference' => $reference,
        ]);

        // =========================
        // 🔥 FIX ERROR ARRAY VS STRING
        // =========================
        if (is_array($paymentUrl)) {
            $paymentUrl = $paymentUrl['paymentUrl'] ?? null;
        }

        if (!$paymentUrl) {
            $trx->update(['status' => 'failed']);

            return back()->with('error', 'Gagal membuat pembayaran');
        }

        return redirect()->away($paymentUrl);
    }
}