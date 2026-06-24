<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\SiswaTemplateExport;
use App\Exports\SiswaPdfExport;
use App\Exports\PegawaiTemplateExport;

use App\Http\Controllers\KartuController;
use App\Http\Controllers\JadwalKegiatanController;
use App\Http\Controllers\WhatsappSettingController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\WaliAuthController;
use App\Http\Controllers\PrintRaportController;
use App\Http\Controllers\WaliDashboardController;
use App\Http\Controllers\DuitkuController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\PerizinanController;
use App\Http\Controllers\RoleLoginController;
use App\Http\Controllers\AnnouncementController;

// ==========================
// ROLE LOGIN GATEWAY (BARU)
// ==========================

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.role-login');
})->name('login');


// ==========================
// ROUTE LAMA (TIDAK DIUBAH)
// ==========================

Route::post('/whatsapp/test', [WhatsappSettingController::class, 'test']);

Route::get('/absensi', [AbsensiController::class, 'index']);
Route::post('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');

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

    Route::get('/kartu-pegawai', [KartuController::class, 'cetakPegawai'])
        ->name('kartu.pegawai');

    Route::get('/raport/pdf/{siswa}', [PrintRaportController::class, 'generate'])
        ->name('raport.pdf');
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