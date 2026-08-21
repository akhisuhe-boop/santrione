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
     * Halaman Transfer Bank
     */
    public function showTransferForm(Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));
    
        abort_if($tagihan->ppdb_id != $ppdb->id, 403);
    
        $isCicilan = optional($tagihan->jenisTagihan)->is_cicilan ?? false;
    
        $sisaTagihan = max(
            $tagihan->nominal - $tagihan->nominal_terbayar,
            0
        );
    
        return view('ppdb.transfer', [
            'ppdb'         => $ppdb,
            'yayasan'      => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
            'tagihan'      => $tagihan,
            'isCicilan'    => $isCicilan,
            'sisaTagihan'  => $sisaTagihan,
        ]);
    }

    /**
     * Upload Bukti Transfer
     */
    public function bayarTransfer(Request $request, Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));
    
        abort_if($tagihan->ppdb_id != $ppdb->id, 403);
    
        $sisa = $tagihan->nominal - $tagihan->nominal_terbayar;

        $rules = [
            'bukti_transfer_ppdb' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];

        // Kalau tagihan ini boleh dicicil, terima input nominal dari
        // form (dulu di-hardcode selalu full, TODO lama yang belum
        // dikerjakan). Kalau TIDAK boleh dicicil, tetap wajib bayar
        // penuh sesuai sisa tagihan.
        if ($tagihan->is_cicilan) {
            $rules['nominal'] = ['required', 'numeric', 'min:1', 'max:' . $sisa];
        }

        $request->validate($rules);

        $nominal = $tagihan->is_cicilan
            ? (int) $request->input('nominal')
            : $sisa;

        // Cek apakah masih ada pembayaran pending
        $pending = Pembayaran::where('tagihan_id', $tagihan->id)
            ->where('status', 'pending')
            ->exists();
    
        if ($pending) {
            return back()->with(
                'error',
                'Masih ada pembayaran yang sedang menunggu verifikasi.'
            );
        }
    
        // Upload bukti transfer
        $path = $request
            ->file('bukti_transfer_ppdb')
            ->store('ppdb-transfer', 'r2-private');
    
        // Simpan pembayaran
        Pembayaran::create([
            'tagihan_id'     => $tagihan->id,
            'ppdb_id'        => $ppdb->id,
            'nominal'        => $nominal,
            'metode'         => 'transfer',
            'reference'      => 'PPDB-' . now()->format('YmdHis') . rand(100,999),
            'status'         => 'pending',
            'bukti_transfer' => $path,
            'tanggal_bayar'  => now(),
        ]);
    
        return redirect()
            ->route('ppdb.pembayaran.transfer', $tagihan)
            ->with(
                'success',
                'Bukti transfer berhasil dikirim dan sedang menunggu verifikasi.'
            );
    }
    
    /**
     * Halaman DOKU
     */
    public function showDokuForm(Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        $paymentMethods = collect([
            'BCA' => ['code' => 'BCA', 'name' => 'BCA Virtual Account', 'category' => 'Virtual Account'],
            'BNI' => ['code' => 'BNI', 'name' => 'BNI Virtual Account', 'category' => 'Virtual Account'],
            'BRI' => ['code' => 'BRI', 'name' => 'BRI Virtual Account', 'category' => 'Virtual Account'],
            'BSI' => ['code' => 'BSI', 'name' => 'BSI Virtual Account', 'category' => 'Virtual Account'],
            'MANDIRI' => ['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'category' => 'Virtual Account'],
            'OV' => ['code' => 'OV', 'name' => 'OVO', 'category' => 'E-Wallet'],
            'DA' => ['code' => 'DA', 'name' => 'DANA', 'category' => 'E-Wallet'],
            'SP' => ['code' => 'SP', 'name' => 'ShopeePay', 'category' => 'E-Wallet'],
            'QRIS' => ['code' => 'QRIS', 'name' => 'QRIS', 'category' => 'QRIS'],
        ]);

        return view('ppdb.doku', [
            'ppdb' => $ppdb,
            'yayasan' => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
            'tagihan' => $tagihan,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Proses DOKU -- pola PERSIS WaliDashboardController::doku(), cuma
     * beda prefix reference_id ('PPDB-' bukan 'TAGIHAN-') supaya
     * DokuWebhookController bisa membedakan keduanya, dan beda sumber
     * identitas pembayar (Ppdb, bukan Siswa -- pendaftar PPDB belum
     * tentu sudah jadi Siswa).
     */
    public function doku(Request $request, Tagihan $tagihan, \App\Services\DokuService $doku)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $vaBanks = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'BSI'];
        $ewalletCodes = ['OV', 'DA', 'SP'];
        $allowedMethods = array_merge($vaBanks, $ewalletCodes, ['QRIS']);

        if (!in_array($request->payment_method, $allowedMethods)) {
            return back()->with('error', 'Metode pembayaran tidak valid.');
        }

        $amount = (int) ($tagihan->nominal - $tagihan->nominal_terbayar);
        $referenceId = 'PPDB-' . $tagihan->id . '-' . time();

        $channel = match (true) {
            in_array($request->payment_method, $vaBanks, true) => 'VA',
            in_array($request->payment_method, $ewalletCodes, true) => 'EWALLET',
            default => 'QRIS',
        };

        $lembaga = $ppdb->lembaga;

        try {
            $result = $doku->buatPaymentRequest(
                referenceId: $referenceId,
                amount: $amount,
                customerName: $ppdb->nama_lengkap ?? $ppdb->nama ?? 'Pendaftar PPDB',
                customerEmail: \App\Services\DokuService::emailAman($ppdb->email ?? null, $ppdb->wa_wali ?? $ppdb->id),
                judul: $tagihan->judul,
                channel: $channel,
                bank: $request->payment_method,
                dokuSubAccountId: $lembaga?->doku_sub_account_id,
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }

        $paymentUrl = $result['response']['url']
            ?? $result['payment']['url']
            ?? $result['virtual_account_info']['virtual_account_number'] ?? null;

        if (!$paymentUrl) {
            return back()->with('error', $result['message'] ?? 'Gagal membuat pembayaran');
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

        if ($channel === 'VA' && !str_starts_with((string) $paymentUrl, 'http')) {
            return back()->with('success', 'Silakan transfer ke nomor VA: ' . $paymentUrl);
        }

        return redirect()->away($paymentUrl);
    }
}