<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use App\Models\Kas;
use App\Models\Pembayaran;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\RaportNonAkademik;
use App\Models\Nilai;
use App\Models\RekapNilai;
use App\Models\Kurikulum;
use App\Models\Perizinan;
use App\Models\Announcement;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with([
            'kelas',
            'asrama',
            'wallet',
            'tagihans',
            'absensis',
            'tahfidzSetoran',
            'pelanggaranSiswa',
            'prestasiSiswa',
        ])->findOrFail(session('siswa_id'));

        $semesterAktif = TahunAjaran::where('aktif', true)->first();
        $wallet = $siswa->wallet;
        $tagihanAktif = $siswa->tagihans()
            ->where('status', '!=', 'Lunas')
            ->latest()
            ->take(2)
            ->get();

        $totalTagihan = $siswa->tagihans
        ->filter(function ($tagihan) {
            return strtolower(trim($tagihan->status)) !== 'lunas';
        })
        ->sum(function ($tagihan) {

            return max(
                0,
                $tagihan->nominal - $tagihan->nominal_terbayar
            );

        });

        $pengumuman = Announcement::visibleFor(
            'wali',
            $siswa->kelas_id
        )
        ->take(2)
        ->get();

        return view('wali.dashboard', compact(
            'siswa',
            'wallet',
            'semesterAktif',
            'tagihanAktif',
            'totalTagihan',
            'pengumuman'
        ));
    }

    public function akademik()
    {
        $siswa = Siswa::with([
            'kelas',
            'asrama',
            'absensis',
            'prestasiSiswa',
            'pelanggaranSiswa',
        ])->findOrFail(session('siswa_id'));

        return view('wali.akademik', compact('siswa'));
    }

    public function tahfidz()
    {
        $siswa = Siswa::with([
            'tahfidzSetoran.surah',
            'tahfidzSetoran.juz',
            'targetTahfidz',
        ])->findOrFail(session('siswa_id'));

        return view('wali.tahfidz', compact('siswa'));
    }

    public function pengumuman()
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        $announcements = Announcement::query()
        ->where(function ($q) use ($siswa) {
            $q->where('target_type', 'all');

            if ($siswa->kelas_id) {
                $q->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'kelas')
                    ->where('kelas_id', $siswa->kelas_id);
                });
            }

            $q->orWhere(function ($q) {
                $q->where('target_type', 'role')
                ->where('target_role', 'wali');
            });
        })
        ->orderByDesc('is_pinned')
        ->orderByDesc('created_at')
        ->latest()
        ->limit(2)
        ->get();

    return view('wali.pengumuman', compact('announcements'));
    }

    public function keuangan()
    {
        $siswa = Siswa::with([
            'wallet',
            'tagihans.pembayarans',
        ])->findOrFail(session('siswa_id'));

        $wallet = $siswa->wallet;

        $tagihanAktif = $siswa->tagihans
            ->filter(function ($tagihan) {
                return strtolower(trim($tagihan->status)) !== 'lunas';
            })
            ->sortByDesc('created_at');

        $riwayatPembayaran = Pembayaran::with('tagihan')
        ->where('siswa_id', $siswa->id)
        ->where('status', 'sukses')
        ->latest('tanggal_bayar')
        ->take(10)
        ->get();

        return view(
            'wali.keuangan',
            compact(
                'siswa',
                'wallet',
                'tagihanAktif',
                'riwayatPembayaran'
            )
        );
    }

    public function kantin()
    {
        $siswa = Siswa::with('wallet')->findOrFail(session('siswa_id'));

        $yayasan = \App\Models\Yayasan::find(session('active_public_yayasan_id'));

        abort_unless(
            $yayasan?->hasFeature(\App\Support\FeatureGate::E_KANTIN),
            403,
            'Fitur Kantin belum aktif untuk yayasan ini.'
        );

        $riwayatKantin = \App\Models\KantinTransaksi::withoutGlobalScopes()
            ->with('items')
            ->where('siswa_id', $siswa->id)
            ->latest('tanggal')
            ->paginate(15);

        $belanjaHariIni = \App\Models\KantinTransaksi::withoutGlobalScopes()
            ->where('siswa_id', $siswa->id)
            ->where('metode', 'wallet')
            ->whereDate('tanggal', today())
            ->sum('total');

        return view('wali.kantin', compact('siswa', 'riwayatKantin', 'belanjaHariIni'));
    }

    public function updateLimitKantin(\Illuminate\Http\Request $request)
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        $request->validate([
            'limit_harian_kantin' => ['nullable', 'numeric', 'min:0'],
        ]);

        $siswa->update([
            'limit_harian_kantin' => $request->limit_harian_kantin ?: null,
        ]);

        return back()->with('success', 'Limit belanja harian berhasil diperbarui.');
    }

    public function absensi()
    {
        $siswa = Siswa::with([

            'absensis.jadwalKegiatan.template',

            'absensiMapels.jadwalPelajaran.mataPelajaran',
            'absensiMapels.jadwalPelajaran.guru',

            'absensiHarians',

        ])->findOrFail(session('siswa_id'));

        return view(
            'wali.absensi',
            compact('siswa')
        );
    }

    public function perizinan()
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        $perizinans = Perizinan::where('siswa_id', $siswa->id)
            ->latest()
            ->get();

        return view('wali.perizinan', compact('siswa', 'perizinans'));
    }

    public function storePerizinan(Request $request)
    {
        $request->validate([
            'tipe' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'keperluan' => 'required',
        ]);

        Perizinan::create([
            'siswa_id' => session('siswa_id'),
            'tipe' => $request->tipe,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keperluan' => $request->keperluan,
            'penjemput' => $request->penjemput,
            'hubungan' => $request->hubungan,
            'no_wa' => $request->no_wa,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('wali.perizinan')
            ->with('success', 'Izin berhasil diajukan');
    }

    public function izinTidakMasuk()
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        $izinHarians = \App\Models\IzinHarian::where('siswa_id', $siswa->id)
            ->where('tipe', 'siswa')
            ->latest()
            ->get();

        return view('wali.izin-tidak-masuk', compact('siswa', 'izinHarians'));
    }

    public function storeIzinTidakMasuk(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:Izin,Sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = null;

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('izin-harian', 'r2-private');
        }

        \App\Models\IzinHarian::create([
            'siswa_id' => session('siswa_id'),
            'tipe' => 'siswa',
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'lampiran' => $path,
            'status' => 'pending',
            'diajukan_oleh' => auth()->id(),
        ]);

        return redirect()
            ->route('wali.izin-tidak-masuk')
            ->with('success', 'Pengajuan izin berhasil dikirim, menunggu persetujuan admin');
    }

    public function pelanggaran()
    {
        $siswa = Siswa::with([
            'pelanggaranSiswa.pelanggaran'
        ])->findOrFail(session('siswa_id'));

        return view('wali.pelanggaran', compact('siswa'));
    }

    public function prestasi()
    {
        $siswa = Siswa::with([
            'prestasiSiswa.prestasi'
        ])->findOrFail(session('siswa_id'));

        return view('wali.prestasi', compact('siswa'));
    }

    public function raport()
    {
        $siswa = Siswa::with('kelas')->findOrFail(session('siswa_id'));

        /*
        |---------------------------------------------
        | RAPORT NON AKADEMIK
        |---------------------------------------------
        */
        $raport = RaportNonAkademik::with([
            'siswa',
            'kelas',
            'tahunAjaran',
            'kepribadians',
            'ekstrakurikulers'
        ])
        ->where('siswa_id', $siswa->id)
        ->latest()
        ->first();

        /*
        |---------------------------------------------
        | NILAI AKHIR (FIXED QUERY)
        |---------------------------------------------
        */
        $rekapNilai = RekapNilai::with(['mapel', 'guru'])
            ->where('siswa_id', $siswa->id)
            ->when($raport, function ($q) use ($raport) {
                $q->where('tahun_ajaran_id', $raport->tahun_ajaran_id);
            })
            ->get();

        /*
        |---------------------------------------------
        | NILAI AKADEMIK CLEAN MAPPING
        |---------------------------------------------
        */
        $nilaiAkademik = $rekapNilai->map(function ($nilai) {

            return (object) [
                'mapel' => $nilai->mapel,   // object asli
                'guru' => $nilai->guru,     // object asli
                'nilai_akhir' => (int) $nilai->nilai_akhir,
                'grade' => $nilai->grade ?? '-',
            ];
        });

        return view('wali.raport', compact(
            'siswa',
            'raport',
            'nilaiAkademik'
        ));
    }

    public function showPembayaran(Tagihan $tagihan)
    {
        $siswa = Siswa::with('wallet')
            ->findOrFail(session('siswa_id'));

        abort_if(
            $tagihan->siswa_id != $siswa->id,
            403
        );

        $sisaTagihan =
            $tagihan->nominal -
            $tagihan->nominal_terbayar;

        $isCicilan =
            $tagihan->jenisTagihan?->is_cicilan ?? false;

        return view(
            'wali.pembayaran',
            compact(
                'siswa',
                'tagihan',
                'sisaTagihan',
                'isCicilan'
            )
        );
    }

    public function topup()
    {
        return view('wali.topup');
    }

    public function profil()
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        return view('wali.profil', compact('siswa'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $siswa = Siswa::find(session('siswa_id'));

        if (!Hash::check($request->old_password, $siswa->password)) {
            return back()->with('error', 'Password lama tidak sesuai');
        }

        $siswa->password = Hash::make($request->password);
        $siswa->save();

        return back()->with('success', 'Password berhasil diupdate');
    }
    
    public function bayarSaldo(Tagihan $tagihan)
    {
        $siswa = Siswa::with('wallet')
            ->findOrFail(session('siswa_id'));

        abort_if(
            $tagihan->siswa_id !== $siswa->id,
            403
        );

        if (strtolower($tagihan->status) === 'lunas') {
            return back()->with(
                'error',
                'Tagihan sudah lunas.'
            );
        }

        $wallet = $siswa->wallet;

        if (!$wallet) {
            return back()->with(
                'error',
                'Wallet tidak ditemukan.'
            );
        }

        $sisaTagihan =
            $tagihan->nominal -
            $tagihan->nominal_terbayar;

        $isCicilan =
            $tagihan->jenisTagihan?->is_cicilan ?? false;

        $nominalBayar = $isCicilan
            ? (int) request('nominal')
            : $sisaTagihan;

        if ($nominalBayar <= 0) {
            return back()->with(
                'error',
                'Nominal pembayaran tidak valid.'
            );
        }

        if ($nominalBayar > $sisaTagihan) {
            return back()->with(
                'error',
                'Nominal melebihi sisa tagihan.'
            );
        }

        if ($wallet->saldo < $nominalBayar) {
            return back()->with(
                'error',
                'Saldo tidak mencukupi.'
            );
        }

        DB::transaction(function () use (
            $wallet,
            $siswa,
            $tagihan,
            $nominalBayar
        ) {

            $wallet->decrement(
                'saldo',
                $nominalBayar
            );

            WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'type'         => 'payment',
                'amount'       => $nominalBayar,
                'status'       => 'success',
                'reference_id' => $tagihan->id,
                'description'  => 'Payment '.$tagihan->judul,
            ]);

            $pembayaran = Pembayaran::create([
                'tagihan_id'    => $tagihan->id,
                'siswa_id'      => $siswa->id,
                'nominal'       => $nominalBayar,
                'metode'        => 'ewallet',
                'status'        => 'sukses',
                'tanggal_bayar' => now(),
                'keterangan'    => 'Pembayaran menggunakan saldo wallet',
            ]);
            
            NotificationService::sendPembayaran(
                $siswa,
                $pembayaran
            );
        });

        return redirect()
            ->route('wali.keuangan')
            ->with(
                'success',
                'Pembayaran berhasil menggunakan saldo.'
            );
            }

    public function doku(
    Request $request,
    Tagihan $tagihan,
    \App\Services\DokuService $doku
    )
    {
        $request->validate([
            'payment_method' => 'required|in:VA,QRIS,DANA,SHOPEEPAY,ALFAMART,INDOMARET,OVO',
            'ovo_phone' => 'required_if:payment_method,OVO|nullable|string|min:9|max:15',
        ]);

        $siswa = Siswa::findOrFail(session('siswa_id'));

        abort_if(
            $tagihan->siswa_id !== $siswa->id,
            403
        );

        $amount = (int) ($tagihan->nominal - $tagihan->nominal_terbayar);
        $referenceId = 'TAGIHAN-' . $tagihan->id . '-' . time();
        $channel = $request->payment_method;

        $lembaga = $tagihan->siswa?->lembaga;
        $customerName = $siswa->nama_lengkap;
        $customerEmail = \App\Services\DokuService::emailAman($siswa->email, $siswa->wa_wali ?? $siswa->id);

        $vaNumber = null;
        $qrString = null;
        $paymentCode = null;
        $redirectUrl = null;
        $howToPayPage = null;

        try {
            if ($channel === 'VA') {
                $result = $doku->buatVaLangsung(
                    referenceId: $referenceId,
                    amount: $amount,
                    judul: $tagihan->judul,
                    customerName: $customerName,
                    customerEmail: $customerEmail,
                    dokuSubAccountId: $lembaga?->doku_sub_account_id,
                );

                $vaNumber = $result['virtual_account_info']['virtual_account_number'] ?? null;
                $howToPayPage = $result['virtual_account_info']['how_to_pay_page'] ?? null;

                if (!$vaNumber) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['error']['message'] ?? null));
                }
            } elseif ($channel === 'QRIS') {
                $result = $doku->buatQris(
                    referenceId: $referenceId,
                    amount: $amount,
                );

                // TODO: field gambar/string QR PERLU dicocokkan dengan
                // respons asli sandbox (belum bisa dipastikan 100% dari
                // dokumentasi publik) -- lihat catatan lengkap di
                // DokuService::buatQris().
                $qrString = $result['qrContent'] ?? $result['qrUrl'] ?? null;

                if (!$qrString) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['message'] ?? $result['responseMessage'] ?? null));
                }
            } elseif (in_array($channel, ['DANA', 'SHOPEEPAY'], true)) {
                $result = $doku->buatEwalletSnap(
                    channel: $channel === 'DANA' ? 'EMONEY_DANA_SNAP' : 'EMONEY_SHOPEE_PAY_SNAP',
                    referenceId: $referenceId,
                    amount: $amount,
                    returnUrl: route('wali.keuangan'),
                );

                $redirectUrl = $result['webRedirectUrl'] ?? null;

                if (!$redirectUrl) {
                    return back()->with('error', \App\Services\DokuService::pesanAman($result['responseMessage'] ?? null));
                }
            } elseif (in_array($channel, ['ALFAMART', 'INDOMARET'], true)) {
                $result = $doku->buatOtc(
                    toko: $channel,
                    referenceId: $referenceId,
                    amount: $amount,
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
                    amount: $amount,
                    ovoId: $request->ovo_phone,
                );

                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'siswa_id'   => $siswa->id,
                    'nominal'    => $amount,
                    'metode'     => 'gateway',
                    'gateway'    => 'doku',
                    'status'     => 'pending',
                    'reference'  => $referenceId,
                ]);

                // OVO Push Payment BLOCKING -- respons langsung berisi
                // hasil approve/tolak/timeout dari HP customer (bukan
                // link/kode untuk ditunda). Webhook tetap dipasang
                // sebagai jaring pengaman, tapi untuk OVO biasanya
                // status sudah bisa langsung diketahui dari respons ini.
                $statusOvo = $result['transaction']['status'] ?? null;

                return redirect()->route('wali.pembayaran.show', $tagihan)
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
            'siswa_id'   => $siswa->id,
            'nominal'    => $amount,
            'metode'     => 'gateway',
            'gateway'    => 'doku',
            'status'     => 'pending',
            'reference'  => $referenceId,
        ]);

        // DANA/ShopeePay langsung redirect (app-to-app), tidak perlu
        // halaman checkout custom.
        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }

        return view('payment.checkout', [
            'layout' => 'wali.layout.wali',
            'namaLembaga' => $lembaga?->nama ?? $siswa->yayasan?->nama ?? 'Qinara',
            'logo' => $lembaga?->logo ? asset('storage/' . $lembaga->logo) : null,
            'referenceId' => $referenceId,
            'judul' => $tagihan->judul,
            'amount' => $amount,
            'channel' => $channel,
            'vaNumber' => $vaNumber,
            'qrString' => $qrString,
            'paymentCode' => $paymentCode,
            'howToPayPage' => $howToPayPage,
            'countdownTo' => now()->addMinutes(in_array($channel, ['ALFAMART', 'INDOMARET'], true) ? 1440 : 60)->toIso8601String(),
            'statusUrl' => route('wali.pembayaran.doku.status', $referenceId),
            'successUrl' => route('wali.keuangan'),
        ]);
    }

    /**
     * Endpoint JSON dipanggil tombol "Cek Status Pembayaran" di halaman
     * checkout custom -- polling manual (bukan realtime), cukup untuk
     * kebutuhan sekarang karena webhook (DokuWebhookController) tetap
     * jadi sumber kebenaran utama status pembayaran.
     */
    public function statusDoku(string $reference)
    {
        $pembayaran = Pembayaran::where('reference', $reference)->first();

        abort_if(!$pembayaran, 404);

        return response()->json([
            'status' => $pembayaran->status,
        ]);
    }

    public function showDokuForm(Tagihan $tagihan)
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        abort_if($tagihan->siswa_id !== $siswa->id, 403);

        return view('wali.doku', compact('tagihan'));
    }
}