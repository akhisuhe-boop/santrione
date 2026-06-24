<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\JadwalKegiatan;
use App\Models\Absensi;
use App\Services\NotificationService;
use App\Models\WhatsappSetting;

class AbsensiController extends Controller
{

    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        $kegiatan = JadwalKegiatan::where('tanggal', $today)
            ->where('jam_mulai', '<=', $now)
            ->where('jam_selesai', '>=', $now)
            ->with('templateKegiatan')
            ->first();

        $next = JadwalKegiatan::where('tanggal', $today)
            ->where('jam_mulai', '>', $now)
            ->with('templateKegiatan')
            ->orderBy('jam_mulai')
            ->first();

        return view('absensi.index', [
            'kegiatan' => $kegiatan,
            'next' => $next
        ]);
    }


    // ===============================
    // KEGIATAN AKTIF
    // ===============================

    private function kegiatanAktif()
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        return JadwalKegiatan::where('tanggal', $today)
            ->where('jam_mulai', '<=', $now)
            ->where('jam_selesai', '>=', $now)
            ->with('templateKegiatan')
            ->first();
    }


    // ===============================
    // GENERATE ABSENSI SISWA DEFAULT ALPA
    // ===============================

    private function generateAbsensi($kegiatan)
    {
        $siswas = Siswa::all();

        foreach ($siswas as $siswa) {

            $cek = Absensi::where('jadwal_kegiatan_id', $kegiatan->id)
                ->where('siswa_id', $siswa->id)
                ->exists();

            if (!$cek) {

                Absensi::create([
                    'jadwal_kegiatan_id' => $kegiatan->id,
                    'siswa_id' => $siswa->id,
                    'tipe' => 'siswa',
                    'status' => 'Alpa',
                    'jam_scan' => '00:00:00',
                    'waktu' => now(),
                    'metode' => '-'
                ]);
            }
        }
    }


    // ===============================
    // PROSES SCAN
    // ===============================

    public function scan(Request $request)
    {

        try {

            $code = trim($request->qr_code);

            if (!$code) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode tidak boleh kosong'
                ]);
            }


            // ===============================
            // KEGIATAN AKTIF
            // ===============================

            $kegiatan = $this->kegiatanAktif();

            if (!$kegiatan) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada kegiatan aktif saat ini'
                ]);
            }


            // ===============================
            // GENERATE ABSENSI SISWA DEFAULT ALPA
            // ===============================

            $this->generateAbsensi($kegiatan);


            // ===============================
            // CARI SISWA
            // ===============================

            $siswa = Siswa::where('nis', $code)
                ->orWhere('nisn', $code)
                ->orWhere('rfid', $code)
                ->orWhere('rfid_code', $code)
                ->orWhere('qr_code', $code)
                ->first();


            // ===============================
            // CARI GURU
            // ===============================

            $pegawai = null;

            if (!$siswa) {

                $pegawai = Pegawai::where('rfid', $code)
                    ->orWhere('qr_code', $code)
                    ->orWhere('niy', $code)
                    ->first();
            }


            // ===============================
            // DATA TIDAK DITEMUKAN
            // ===============================

            if (!$siswa && !$pegawai) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }


            // ===============================
            // CEK ABSENSI
            // ===============================

            if ($siswa) {

                $absensi = Absensi::where('jadwal_kegiatan_id', $kegiatan->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

            } else {

                $absensi = Absensi::firstOrCreate(
                    [
                        'jadwal_kegiatan_id' => $kegiatan->id,
                        'pegawai_id' => $pegawai->id,
                    ],
                    [
                        'tipe' => 'guru',
                        'status' => 'Alpa',
                        'jam_scan' => '00:00:00',
                        'waktu' => now(),
                        'metode' => '-'
                    ]
                );
            }


            // ===============================
            // SUDAH ABSEN
            // ===============================

            if ($absensi && $absensi->status != 'Alpa') {

                return response()->json([

                    'status' => 'success',

                    'nama' => $siswa
                        ? $siswa->nama_lengkap
                        : $pegawai->nama,

                    'foto' => $siswa
                        ? ($siswa->foto
                            ? asset('storage/' . $siswa->foto)
                            : null)
                        : ($pegawai->foto
                            ? asset('storage/' . $pegawai->foto)
                            : null),

                    'message' => 'Sudah absen sebelumnya'
                ]);
            }


            // ===============================
            // STATUS HADIR / TERLAMBAT
            // ===============================

            $status = 'Hadir';

            $jamMulai = Carbon::parse($kegiatan->jam_mulai);

            $toleransi = $kegiatan
                ->templateKegiatan
                ->toleransi_telat ?? 0;

            if (
                Carbon::now()->gt(
                    $jamMulai->copy()->addMinutes($toleransi)
                )
            ) {
                $status = 'Terlambat';
            }


            // ===============================
            // UPDATE ABSENSI
            // ===============================

            $absensi->update([
                'pegawai_id' => $pegawai->id ?? null,
                'tipe' => $siswa
                    ? 'siswa'
                    : 'guru',
                'status' => $status,
                'jam_scan' => Carbon::now(),
                'waktu' => Carbon::now(),
                'metode' => 'QR'
            ]);


            // ===============================
            // KIRIM WA SISWA
            // ===============================

            if ($siswa) {
                NotificationService::sendAbsensiSiswa(
                    $siswa,
                    $status,
                    $kegiatan
                );

            }

            // ===============================
            // KIRIM WA GURU KE ADMIN
            // ===============================

            if ($pegawai) {
            $setting = WhatsappSetting::where('is_active', 1)->first();
            NotificationService::sendAbsensiGuru(
                $pegawai,
                $status,
                $kegiatan,
                $setting?->no_admin_absensi
                );
            }

            // ===============================
            // RIWAYAT
            // ===============================

            $riwayat = Absensi::where('jadwal_kegiatan_id', $kegiatan->id)
                ->where('jam_scan', '!=', '00:00:00')
                ->with(['siswa', 'pegawai'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($r) {

                    $nama = '-';
                    $foto = '';
                    $tipe = $r->tipe;

                    if ($r->tipe == 'siswa' && $r->siswa) {

                        $nama = $r->siswa->nama_lengkap;

                        $foto = $r->siswa->foto
                            ? asset('storage/' . $r->siswa->foto)
                            : '';

                    } elseif ($r->tipe == 'guru' && $r->pegawai) {

                        $nama = $r->pegawai->nama;

                        $foto = $r->pegawai->foto
                            ? asset('storage/' . $r->pegawai->foto)
                            : '';
                    }

                    return [
                        'nama' => $nama,
                        'tipe' => strtoupper($tipe),
                        'waktu' => Carbon::parse($r->waktu)->format('H:i:s'),
                        'status' => $r->status,
                        'foto' => $foto
                    ];
                });


            // ===============================
            // RESPONSE SUCCESS
            // ===============================

            return response()->json([

                'status' => 'success',

                'nama' => $siswa
                    ? $siswa->nama_lengkap
                    : $pegawai->nama,

                'foto' => $siswa
                    ? ($siswa->foto
                        ? asset('storage/' . $siswa->foto)
                        : null)
                    : ($pegawai->foto
                        ? asset('storage/' . $pegawai->foto)
                        : null),

                'message' => $status,

                'riwayat' => $riwayat
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