<?php

namespace App\Services;

use App\Jobs\SendWhatsappJob;
use Carbon\Carbon;

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | CORE WA
    |--------------------------------------------------------------------------
    */
    public static function wa($phone, $message)
    {
        SendWhatsappJob::dispatch($phone, $message);
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT NOMOR
    |--------------------------------------------------------------------------
    */
    private static function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | ABSENSI SISWA
    |--------------------------------------------------------------------------
    */
    public static function sendAbsensiSiswa(
        $siswa,
        $status,
        $kegiatan
    ) {

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;
        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $jam = Carbon::now()->format('H:i');
        $narasi = $status == 'Hadir'
            ? 'Ananda hadir tepat waktu'
            : 'Ananda datang terlambat';

        $pesan =
            "*ABSENSI SEKOLAH*\n\n" .
            "Ananda telah melakukan absensi\n\n" .
            $narasi . "\n\n" .
            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Kegiatan : *{$kegiatan->templateKegiatan->nama_kegiatan}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | ABSENSI GURU
    |--------------------------------------------------------------------------
    */

    public static function sendAbsensiGuru(
        $pegawai,
        $status,
        $kegiatan,
        $nomorAdmin
    ) {

        if (!$nomorAdmin) {
            return;
        }

        $nomorAdmin = self::formatPhone($nomorAdmin);
        $jam = Carbon::now()->format('H:i');
        $pesan =
            "*ABSENSI GURU / PEGAWAI*\n\n" .
            "Guru / Pegawai telah melakukan absensi\n\n" .

            "Nama : *{$pegawai->nama}*\n" .
            "NIY : *{$pegawai->niy}*\n" .
            "Kegiatan : *{$kegiatan->templateKegiatan->nama_kegiatan}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        self::wa($nomorAdmin, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PELANGGARAN
    |--------------------------------------------------------------------------
    */

    public static function sendPelanggaran(
        $siswa,
        $pelanggaran
    ) {

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*PELANGGARAN SISWA*\n\n" .

            "Kami memberitahukan bahwa ananda:\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Pelanggaran : *{$pelanggaran->nama}*\n" .
            "Poin : *{$pelanggaran->point}*\n\n" .

            "Mohon perhatian dan pembinaannya.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PRESTASI
    |--------------------------------------------------------------------------
    */

    public static function sendPrestasi(
        $siswa,
        $prestasi
    ) {

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "🎉 *PRESTASI SISWA* 🎉\n\n" .

            "Selamat kepada ananda:\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Prestasi : *{$prestasi->nama}*\n\n" .

            "Semoga terus berprestasi.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | TAHFIDZ
    |--------------------------------------------------------------------------
    */

    public static function sendTahfidz(
        $siswa,
        $tahfidz
    ) {

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $jenis = match ($tahfidz->jenis) {
        'ziyadah' => 'Ziyadah',
        'murajaah' => 'Murajaah',
        default => ucfirst($tahfidz->jenis),
        };

        $pesan =
            "*LAPORAN TAHFIDZ*\n\n" .
            "Selamat kepada ananda, telah menyelesaikan tugas tahfidz:\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Jenis : *{$jenis}*\n" .
            "Surat : *{$tahfidz->surah->nama}*\n" .
            "Ayat : *{$tahfidz->ayat_dari} - {$tahfidz->ayat_sampai}*\n" .
            "Nilai : *{$tahfidz->nilai}*\n\n" .
            "Musyrif : *{$tahfidz->pegawai->nama}*\n\n" .

            "Semoga istiqomah dalam menghafal Al-Qur'an.\n\n" .
            "Barakallahu fiikum.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PERIZINAN DISETUJUI
    |--------------------------------------------------------------------------
    */

    public static function sendPerizinanApproved($perizinan)
    {
        $siswa = $perizinan->siswa;

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*PERIZINAN DISETUJUI*\n\n" .

            "Perizinan ananda telah disetujui.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Tipe izin: *" . ucfirst($perizinan->tipe) . "*\n" .
            "Keperluan : *{$perizinan->keperluan}*\n" .
            "Tanggal Izin : *" . Carbon::parse($perizinan->tanggal_mulai)->format('d M Y') . "*\n" .
            "Batas Kembali : *" . Carbon::parse($perizinan->tanggal_selesai)->format('d M Y H:i') . "*\n\n" .

            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PERIZINAN DIJEMPUT
    |--------------------------------------------------------------------------
    */

    public static function sendPerizinanDijemput($perizinan)
    {
        $siswa = $perizinan->siswa;
        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $pesan =
            "*SANTRI DIJEMPUT*\n\n" .

            "Ananda telah dijemput.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Penjemput : *{$perizinan->penjemput}*\n" .
            "Hubungan : *{$perizinan->hubungan}*\n" .
            "Jam Keluar : *" . now()->format('d M Y H:i') . "*\n\n" .

            "Semoga selamat dalam perjalanan.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PERIZINAN KEMBALI
    |--------------------------------------------------------------------------
    */

    public static function sendPerizinanKembali($perizinan)
    {
        $siswa = $perizinan->siswa;

        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $status = match ($perizinan->keterangan_waktu) {
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'sangat_terlambat' => 'Sangat Terlambat',
            default => '-',
        };

        $pesan =
            "*SANTRI TELAH KEMBALI*\n\n" .

            "Ananda telah kembali ke pondok/sekolah.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Jam Kembali : *" . now()->format('d M Y H:i') . "*\n" .
            "Status : *{$status}*\n\n" .

            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - PENDAFTARAN MASUK
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbBaru($ppdb)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*PENDAFTARAN PPDB BERHASIL*\n\n" .

            "Data pendaftaran telah diterima.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n" .
            "Status : *Draft*\n\n" .

            "Silakan menunggu informasi selanjutnya.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - MENUNGGU PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbPembayaran($ppdb, $tagihan)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*TAGIHAN PPDB*\n\n" .

            "Silakan melakukan pembayaran pendaftaran.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Tagihan : *{$tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n" .
            "Jatuh Tempo : *" . Carbon::parse($tagihan->jatuh_tempo)->format('d M Y') . "*\n\n" .

            "Mohon segera melakukan pembayaran.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - MASUK TES
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbTes($ppdb)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*INFORMASI TES PPDB*\n\n" .

            "Ananda masuk tahap seleksi tes.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Silakan menunggu jadwal tes dari panitia.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - LULUS
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbLulus($ppdb)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*SELAMAT! ANDA DINYATAKAN LULUS*\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Silakan melanjutkan proses daftar ulang.\n\n" .
            "Barakallahu fiikum.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - TIDAK LULUS
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbTidakLulus($ppdb)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*INFORMASI HASIL PPDB*\n\n" .

            "Mohon maaf, ananda belum dinyatakan lulus seleksi.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Tetap semangat dan sukses selalu.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - DAFTAR ULANG
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbDaftarUlang($ppdb, $tagihan)
    {
        $nomor = $ppdb->wa_ayah ?? $ppdb->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*TAGIHAN DAFTAR ULANG*\n\n" .

            "Silakan melakukan pembayaran daftar ulang.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Tagihan : *{$tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n\n" .

            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - SISWA AKTIF
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbAktif($siswa)
    {
        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;

        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $pesan =
            "*SISWA BERHASIL DIAKTIFKAN*\n\n" .

            "Selamat, ananda resmi menjadi siswa.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "NIS : *{$siswa->nis}*\n" .
            "Kelas : *{$siswa->kelas?->nama}*\n\n" .

            "Semoga menjadi siswa yang sholeh dan berprestasi.\n\n" .
            "Barakallahu fiikum.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | TAGIHAN
    |--------------------------------------------------------------------------
    */

    public static function sendTagihan(
        $user,
        $tagihan
    ) {

        $nomor = $user->wa_ayah ?? $user->wa_ibu;

        if (!$nomor) {
            return;
        }

        // ❌ Jangan kirim jika lunas
        if ($tagihan->status === 'lunas') {
            return;
        }

        $status = match ($tagihan->status) {
            'Belum' => 'Belum Lunas',
            'sebagian' => 'Sebagian',
            'lunas' => 'Lunas',
            default => $tagihan->status,
        };

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*TAGIHAN SEKOLAH*\n\n" .

            "Ananda Memiliki Tagihan Pembayaran.\n\n" .

            "Nama : *{$user->nama_lengkap}*\n" .

            "Jenis Tagihan : *{$tagihan->judul}*\n" .
            "Total Tagihan : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n" .
            "Terbayar : *Rp " . number_format($tagihan->nominal_terbayar, 0, ',', '.') . "*\n" .
            "Status : *{$status}*\n\n" .
            "Sisa : *Rp " . number_format(
                $tagihan->nominal - $tagihan->nominal_terbayar,
                0,
                ',',
                '.'
            ) . "*\n" .
            "Jatuh Tempo : *" . Carbon::parse($tagihan->jatuh_tempo)->format('d M Y') . "*\n\n" .

            "Mohon segera melakukan pembayaran ke rekening resmi yayasan.\n\n" .
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }

     /*
    |--------------------------------------------------------------------------
    | PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public static function sendPembayaran(
    $user,
    $pembayaran
    ) {

        if (!$user) {
            return;
        }
        $nomor = $user->wa_ayah ?? $user->wa_ibu;
        if (!$nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan =
            "*PEMBAYARAN BERHASIL*\n\n" .

            "Pembayaran telah diterima.\n\n" .

            "Nama : *{$user->nama_lengkap}*\n" .
            "Pembayaran : *{$pembayaran->tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($pembayaran->nominal, 0, ',', '.') . "*\n" .
            "Tanggal : *" . Carbon::now()->format('d M Y H:i') . "*\n\n" .
            
            "Terima kasih.";

        self::wa($nomor, $pesan);
    }
}