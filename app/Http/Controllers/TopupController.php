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

        $channel = $request->payment_method === 'QRIS' ? 'QRIS' : 'VA';

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
            if ($channel === 'VA') {
                $result = $doku->buatVaLangsung(
                    referenceId: $reference,
                    amount: (int) $amount,
                    judul: 'Top Up Saldo',
                    customerName: $siswa->nama_lengkap,
                    customerEmail: DokuService::emailAman($siswa->email, $siswa->id),
                );

                $vaNumber = $result['virtual_account_info']['virtual_account_number'] ?? null;

                if (!$vaNumber) {
                    $trx->update(['status' => 'failed']);

                    return back()->with('error', DokuService::pesanAman($result['message'] ?? $result['error']['message'] ?? null));
                }
            } else {
                $result = $doku->buatQris(
                    referenceId: $reference,
                    amount: (int) $amount,
                );

                $qrString = $result['qrContent'] ?? $result['qrUrl'] ?? null;

                if (!$qrString) {
                    $trx->update(['status' => 'failed']);

                    return back()->with('error', DokuService::pesanAman($result['message'] ?? $result['responseMessage'] ?? null));
                }
            }
        } catch (\Throwable $e) {
            $trx->update(['status' => 'failed']);

            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        return view('payment.checkout', [
            'layout' => 'wali.layout.wali',
            'namaLembaga' => $siswa->lembaga?->nama ?? $siswa->yayasan?->nama ?? 'Qinara',
            'logo' => $siswa->lembaga?->logo ? \Storage::disk('r2-public')->url($siswa->lembaga->logo) : null,
            'referenceId' => $reference,
            'judul' => 'Top Up Saldo',
            'amount' => (int) $amount,
            'channel' => $channel,
            'vaNumber' => $vaNumber ?? null,
            'qrString' => $qrString ?? null,
            'countdownTo' => now()->addMinutes(60)->toIso8601String(),
            'statusUrl' => route('wali.topup.status', $reference),
            'successUrl' => route('wali.topup'),
        ]);
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
