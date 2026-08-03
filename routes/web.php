<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\SiswaTemplateExport;
use App\Exports\SiswaPdfExport;
use App\Exports\PegawaiTemplateExport;

use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\SlipGajiController;

use App\Http\Controllers\KartuController;
use App\Http\Controllers\JadwalKegiatanController;
use App\Http\Controllers\WhatsappSettingController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\WaliAuthController;
use App\Http\Controllers\PrintRaportController;
use App\Http\Controllers\WaliDashboardController;
use App\Http\Controllers\DuitkuController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\PerizinanController;
use App\Http\Controllers\RoleLoginController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\AnnouncementController;

use App\Http\Controllers\Guru\GuruAuthController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\Guru\GuruAbsensiController;
use App\Http\Controllers\Guru\GuruJurnalController;
use App\Http\Controllers\Guru\GuruGajiController;
use App\Http\Controllers\Guru\GuruProfileController;
use App\Http\Controllers\Guru\GuruJadwalController;
use App\Http\Controllers\Guru\GuruNilaiController;
use App\Http\Controllers\Guru\GuruPengumumanController;

use App\Http\Controllers\Ppdb\PpdbDashboardController;
use App\Http\Controllers\Ppdb\PpdbProfileController;
use App\Http\Controllers\Ppdb\PpdbPembayaranController;
use App\Http\Controllers\Ppdb\PpdbPengumumanController;
use App\Http\Controllers\Ppdb\PpdbFormulirController;
use App\Http\Controllers\Ppdb\PpdbAuthController;

// ==========================
// ROLE LOGIN GATEWAY (BARU)
// ==========================

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/manifest.json', [ManifestController::class, 'show'])->name('manifest');
Route::get('/login', function () {
    return view('auth.role-login');
})->name('login');

// ==========================
// PENDAFTARAN YAYASAN BARU (SaaS self-service signup)
// ==========================
Route::get('/daftar', [PublicRegistrationController::class, 'create'])->name('public.daftar');
Route::post('/daftar', [PublicRegistrationController::class, 'store'])->name('public.daftar.store');

// Webhook Midtrans (dipanggil server Midtrans, bukan browser user —
// tidak pakai auth/CSRF, verifikasi lewat signature key di dalam
// controller-nya sendiri).
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhooks.midtrans');

// Halaman langganan tenant (lihat status trial/aktif, upload bukti
// transfer manual, atau bayar via Midtrans kalau sudah dikonfigurasi).
Route::middleware(['auth'])->group(function () {
    Route::get('/langganan', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/langganan/{plan}/manual', [SubscriptionController::class, 'payManual'])->name('subscription.pay-manual');
    Route::post('/langganan/{plan}/duitku', [SubscriptionController::class, 'payDuitku'])->name('subscription.pay-duitku');
    Route::post('/langganan/{plan}/midtrans', [SubscriptionController::class, 'payMidtrans'])->name('subscription.pay-midtrans');
});

Route::get('/y/{slug}', function (string $slug) {
    $yayasan = \App\Models\Yayasan::withoutGlobalScopes()
        ->where('slug', $slug)
        ->first();

    if (! $yayasan) {
        abort(404);
    }

    session(['active_public_yayasan_id' => $yayasan->id]);

    return redirect()->route('login');
})->name('tenant.entry');

// ==========================
// ROUTE LAMA (TIDAK DIUBAH)
// ==========================

Route::post('/whatsapp/test', [WhatsappSettingController::class, 'test']);

Route::get('/absensi', [AbsensiController::class, 'index']);
Route::post('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');

Route::get('/absensi-harian', [\App\Http\Controllers\AbsensiHarianController::class, 'index'])->name('absensi-harian.index');
Route::post('/absensi-harian/scan', [\App\Http\Controllers\AbsensiHarianController::class, 'scan'])->name('absensi-harian.scan');

Route::get('/kartu/siswa-massal', function () {

    $ids = request('ids');
    $ids = explode(',', $ids);

    $siswas = \App\Models\Siswa::whereIn('id', $ids)->get();

    return view('kartu.massal', compact('siswas'));
});

Route::get('/generate-jadwal/{bulan}/{tahun}', [JadwalKegiatanController::class, 'generate']);

Route::get('/export-siswa-pdf', function () {

    $lembagaId = request('lembaga_id');
    $kelasId   = request('kelas_id');

    return (new SiswaPdfExport($lembagaId, $kelasId))->download();

})->name('export.siswa.pdf');


// ==========================
// AUTH ROUTE UMUM
// ==========================

Route::middleware(['auth'])->group(function () {

    Route::get('/kartu/siswa/{id}', [KartuController::class,'cetakSatu']);
    Route::get('/kartu/siswa-massal', [KartuController::class,'cetakMassal']);

    Route::get('/siswa/template', function () {
        return Excel::download(new SiswaTemplateExport, 'template-siswa.xlsx');
    })->name('siswa.template');

    Route::post('/absensi/update-status', function (\Illuminate\Http\Request $request) {

        $absen = \App\Models\Absensi::find($request->id);

        if ($absen) {
            $absen->update([
                'status' => $request->status
            ]);
        }

        return back();

    });

    Route::get('/pegawai-template', function () {
        return Excel::download(new PegawaiTemplateExport, 'template-pegawai.xlsx');
    })->name('pegawai.template');

    Route::get('/kantin-produk-template', function () {
        return Excel::download(new \App\Exports\KantinProdukTemplateExport, 'template-kantin-produk.xlsx');
    })->name('kantin-produk.template');

    Route::get('/kartu-pegawai', [KartuController::class, 'cetakPegawai'])
        ->name('kartu.pegawai');

    Route::get('/raport/pdf/{siswa}', [PrintRaportController::class, 'generate'])
        ->name('raport.pdf');
});

// ==========================
// KWITANSI
// ==========================
Route::middleware('akses.kwitansi')->group(function () {

    Route::get('/kwitansi/{pembayaran}', [KwitansiController::class, 'show'])
        ->name('kwitansi.show');

    Route::get('/kwitansi/{pembayaran}/thermal58', [KwitansiController::class, 'thermal58'])
        ->name('kwitansi.thermal58');

    Route::get('/kwitansi/{pembayaran}/thermal80', [KwitansiController::class, 'thermal80'])
        ->name('kwitansi.thermal80');

    Route::get('/kwitansi/{pembayaran}/dotmatrix', [KwitansiController::class, 'dotmatrix'])
        ->name('kwitansi.dotmatrix');
        
    Route::get('/kwitansi/{pembayaran}/cetak', [KwitansiController::class, 'cetak'])
    ->name('kwitansi.cetak');
    
    Route::get('/kwitansi/{pembayaran}/pdf', [KwitansiController::class, 'pdf'])
    ->name('kwitansi.pdf');

});

// ==========================
// GAJI
// ==========================
Route::middleware('akses.slip.gaji')
    ->prefix('slip-gaji')
    ->name('slip-gaji.')
    ->group(function () {

        Route::get('/{payroll}', [SlipGajiController::class, 'show'])->name('show');

        Route::get('/{payroll}/pdf', [SlipGajiController::class, 'pdf'])->name('pdf');

        Route::get('/{payroll}/thermal80', [SlipGajiController::class, 'thermal80'])->name('thermal80');

        Route::get('/{payroll}/thermal58', [SlipGajiController::class, 'thermal58'])->name('thermal58');

        Route::get('/{payroll}/dotmatrix', [SlipGajiController::class, 'dotmatrix'])->name('dotmatrix');

        Route::get('/{payroll}/cetak', [SlipGajiController::class, 'cetak'])->name('cetak');

    });

// ==========================
// PORTAL WALI SANTRI
// ==========================

Route::prefix('wali')->group(function () {

    // LOGIN
    Route::get('/login', [WaliAuthController::class, 'login'])->name('wali.login');
    Route::get('/role-login', function () {return view('auth.role-login');})->name('role.login');
    Route::post('/login', [WaliAuthController::class, 'authenticate'])->name('wali.authenticate');
    Route::post('/logout', [WaliAuthController::class, 'logout'])->name('wali.logout');

    // DASHBOARD (PROTECTED)
    Route::middleware('wali')->group(function () {

        Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');
        Route::get('/pengumuman', [WaliDashboardController::class, 'pengumuman'])->name('wali.pengumuman');
        Route::get('/keuangan', [WaliDashboardController::class, 'keuangan'])->name('wali.keuangan');
        Route::get('/kantin', [WaliDashboardController::class, 'kantin'])->name('wali.kantin');
        Route::post('/kantin/limit', [WaliDashboardController::class, 'updateLimitKantin'])->name('wali.kantin.limit');
        Route::get('/akademik', [WaliDashboardController::class, 'akademik'])->name('wali.akademik');
        Route::get('/tahfidz', [WaliDashboardController::class, 'tahfidz'])->name('wali.tahfidz');
        Route::get('/absensi', [WaliDashboardController::class, 'absensi'])->name('wali.absensi');

        Route::get('/perizinan', [WaliDashboardController::class, 'perizinan'])->name('wali.perizinan');
        Route::post('/perizinan', [WaliDashboardController::class, 'storePerizinan'])->name('wali.perizinan.store');

        Route::get('/topup', [TopupController::class, 'index'])->name('wali.topup');
        Route::post('/topup', [TopupController::class, 'store'])->name('wali.topup.store');

        Route::get('/pelanggaran', [WaliDashboardController::class, 'pelanggaran'])->name('wali.pelanggaran');
        Route::get('/prestasi', [WaliDashboardController::class, 'prestasi'])->name('wali.prestasi');
        Route::get('/raport', [WaliDashboardController::class, 'raport'])->name('wali.raport');

        Route::get('/profil', [WaliDashboardController::class, 'profil'])->name('wali.profil');
        Route::post('/profil/password', [WaliDashboardController::class, 'updatePassword'])->name('wali.profil.updatePassword');

        // ==========================
        // PEMBAYARAN
        // ==========================

        Route::get('/pembayaran/{tagihan}', [WaliDashboardController::class, 'showPembayaran'])
            ->name('wali.pembayaran.show');

        Route::post('/pembayaran/{tagihan}/saldo', [WaliDashboardController::class, 'bayarSaldo'])
            ->name('wali.pembayaran.saldo');

        Route::get('/pembayaran/{tagihan}/transfer', [WaliDashboardController::class, 'showTransferForm'])
            ->name('wali.pembayaran.transfer');

        Route::post('/pembayaran/{tagihan}/transfer', [WaliDashboardController::class, 'bayarTransfer'])
            ->name('wali.pembayaran.transfer.store');

        Route::get('/pembayaran/{tagihan}/duitku', [WaliDashboardController::class, 'showDuitkuForm'])
            ->name('wali.pembayaran.duitku.form');

        Route::post('/pembayaran/{tagihan}/duitku', [WaliDashboardController::class, 'duitku'])
            ->name('wali.pembayaran.duitku');

        Route::get('/duitku/demo/topup', function (\Illuminate\Http\Request $request) {
            $trx = \App\Models\WalletTransaction::where('reference_id', $request->reference)->first();
            if ($trx) {
                $trx->update([
                    'status' => 'success'
                ]);
                $wallet = $trx->wallet;
                if ($wallet) {
                    $wallet->increment('saldo', $trx->amount);
                }
            }
            return redirect()
                ->route('wali.topup')
                ->with('success', 'Demo payment berhasil (Simulasi Duitku)');
        })->name('duitku.demo.topup');
        
    });

    // CALLBACK PAYMENT GATEWAY
    Route::post('/duitku/callback', [DuitkuController::class,'callback'])
        ->name('duitku.callback');
});


    //ROUTE GURU

    Route::prefix('guru')->group(function () {

    // ======================
    // LOGIN
    // ======================

    Route::get('/login', [GuruAuthController::class, 'login'])
        ->name('guru.login');

    Route::post('/authenticate', [GuruAuthController::class, 'authenticate'])
        ->name('guru.authenticate');

    Route::post('/logout', [GuruAuthController::class, 'logout'])
        ->name('guru.logout');

    // ======================
    // SETELAH LOGIN
    // ======================
    
    Route::middleware('guru')->group(function () {

    // Dashboard
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])
        ->name('guru.dashboard');

    // Jadwal Mengajar
    Route::get('/jadwal', [GuruJadwalController::class, 'index'])
        ->name('guru.jadwal');

    // Jurnal Mengajar
    Route::get('/jurnal', [GuruJurnalController::class, 'index'])
        ->name('guru.jurnal');
        
    Route::post('/jurnal', [GuruJurnalController::class, 'store'])
    ->name('guru.jurnal.store');

    Route::get('/jurnal-pengganti', [GuruJurnalController::class, 'pengganti'])
        ->name('guru.jurnal.pengganti');

    Route::post('/jurnal-pengganti', [GuruJurnalController::class, 'isiPengganti'])
        ->name('guru.jurnal.pengganti.isi');

    // Absensi
    Route::get('/absensi', [GuruAbsensiController::class, 'index'])
        ->name('guru.absensi');

    Route::get('/nilai', [GuruNilaiController::class,'index'])
    ->name('guru.nilai');

    Route::post('/nilai', [GuruNilaiController::class,'store'])
    ->name('guru.nilai.store');

    // Gaji
    Route::get('/gaji', [GuruGajiController::class, 'index'])
    ->name('guru.gaji');

    // Profil
    Route::get('/profil', [GuruProfileController::class, 'index'])
    ->name('guru.profile');

    Route::post('/profil/password', [GuruProfileController::class, 'updatePassword'])
    ->name('guru.profil.updatePassword');
    
    // Pengumuman
    Route::get('/pengumuman', [GuruPengumumanController::class, 'index'])
        ->name('guru.pengumuman');
    });
});

    /*
    |--------------------------------------------------------------------------
    | ROUTER PPDB
    |--------------------------------------------------------------------------
    */
    Route::prefix('ppdb')->group(function () {

    Route::get('/register', [PpdbAuthController::class, 'register'])
        ->name('ppdb.register');

    Route::post('/register', [PpdbAuthController::class, 'store'])
        ->name('ppdb.store');

    /*
    |--------------------------------------------------------------------------
    | LOGIN PPDB
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [PpdbAuthController::class, 'login'])
        ->name('ppdb.login');

    Route::post('/authenticate', [PpdbAuthController::class, 'authenticate'])
        ->name('ppdb.authenticate');

    Route::post('/logout', [PpdbAuthController::class, 'logout'])
        ->name('ppdb.logout');

    /*
    |--------------------------------------------------------------------------
    | AFTER LOGIN PPDB
    |--------------------------------------------------------------------------
    */

    Route::middleware('ppdb')->group(function () {

        // Dashboard
        Route::get('/dashboard', [PpdbDashboardController::class, 'index'])
            ->name('ppdb.dashboard');
        
        // Pembayaran
        Route::get('/pembayaran', [PpdbPembayaranController::class, 'index'])
            ->name('ppdb.pembayaran');
        
        Route::post('/pembayaran/saldo/{tagihan}', [PpdbPembayaranController::class, 'bayarSaldo'])
            ->name('ppdb.pembayaran.saldo');
        
        Route::get('/pembayaran/transfer/{tagihan}', [PpdbPembayaranController::class, 'showTransferForm'])
            ->name('ppdb.pembayaran.transfer');
        
        Route::post('/pembayaran/transfer/{tagihan}', [PpdbPembayaranController::class, 'bayarTransfer'])
            ->name('ppdb.pembayaran.transfer.store');
        
        Route::get('/pembayaran/duitku/{tagihan}', [PpdbPembayaranController::class, 'showDuitkuForm'])
            ->name('ppdb.pembayaran.duitku.form');
        
        Route::post('/pembayaran/duitku/{tagihan}', [PpdbPembayaranController::class, 'duitku'])
            ->name('ppdb.pembayaran.duitku');
        
        // Formulir
        Route::get('/formulir', [PpdbFormulirController::class, 'index'])
            ->name('ppdb.formulir');
        
        Route::post('/formulir', [PpdbFormulirController::class, 'store'])
            ->name('ppdb.formulir.store');
        
        // Upload Berkas
        Route::get('/upload-berkas', [PpdbFormulirController::class, 'uploadBerkas'])
            ->name('ppdb.upload-berkas');
        
        Route::post('/upload-berkas', [PpdbFormulirController::class, 'storeBerkas'])
            ->name('ppdb.upload-berkas.store');

        // Pengumuman
        Route::get('/pengumuman', [PpdbPengumumanController::class, 'index'])
            ->name('ppdb.pengumuman');
        
        // Profil
        Route::get('/profil', [PpdbProfileController::class, 'index'])
            ->name('ppdb.profil');

        Route::post('/profil/password', [PpdbProfileController::class, 'updatePassword'])
            ->name('ppdb.profil.updatePassword');
    });

});
