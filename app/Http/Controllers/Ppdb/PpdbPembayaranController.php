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
            ->firstOrFail();

        $isCicilan = optional($tagihan->jenisTagihan)->is_cicilan ?? false;

        $sisaTagihan = max(
            $tagihan->nominal - $tagihan->nominal_terbayar,
            0
        );

        return view('ppdb.pembayaran', [
            'ppdb'         => $ppdb,
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
    
        $request->validate([
            'bukti_transfer_ppdb' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);
    
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
            ->store('ppdb-transfer', 'public');
    
        // Simpan pembayaran
        Pembayaran::create([
            'tagihan_id'     => $tagihan->id,
            'ppdb_id'        => $ppdb->id,
            'nominal'        => $tagihan->nominal, // nanti saat cicilan gunakan $request->nominal
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
     * Halaman Duitku
     */
    public function showDuitkuForm(Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        return view('ppdb.duitku', [
            'ppdb' => $ppdb,
            'tagihan' => $tagihan,
        ]);
    }

    /**
     * Proses Duitku
     */
    public function duitku(Request $request, Tagihan $tagihan)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        abort_if($tagihan->ppdb_id != $ppdb->id, 403);

        /*
        |--------------------------------------------------------------------------
        | Nanti di sini tinggal copy logic Duitku milik Wali
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('ppdb.pembayaran')
            ->with(
                'success',
                'Redirect ke Payment Gateway.'
            );
    }
}