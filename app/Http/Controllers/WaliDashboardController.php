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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\DuitkuPaymentMapper;
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

                public function bayarTransfer(Request $request, Tagihan $tagihan)
        {
            $siswa = Siswa::findOrFail(session('siswa_id'));

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

            $sisaTagihan =
                $tagihan->nominal -
                $tagihan->nominal_terbayar;

            $isCicilan =
                $tagihan->jenisTagihan?->is_cicilan ?? false;

            $request->validate([
                'nominal' => [
                    $isCicilan ? 'required' : 'nullable',
                    'numeric',
                    'min:1000'
                ],

                'bukti_transfer' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048'
                ],
            ]);

            $nominalBayar = $isCicilan
                ? (int) $request->nominal
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

            $pending = Pembayaran::where(
                'tagihan_id',
                $tagihan->id
            )
            ->where(
                'siswa_id',
                $siswa->id
            )
            ->where(
                'metode',
                'transfer'
            )
            ->where(
                'status',
                'pending'
            )
            ->first();

            if ($pending) {

                return redirect()
                ->route('wali.keuangan')
                ->with(
                    'success',
                    'Bukti transfer berhasil dikirim dan menunggu verifikasi admin.'
                );
            }

            $path = $request
            ->file('bukti_transfer')
            ->store(
                'bukti-transfer',
                'public'
            );

        Pembayaran::create([
            'tagihan_id'      => $tagihan->id,
            'siswa_id'        => $siswa->id,
            'nominal'         => $nominalBayar,
            'metode'          => 'transfer',
            'status'          => 'pending',
            'tanggal_bayar'   => now(),
            'bukti_transfer'  => $path,
            'keterangan'      => 'Menunggu verifikasi admin',
        ]);

        return redirect()
            ->route('wali.keuangan')
            ->with(
                'success',
                'Bukti transfer berhasil dikirim dan menunggu verifikasi admin.'
            );
        }

        public function showTransfer(Pembayaran $pembayaran)
        {
            $siswa = Siswa::findOrFail(session('siswa_id'));
            abort_if(
                $pembayaran->siswa_id !== $siswa->id,
                403
            );

            $rekenings = \App\Models\Rekening::where(
                'is_active',
                true
            )
            ->where(
                'tipe',
                'bank'
            )
            ->get();

            return view(
                'wali.transfer',
                compact(
                    'siswa',
                    'pembayaran',
                    'rekenings'
                )
            );
        }

        public function showTransferForm(Tagihan $tagihan)
        {
            $siswa = Siswa::findOrFail(session('siswa_id'));

            abort_if(
                $tagihan->siswa_id !== $siswa->id,
                403
            );

            $rekenings = \App\Models\Rekening::where(
                'is_active',
                true
            )
            ->where(
                'tipe',
                'bank'
            )
            ->get();

            $sisaTagihan =
                $tagihan->nominal -
                $tagihan->nominal_terbayar;

            return view(
                'wali.transfer',
                compact(
                    'siswa',
                    'tagihan',
                    'rekenings',
                    'sisaTagihan'
                )
            );
        }

        public function uploadBuktiTransfer(
            Request $request,
            Pembayaran $pembayaran
        )
        {
            $siswa = Siswa::findOrFail(
                session('siswa_id')
            );

            abort_if(
                $pembayaran->siswa_id !== $siswa->id,
                403
            );

            if ($pembayaran->metode !== 'transfer') {
                abort(403);
            }

            if ($pembayaran->bukti_transfer) {
                return back()->with(
                    'error',
                    'Bukti transfer sudah pernah dikirim.'
                );
            }

            $request->validate([
                'bukti_transfer' => [
                    'required',
                    'image',
                    'max:2048'
                ]
            ]);

            $path = $request
                ->file('bukti_transfer')
                ->store(
                    'bukti-transfer',
                    'public'
                );

            $pembayaran->update([
                'bukti_transfer' => $path,
                'status' => 'pending',
                'keterangan' => 'Menunggu verifikasi admin',
            ]);

            return redirect()
                ->route('wali.keuangan')
                ->with(
                    'success',
                    'Bukti transfer berhasil dikirim.'
                );
        }

    public function duitku(
    Request $request,
    Tagihan $tagihan
    )
    {
        $request->validate([
            'payment_method' => 'required|string'
        ]);

        $siswa = Siswa::findOrFail(session('siswa_id'));

        abort_if(
            $tagihan->siswa_id !== $siswa->id,
            403
        );

        $allowedMethods = [
            'BCA', 'BNI', 'BRI', 'MANDIRI', 'BSI',
            'OV', 'DA', 'SP',
            'QRIS',
            'ALFAMART', 'INDOMARET'
        ];

        if (!in_array($request->payment_method, $allowedMethods)) {
            return back()->with('error', 'Metode pembayaran tidak valid.');
        }

        $merchantCode = config('services.duitku.merchant_code');
        $apiKey       = config('services.duitku.api_key');

        $amount = $tagihan->nominal - $tagihan->nominal_terbayar;

        $merchantOrderId = 'TAGIHAN-' . $tagihan->id . '-' . time();

        $signature = md5(
            $merchantCode .
            $merchantOrderId .
            $amount .
            $apiKey
        );

        $endpoint = config('services.duitku.sandbox')
            ? 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';

        $response = Http::post($endpoint, [
            'merchantCode'    => $merchantCode,
            'paymentAmount'   => $amount,
            'paymentMethod'   => $request->payment_method,
            'merchantOrderId' => $merchantOrderId,
            'productDetails'  => $tagihan->judul,

            // ✅ FIX: email dari wali/ayah/ibu kalau ada
            'email' => $siswa->email
                ?? $siswa->wa_wali . '@dummy.id',

            'phoneNumber' =>
                $siswa->wa_wali
                ?? $siswa->wa_ayah
                ?? $siswa->wa_ibu
                ?? '08123456789',

            'customerVaName' => $siswa->nama_lengkap,
            'callbackUrl' => route('duitku.callback'),
            'returnUrl' => route('wali.keuangan'),
            'signature' => $signature,
        ]);

        $result = $response->json();

        if (!isset($result['paymentUrl'])) {
            return back()->with(
                'error',
                $result['Message'] ?? 'Gagal membuat pembayaran'
            );
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'siswa_id'   => $siswa->id,
            'nominal'    => $amount,
            'metode'     => 'duitku',
            'status'     => 'pending',
            'reference'  => $merchantOrderId,
        ]);

        return redirect($result['paymentUrl']);
    }

    public function showDuitkuForm(Tagihan $tagihan)
    {
        $siswa = Siswa::findOrFail(session('siswa_id'));

        abort_if($tagihan->siswa_id !== $siswa->id, 403);

        $paymentMethods = collect([
        'BCA' => [
            'code' => 'BCA',
            'name' => 'BCA Virtual Account',
            'category' => 'Virtual Account'
        ],

        'BNI' => [
            'code' => 'BNI',
            'name' => 'BNI Virtual Account',
            'category' => 'Virtual Account'
        ],

        'BRI' => [
            'code' => 'BRI',
            'name' => 'BRI Virtual Account',
            'category' => 'Virtual Account'
        ],

        'BSI' => [
        'code' => 'BSI',
        'name' => 'BSI Virtual Account',
        'category' => 'Virtual Account'
        ],

        'MANDIRI' => [
            'code' => 'MANDIRI',
            'name' => 'Mandiri Virtual Account',
            'category' => 'Virtual Account'
        ],

        'OV' => [
            'code' => 'OV',
            'name' => 'OVO',
            'category' => 'E-Wallet'
        ],

        'DA' => [
            'code' => 'DA',
            'name' => 'DANA',
            'category' => 'E-Wallet'
        ],

        'SP' => [
            'code' => 'SP',
            'name' => 'ShopeePay',
            'category' => 'E-Wallet'
        ],

        'QRIS' => [
            'code' => 'QRIS',
            'name' => 'QRIS',
            'category' => 'QRIS'
        ],

        'ALFAMART' => [
            'code' => 'ALFAMART',
            'name' => 'Alfamart',
            'category' => 'Retail'
        ],

        'INDOMARET' => [
            'code' => 'INDOMARET',
            'name' => 'Indomaret',
            'category' => 'Retail'
        ],
    ]);

        return view(
            'wali.duitku',
            compact(
                'tagihan',
                'paymentMethods'
            )
        );
    }
}