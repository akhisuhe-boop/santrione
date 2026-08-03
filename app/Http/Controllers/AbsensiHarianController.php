<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\AbsensiHarian;
use App\Services\NotificationService;
use App\Support\FileUrlResolver;

class AbsensiHarianController extends Controller
{
    public function index()
    {
        return view('absensi-harian.index');
    }

    // ===============================
    // PROSES SCAN (MASUK / PULANG)
    // ===============================

    public function scan(Request $request)
    {
        try {

            $code = trim($request->qr_code);
            $jenis = $request->jenis === 'pulang' ? 'pulang' : 'masuk';

            if (!$code) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode tidak boleh kosong'
                ]);
            }

            // ===============================
            // CARI SISWA / GURU
            // ===============================

            $siswa = Siswa::where('nis', $code)
                ->orWhere('nisn', $code)
                ->orWhere('rfid', $code)
                ->orWhere('rfid_code', $code)
                ->orWhere('qr_code', $code)
                ->first();

            $pegawai = null;

            if (!$siswa) {
                $pegawai = Pegawai::where('rfid', $code)
                    ->orWhere('qr_code', $code)
                    ->orWhere('niy', $code)
                    ->first();
            }

            if (!$siswa && !$pegawai) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $today = Carbon::today()->format('Y-m-d');

            // ===============================
            // AMBIL / BUAT RECORD HARI INI
            // ===============================

            $absen = AbsensiHarian::firstOrCreate(
                [
                    'tanggal' => $today,
                    'siswa_id' => $siswa?->id,
                    'pegawai_id' => $pegawai?->id,
                ],
                [
                    'tipe' => $siswa ? 'siswa' : 'guru',
                ]
            );

            $nama = $siswa ? $siswa->nama_lengkap : $pegawai->nama;
            $foto = $siswa
                ? FileUrlResolver::public($siswa->foto)
                : FileUrlResolver::public($pegawai->foto);

            $lembaga = $siswa
                ? $siswa->lembaga
                : $pegawai?->lembagas?->first();

            // ===============================
            // MODE MASUK
            // ===============================

            if ($jenis === 'masuk') {

                if ($absen->jam_masuk) {
                    return response()->json([
                        'status' => 'success',
                        'nama' => $nama,
                        'foto' => $foto,
                        'message' => 'Sudah absen masuk sebelumnya (' . Carbon::parse($absen->jam_masuk)->format('H:i') . ')',
                    ]);
                }

                $jamMasukSetting = $siswa
                    ? $lembaga?->jam_masuk_siswa
                    : $lembaga?->jam_masuk_guru;

                $toleransi = $lembaga?->toleransi_telat_menit ?? 15;

                $status = 'Hadir';

                if ($jamMasukSetting) {
                    $batas = Carbon::parse($jamMasukSetting)->addMinutes($toleransi);
                    if (Carbon::now()->gt($batas)) {
                        $status = 'Terlambat';
                    }
                }

                $absen->update([
                    'jam_masuk' => now(),
                    'status_masuk' => $status,
                    'metode_masuk' => 'Scan',
                    'diinput_oleh' => auth()->id(),
                ]);

                if ($siswa) {
                    NotificationService::sendAbsensiHarian(
                        $siswa,
                        'masuk',
                        $status,
                        $absen->jam_masuk,
                        $lembaga?->id
                    );
                } else {
                    $setting = \App\Models\WhatsappSetting::where('lembaga_id', $lembaga?->id)
                        ->where('is_active', 1)
                        ->first();

                    NotificationService::sendAbsensiHarianGuru(
                        $pegawai,
                        'masuk',
                        $status,
                        $absen->jam_masuk,
                        $setting?->no_admin_absensi,
                        $lembaga?->id
                    );
                }

                $message = $status;

            }

            // ===============================
            // MODE PULANG
            // ===============================

            else {

                if (!$absen->jam_masuk) {
                    return response()->json([
                        'status' => 'error',
                        'nama' => $nama,
                        'foto' => $foto,
                        'message' => 'Belum absen masuk hari ini',
                    ]);
                }

                if ($absen->jam_pulang) {
                    return response()->json([
                        'status' => 'success',
                        'nama' => $nama,
                        'foto' => $foto,
                        'message' => 'Sudah absen pulang sebelumnya (' . Carbon::parse($absen->jam_pulang)->format('H:i') . ')',
                    ]);
                }

                $jamPulangSetting = $siswa
                    ? $lembaga?->jam_pulang_siswa
                    : $lembaga?->jam_pulang_guru;

                $status = 'Pulang';

                if ($jamPulangSetting && Carbon::now()->lt(Carbon::parse($jamPulangSetting))) {
                    $status = 'Pulang Awal';
                }

                $absen->update([
                    'jam_pulang' => now(),
                    'status_pulang' => $status,
                    'metode_pulang' => 'Scan',
                    'diinput_oleh' => auth()->id(),
                ]);

                if ($siswa) {
                    NotificationService::sendAbsensiHarian(
                        $siswa,
                        'pulang',
                        $status,
                        $absen->jam_pulang,
                        $lembaga?->id
                    );
                } else {
                    $setting = \App\Models\WhatsappSetting::where('lembaga_id', $lembaga?->id)
                        ->where('is_active', 1)
                        ->first();

                    NotificationService::sendAbsensiHarianGuru(
                        $pegawai,
                        'pulang',
                        $status,
                        $absen->jam_pulang,
                        $setting?->no_admin_absensi,
                        $lembaga?->id
                    );
                }

                $message = $status;
            }

            // ===============================
            // RIWAYAT HARI INI
            // ===============================

            $riwayat = AbsensiHarian::where('tanggal', $today)
                ->where(function ($q) {
                    $q->whereNotNull('jam_masuk')->orWhereNotNull('jam_pulang');
                })
                ->with(['siswa', 'pegawai'])
                ->latest('updated_at')
                ->take(8)
                ->get()
                ->map(function ($r) {

                    $n = '-';
                    $f = '';

                    if ($r->tipe === 'siswa' && $r->siswa) {
                        $n = $r->siswa->nama_lengkap;
                        $f = FileUrlResolver::public($r->siswa->foto) ?? '';
                    } elseif ($r->tipe === 'guru' && $r->pegawai) {
                        $n = $r->pegawai->nama;
                        $f = FileUrlResolver::public($r->pegawai->foto) ?? '';
                    }

                    $jamTerakhir = $r->jam_pulang ?? $r->jam_masuk;
                    $statusTerakhir = $r->jam_pulang
                        ? ($r->status_pulang . ' (Pulang)')
                        : ($r->status_masuk . ' (Masuk)');

                    return [
                        'nama' => $n,
                        'tipe' => strtoupper($r->tipe),
                        'waktu' => $jamTerakhir ? Carbon::parse($jamTerakhir)->format('H:i:s') : '-',
                        'status' => $statusTerakhir,
                        'foto' => $f,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'nama' => $nama,
                'foto' => $foto,
                'message' => $message,
                'riwayat' => $riwayat,
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }
}
