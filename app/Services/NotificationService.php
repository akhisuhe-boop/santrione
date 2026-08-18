<?php

namespace App\Services;

use App\Jobs\SendWhatsappJob;
use Carbon\Carbon;
use App\Models\Ppdb;

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | CORE WA
    |--------------------------------------------------------------------------
    */
    public static function wa($phone, $message, $lembagaId = null)
    {
        SendWhatsappJob::dispatch($phone, $message, $lembagaId);
    }

    /**
     * KHUSUS notifikasi level platform (tagihan langganan, broadcast,
     * reminder trial) -- SELALU pakai kredensial WA milik Qinara
     * sendiri, TIDAK PERNAH meminjam WhatsappSetting Lembaga manapun.
     */
    public static function waPlatform($phone, $message)
    {
        \App\Jobs\SendPlatformWhatsappJob::dispatch($phone, $message);
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

        $lembagaId = $kegiatan->templateKegiatan->lembaga_id ?? $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::ABSENSI_SISWA)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $jam = Carbon::now()->format('H:i');
        $narasi = $status == 'Hadir'
            ? 'Ananda hadir tepat waktu'
            : 'Ananda datang terlambat';

        $default =
            "*ABSENSI SEKOLAH*\n\n" .
            "Ananda telah melakukan absensi\n\n" .
            $narasi . "\n\n" .
            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Kegiatan : *{$kegiatan->templateKegiatan->nama_kegiatan}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::ABSENSI_SISWA, [
                'nama_siswa' => $siswa->nama_lengkap,
                'kegiatan' => $kegiatan->templateKegiatan->nama_kegiatan,
                'status' => $status,
                'jam' => $jam,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $kegiatan->templateKegiatan->lembaga_id ?? null;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::ABSENSI_GURU)) {
            return;
        }

        $nomorAdmin = self::formatPhone($nomorAdmin);
        $jam = Carbon::now()->format('H:i');
        $default =
            "*ABSENSI GURU / PEGAWAI*\n\n" .
            "Guru / Pegawai telah melakukan absensi\n\n" .

            "Nama : *{$pegawai->nama}*\n" .
            "NIY : *{$pegawai->niy}*\n" .
            "Kegiatan : *{$kegiatan->templateKegiatan->nama_kegiatan}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::ABSENSI_GURU, [
                'nama_guru' => $pegawai->nama,
                'niy' => $pegawai->niy,
                'kegiatan' => $kegiatan->templateKegiatan->nama_kegiatan,
                'status' => $status,
                'jam' => $jam,
            ], $default)
            : $default;

        self::wa($nomorAdmin, $pesan, $lembagaId);
    }

    /*
    |--------------------------------------------------------------------------
    | ABSENSI HARIAN (MASUK / PULANG)
    |--------------------------------------------------------------------------
    */

    public static function sendAbsensiHarian(
        $siswa,
        string $jenis,   // 'masuk' | 'pulang'
        string $status,  // Hadir/Terlambat/Pulang/Pulang Awal
        $waktu,
        ?int $lembagaId = null
    ) {
        $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;
        if (!$nomor) {
            return;
        }

        $lembagaId = $lembagaId ?? $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::ABSENSI_HARIAN)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $jam = Carbon::parse($waktu)->format('H:i');
        $label = $jenis === 'masuk' ? 'MASUK SEKOLAH' : 'PULANG SEKOLAH';

        $default =
            "*ABSENSI {$label}*\n\n" .
            "Ananda telah melakukan absensi {$jenis}\n\n" .
            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::ABSENSI_HARIAN, [
                'nama_siswa' => $siswa->nama_lengkap,
                'jenis' => $jenis,
                'status' => $status,
                'jam' => $jam,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
    }

    public static function sendAbsensiHarianGuru(
        $pegawai,
        string $jenis,   // 'masuk' | 'pulang'
        string $status,
        $waktu,
        ?string $nomorAdmin,
        ?int $lembagaId = null
    ) {
        if (!$nomorAdmin) {
            return;
        }

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::ABSENSI_HARIAN_GURU)) {
            return;
        }

        $nomorAdmin = self::formatPhone($nomorAdmin);
        $jam = Carbon::parse($waktu)->format('H:i');
        $label = $jenis === 'masuk' ? 'MASUK' : 'PULANG';

        $default =
            "*ABSENSI GURU/PEGAWAI - {$label}*\n\n" .
            "Guru/Pegawai telah melakukan absensi {$jenis}\n\n" .
            "Nama : *{$pegawai->nama}*\n" .
            "NIY : *{$pegawai->niy}*\n" .
            "Status : *{$status}*\n" .
            "Jam : *{$jam}*\n\n" .
            "Terima kasih";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::ABSENSI_HARIAN_GURU, [
                'nama_guru' => $pegawai->nama,
                'niy' => $pegawai->niy,
                'jenis' => $jenis,
                'status' => $status,
                'jam' => $jam,
            ], $default)
            : $default;

        self::wa($nomorAdmin, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PELANGGARAN)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "*PELANGGARAN SISWA*\n\n" .

            "Kami memberitahukan bahwa ananda:\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Pelanggaran : *{$pelanggaran->nama}*\n" .
            "Poin : *{$pelanggaran->point}*\n\n" .

            "Mohon perhatian dan pembinaannya.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PELANGGARAN, [
                'nama_siswa' => $siswa->nama_lengkap,
                'nama_pelanggaran' => $pelanggaran->nama,
                'poin' => $pelanggaran->point,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PRESTASI)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "🎉 *PRESTASI SISWA* 🎉\n\n" .

            "Selamat kepada ananda:\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Prestasi : *{$prestasi->nama}*\n\n" .

            "Semoga terus berprestasi.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PRESTASI, [
                'nama_siswa' => $siswa->nama_lengkap,
                'nama_prestasi' => $prestasi->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::TAHFIDZ)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $jenis = match ($tahfidz->jenis) {
        'ziyadah' => 'Ziyadah',
        'murajaah' => 'Murajaah',
        default => ucfirst($tahfidz->jenis),
        };

        $default =
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

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::TAHFIDZ, [
                'nama_siswa' => $siswa->nama_lengkap,
                'jenis' => $jenis,
                'surah' => $tahfidz->surah->nama,
                'ayat_dari' => $tahfidz->ayat_dari,
                'ayat_sampai' => $tahfidz->ayat_sampai,
                'nilai' => $tahfidz->nilai,
                'musyrif' => $tahfidz->pegawai->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PERIZINAN_APPROVED)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $tipeIzin = ucfirst($perizinan->tipe);
        $tanggalIzin = Carbon::parse($perizinan->tanggal_mulai)->format('d M Y');
        $batasKembali = Carbon::parse($perizinan->tanggal_selesai)->format('d M Y H:i');

        $default =
            "*PERIZINAN DISETUJUI*\n\n" .

            "Perizinan ananda telah disetujui.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Tipe izin: *{$tipeIzin}*\n" .
            "Keperluan : *{$perizinan->keperluan}*\n" .
            "Tanggal Izin : *{$tanggalIzin}*\n" .
            "Batas Kembali : *{$batasKembali}*\n\n" .

            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PERIZINAN_APPROVED, [
                'nama_siswa' => $siswa->nama_lengkap,
                'tipe_izin' => $tipeIzin,
                'keperluan' => $perizinan->keperluan,
                'tanggal_izin' => $tanggalIzin,
                'batas_kembali' => $batasKembali,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PERIZINAN_DIJEMPUT)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $jamKeluar = now()->format('d M Y H:i');

        $default =
            "*SANTRI DIJEMPUT*\n\n" .

            "Ananda telah dijemput.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Penjemput : *{$perizinan->penjemput}*\n" .
            "Hubungan : *{$perizinan->hubungan}*\n" .
            "Jam Keluar : *{$jamKeluar}*\n\n" .

            "Semoga selamat dalam perjalanan.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PERIZINAN_DIJEMPUT, [
                'nama_siswa' => $siswa->nama_lengkap,
                'penjemput' => $perizinan->penjemput,
                'hubungan' => $perizinan->hubungan,
                'jam_keluar' => $jamKeluar,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PERIZINAN_KEMBALI)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $status = match ($perizinan->keterangan_waktu) {
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'sangat_terlambat' => 'Sangat Terlambat',
            default => '-',
        };

        $jamKembali = now()->format('d M Y H:i');

        $default =
            "*SANTRI TELAH KEMBALI*\n\n" .

            "Ananda telah kembali ke pondok/sekolah.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "Jam Kembali : *{$jamKembali}*\n" .
            "Status : *{$status}*\n\n" .

            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PERIZINAN_KEMBALI, [
                'nama_siswa' => $siswa->nama_lengkap,
                'jam_kembali' => $jamKembali,
                'status' => $status,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
    }

    /*
    |--------------------------------------------------------------------------
    | PPDB - PENDAFTARAN MASUK
    |--------------------------------------------------------------------------
    */

    public static function sendPpdbBaru(Ppdb $ppdb): void
    {
        $nomor = $ppdb->wa_ayah;
    
        if (empty($nomor)) {
            $nomor = $ppdb->wa_ibu;
        }
    
        if (empty($nomor)) {
            $nomor = $ppdb->wa_wali;
        }
    
        if (empty($nomor)) {
            return;
        }

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_BARU)) {
            return;
        }
    
        $nomor = self::formatPhone($nomor);
    
        $default =
            "🎉 *PENDAFTARAN PPDB BERHASIL*\n\n"
    
            ."Assalamu'alaikum Wr. Wb.\n\n"
    
            ."Terima kasih telah melakukan pendaftaran PPDB.\n\n"
    
            ."Berikut akun Portal PPDB Anda:\n\n"
    
            ."👤 Nama : *{$ppdb->nama_lengkap}*\n"
            ."🆔 NISN : *{$ppdb->nisn}*\n"
            ."🔑 Password Awal : *{$ppdb->nisn}*\n\n"
    
            ."Silakan login ke Portal PPDB untuk melengkapi data pendaftaran.\n\n"
    
            ."Demi keamanan akun, segera ubah password setelah berhasil login.\n\n"
    
            ."Barakallahu fiikum.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_BARU, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'nisn' => $ppdb->nisn,
            ], $default)
            : $default;
    
        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_PEMBAYARAN)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $jatuhTempo = Carbon::parse($tagihan->jatuh_tempo)->format('d M Y');

        $default =
            "*TAGIHAN PPDB*\n\n" .

            "Silakan melakukan pembayaran pendaftaran.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Tagihan : *{$tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n" .
            "Jatuh Tempo : *{$jatuhTempo}*\n\n" .

            "Mohon segera melakukan pembayaran.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_PEMBAYARAN, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'judul_tagihan' => $tagihan->judul,
                'nominal' => 'Rp ' . number_format($tagihan->nominal, 0, ',', '.'),
                'jatuh_tempo' => $jatuhTempo,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_TES)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "*INFORMASI TES PPDB*\n\n" .

            "Ananda masuk tahap seleksi tes.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Silakan menunggu jadwal tes dari panitia.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_TES, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'nama_lembaga' => $ppdb->lembaga?->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_LULUS)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "*SELAMAT! ANDA DINYATAKAN LULUS*\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Silakan melanjutkan proses daftar ulang.\n\n" .
            "Barakallahu fiikum.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_LULUS, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'nama_lembaga' => $ppdb->lembaga?->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_TIDAK_LULUS)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "*INFORMASI HASIL PPDB*\n\n" .

            "Mohon maaf, ananda belum dinyatakan lulus seleksi.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Lembaga : *{$ppdb->lembaga?->nama}*\n\n" .

            "Tetap semangat dan sukses selalu.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_TIDAK_LULUS, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'nama_lembaga' => $ppdb->lembaga?->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_DAFTAR_ULANG)) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $default =
            "*TAGIHAN DAFTAR ULANG*\n\n" .

            "Silakan melakukan pembayaran daftar ulang.\n\n" .

            "Nama : *{$ppdb->nama_lengkap}*\n" .
            "Tagihan : *{$tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n\n" .

            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_DAFTAR_ULANG, [
                'nama_lengkap' => $ppdb->nama_lengkap,
                'judul_tagihan' => $tagihan->judul,
                'nominal' => 'Rp ' . number_format($tagihan->nominal, 0, ',', '.'),
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $siswa->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_AKTIF)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $default =
            "*SISWA BERHASIL DIAKTIFKAN*\n\n" .

            "Selamat, ananda resmi menjadi siswa.\n\n" .

            "Nama : *{$siswa->nama_lengkap}*\n" .
            "NIS : *{$siswa->nis}*\n" .
            "Kelas : *{$siswa->kelas?->nama}*\n\n" .

            "Semoga menjadi siswa yang sholeh dan berprestasi.\n\n" .
            "Barakallahu fiikum.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_AKTIF, [
                'nama_siswa' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas?->nama,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
    }
    
    /*
    |--------------------------------------------------------------------------
    | PPDB - RESET PASSWORD
    |--------------------------------------------------------------------------
    */
    public static function sendPpdbResetPassword(Ppdb $ppdb): void
    {
        $nomor = $ppdb->wa_ayah;
    
        if (empty($nomor)) {
            $nomor = $ppdb->wa_ibu;
        }
    
        if (empty($nomor)) {
            $nomor = $ppdb->wa_wali;
        }
    
        if (empty($nomor)) {
            return;
        }

        $lembagaId = $ppdb->lembaga_id;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PPDB_RESET_PASSWORD)) {
            return;
        }
    
        $default = "🔑 *RESET PASSWORD PORTAL PPDB*\n\n"
            ."Assalamu'alaikum Wr. Wb.\n\n"
            ."Password Portal PPDB berhasil direset.\n\n"
            ."Silakan login menggunakan:\n"
            ."NISN : {$ppdb->nisn}\n"
            ."Password : {$ppdb->nisn}\n\n"
            ."Setelah berhasil login, segera ubah password Anda.\n\n"
            ."Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PPDB_RESET_PASSWORD, [
                'nisn' => $ppdb->nisn,
            ], $default)
            : $default;
            
        $nomor = self::formatPhone($nomor);
        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $user->lembaga_id ?? null;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::TAGIHAN)) {
            return;
        }

        $status = match ($tagihan->status) {
            'Belum' => 'Belum Lunas',
            'sebagian' => 'Sebagian',
            'lunas' => 'Lunas',
            default => $tagihan->status,
        };

        $nomor = self::formatPhone($nomor);
        $sisa = $tagihan->nominal - $tagihan->nominal_terbayar;
        $jatuhTempo = Carbon::parse($tagihan->jatuh_tempo)->format('d M Y');

        $default =
            "*TAGIHAN SEKOLAH*\n\n" .

            "Ananda Memiliki Tagihan Pembayaran.\n\n" .

            "Nama : *{$user->nama_lengkap}*\n" .

            "Jenis Tagihan : *{$tagihan->judul}*\n" .
            "Total Tagihan : *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*\n" .
            "Terbayar : *Rp " . number_format($tagihan->nominal_terbayar, 0, ',', '.') . "*\n" .
            "Status : *{$status}*\n\n" .
            "Sisa : *Rp " . number_format($sisa, 0, ',', '.') . "*\n" .
            "Jatuh Tempo : *{$jatuhTempo}*\n\n" .

            "Mohon segera melakukan pembayaran ke rekening resmi yayasan.\n\n" .
            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::TAGIHAN, [
                'nama_siswa' => $user->nama_lengkap,
                'judul_tagihan' => $tagihan->judul,
                'total_tagihan' => 'Rp ' . number_format($tagihan->nominal, 0, ',', '.'),
                'terbayar' => 'Rp ' . number_format($tagihan->nominal_terbayar, 0, ',', '.'),
                'status' => $status,
                'sisa' => 'Rp ' . number_format($sisa, 0, ',', '.'),
                'jatuh_tempo' => $jatuhTempo,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
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

        $lembagaId = $user->lembaga_id ?? null;

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::PEMBAYARAN)) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $tanggal = Carbon::now()->format('d M Y H:i');

        $default =
            "*PEMBAYARAN BERHASIL*\n\n" .

            "Pembayaran telah diterima.\n\n" .

            "Nama : *{$user->nama_lengkap}*\n" .
            "Pembayaran : *{$pembayaran->tagihan->judul}*\n" .
            "Nominal : *Rp " . number_format($pembayaran->nominal, 0, ',', '.') . "*\n" .
            "Tanggal : *{$tanggal}*\n\n" .

            "Terima kasih.";

        $pesan = $lembagaId
            ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::PEMBAYARAN, [
                'nama_siswa' => $user->nama_lengkap,
                'judul_tagihan' => $pembayaran->tagihan->judul,
                'nominal' => 'Rp ' . number_format($pembayaran->nominal, 0, ',', '.'),
                'tanggal' => $tanggal,
            ], $default)
            : $default;

        self::wa($nomor, $pesan, $lembagaId);
    }
    
    /*
    |--------------------------------------------------------------------------
    | PENGUMUMAN
    |--------------------------------------------------------------------------
    */
    
    public static function sendAnnouncement($recipient, string $message)
    {
        $nomor = null;
        $lembagaId = null;
    
        if ($recipient instanceof \App\Models\Pegawai) {
            $nomor = $recipient->no_hp;
            $lembagaId = $recipient->lembagas()->value('lembagas.id');
        }
    
        elseif ($recipient instanceof \App\Models\Siswa) {
            $nomor =
                $recipient->wa_wali
                ?: $recipient->wa_ayah
                ?: $recipient->wa_ibu;
            $lembagaId = $recipient->lembaga_id;
        }
    
        elseif ($recipient instanceof \App\Models\Ppdb) {
            $nomor =
                $recipient->wa_wali
                ?: $recipient->wa_ayah
                ?: $recipient->wa_ibu;
            $lembagaId = $recipient->lembaga_id;
        }
    
        if (! $nomor) {
            return;
        }

        if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::ANNOUNCEMENT)) {
            return;
        }
    
        $nomor = self::formatPhone($nomor);
        self::wa($nomor, $message, $lembagaId);
    }

    /*
    |--------------------------------------------------------------------------
    | IZIN HARIAN (TIDAK MASUK SEKOLAH - IZIN/SAKIT)
    |--------------------------------------------------------------------------
    */

    public static function sendIzinHarianDiproses($izin)
    {
        $statusLabel = $izin->status === 'approved' ? 'DISETUJUI' : 'DITOLAK';
        $tanggal = Carbon::parse($izin->tanggal_mulai)->format('d M Y') . " - " . Carbon::parse($izin->tanggal_selesai)->format('d M Y');
        $alasan = ($izin->status === 'ditolak' && $izin->catatan_admin) ? $izin->catatan_admin : '';

        if ($izin->tipe === 'siswa') {

            $siswa = $izin->siswa;
            if (!$siswa) return;

            $nomor = $siswa->wa_ayah ?? $siswa->wa_ibu;
            if (!$nomor) return;

            $lembagaId = $siswa->lembaga_id;

            if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::IZIN_HARIAN_DIPROSES)) {
                return;
            }

            $nomor = self::formatPhone($nomor);

            $default =
                "*PENGAJUAN {$izin->jenis} {$statusLabel}*\n\n" .
                "Pengajuan {$izin->jenis} untuk ananda telah {$statusLabel}.\n\n" .
                "Nama : *{$siswa->nama_lengkap}*\n" .
                "Jenis : *{$izin->jenis}*\n" .
                "Tanggal : *{$tanggal}*\n" .
                ($alasan ? "Alasan : *{$alasan}*\n" : "") .
                "\nTerima kasih.";

            $pesan = $lembagaId
                ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::IZIN_HARIAN_DIPROSES, [
                    'nama' => $siswa->nama_lengkap,
                    'jenis_izin' => $izin->jenis,
                    'status_izin' => $statusLabel,
                    'tanggal_mulai' => Carbon::parse($izin->tanggal_mulai)->format('d M Y'),
                    'tanggal_selesai' => Carbon::parse($izin->tanggal_selesai)->format('d M Y'),
                    'alasan_ditolak' => $alasan,
                ], $default)
                : $default;

            self::wa($nomor, $pesan, $lembagaId);

        } else {

            $pegawai = $izin->pegawai;
            if (!$pegawai || !$pegawai->no_hp) return;

            $lembagaId = $pegawai->lembagas?->first()?->id;

            if ($lembagaId && ! \App\Models\NotificationTypeToggle::isEnabled($lembagaId, \App\Support\NotificationType::IZIN_HARIAN_DIPROSES)) {
                return;
            }

            $nomor = self::formatPhone($pegawai->no_hp);

            $default =
                "*PENGAJUAN {$izin->jenis} {$statusLabel}*\n\n" .
                "Pengajuan {$izin->jenis} Anda telah {$statusLabel}.\n\n" .
                "Nama : *{$pegawai->nama}*\n" .
                "Jenis : *{$izin->jenis}*\n" .
                "Tanggal : *{$tanggal}*\n" .
                ($alasan ? "Alasan : *{$alasan}*\n" : "") .
                "\nTerima kasih.";

            $pesan = $lembagaId
                ? \App\Models\LembagaNotificationTemplate::renderFor($lembagaId, \App\Support\NotificationType::IZIN_HARIAN_DIPROSES, [
                    'nama' => $pegawai->nama,
                    'jenis_izin' => $izin->jenis,
                    'status_izin' => $statusLabel,
                    'tanggal_mulai' => Carbon::parse($izin->tanggal_mulai)->format('d M Y'),
                    'tanggal_selesai' => Carbon::parse($izin->tanggal_selesai)->format('d M Y'),
                    'alasan_ditolak' => $alasan,
                ], $default)
                : $default;

            self::wa($nomor, $pesan, $lembagaId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TAGIHAN LANGGANAN QINARA (Yayasan -> Qinara)
    |--------------------------------------------------------------------------
    */

    public static function sendTagihanSubscription($yayasan, $totalTagihan, $paymentUrl, $periode)
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);
        $bulan = Carbon::createFromFormat('Y-m', $periode)->locale('id')->translatedFormat('F Y');

        $pesan = \App\Models\NotificationTemplate::render('tagihan_subscription', [
            'nama_yayasan' => $yayasan->nama,
            'periode' => $bulan,
            'total_tagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'link_pembayaran' => $paymentUrl,
        ], default:
            "*TAGIHAN LANGGANAN QINARA APPS*\n\n" .
            "Yth. {$yayasan->nama},\n\n" .
            "Tagihan langganan periode *{$bulan}* telah terbit.\n\n" .
            "Total Tagihan : *Rp " . number_format($totalTagihan, 0, ',', '.') . "*\n\n" .
            "Silakan lakukan pembayaran melalui link berikut:\n" .
            $paymentUrl . "\n\n" .
            "Terima kasih telah menggunakan Qinara Apps."
        );

        // lembagaId sengaja null -> WhatsappService fallback ke setting WA
        // aktif pertama yang ditemukan, karena ini notifikasi level
        // Yayasan/platform, bukan spesifik 1 Lembaga tertentu.
        self::waPlatform($nomor, $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | BROADCAST PLATFORM (update info, produk/layanan Qinara)
    |--------------------------------------------------------------------------
    */

    public static function sendBroadcastYayasan($yayasan, string $judul, string $pesan): bool
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return false;
        }

        $nomor = self::formatPhone($nomor);

        $isiLengkap =
            "*{$judul}*\n\n" .
            $pesan . "\n\n" .
            "— Tim Qinara Apps";

        self::waPlatform($nomor, $isiLengkap);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | REMINDER TRIAL AKAN HABIS (H-7 / H-3 / H-1)
    |--------------------------------------------------------------------------
    */

    public static function sendTrialReminder($yayasan, int $sisaHari): bool
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return false;
        }

        $nomor = self::formatPhone($nomor);
        $tanggal = $yayasan->trial_ends_at?->locale('id')->translatedFormat('d M Y');

        $pesan = \App\Models\NotificationTemplate::render('trial_reminder', [
            'nama_yayasan' => $yayasan->nama,
            'sisa_hari' => $sisaHari,
            'tanggal_berakhir' => $tanggal,
        ], default:
            "*MASA TRIAL QINARA APPS AKAN BERAKHIR*\n\n" .
            "Yth. {$yayasan->nama},\n\n" .
            "Masa coba gratis Anda tinggal *{$sisaHari} hari lagi* (berakhir {$tanggal}).\n\n" .
            "Segera pilih modul yang mau dilanjutkan dan lakukan pembayaran di menu \"Langganan\" supaya akses tidak terputus.\n\n" .
            "Terima kasih telah mencoba Qinara Apps."
        );

        self::waPlatform($nomor, $pesan);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | PENDAFTARAN BERHASIL & AKTIVASI
    |--------------------------------------------------------------------------
    */

    public static function sendPendaftaranBerhasil($yayasan, string $namaAdmin, string $email, string $password): bool
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return false;
        }

        $nomor = self::formatPhone($nomor);
        $tanggalTrial = $yayasan->trial_ends_at?->locale('id')->translatedFormat('d M Y') ?? '-';

        $pesan = \App\Models\NotificationTemplate::render('pendaftaran_berhasil', [
            'nama_admin' => $namaAdmin,
            'nama_yayasan' => $yayasan->nama,
            'tanggal_trial_berakhir' => $tanggalTrial,
            'email' => $email,
            'password' => $password,
            'link_login' => rtrim(config('app.url'), '/') . '/admin/' . $yayasan->slug,
        ], default:
            "*SELAMAT DATANG DI QINARA APPS!*\n\n" .
            "Yth. {$namaAdmin},\n\n" .
            "Pendaftaran *{$yayasan->nama}* berhasil. Masa coba gratis 14 hari sudah aktif sampai {$tanggalTrial}.\n\n" .
            "Email: {$email}\nPassword: {$password}"
        );

        self::waPlatform($nomor, $pesan);

        return true;
    }

    public static function sendAplikasiAktif($yayasan): bool
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return false;
        }

        $nomor = self::formatPhone($nomor);

        $pesan = \App\Models\NotificationTemplate::render('aplikasi_aktif', [
            'nama_yayasan' => $yayasan->nama,
        ], default:
            "*LANGGANAN QINARA APPS AKTIF*\n\nYth. {$yayasan->nama},\n\nPembayaran berhasil, langganan sekarang aktif penuh."
        );

        self::waPlatform($nomor, $pesan);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | CRM - LEAD BARU (notifikasi internal, bukan ke calon client)
    |--------------------------------------------------------------------------
    */

    /**
     * Notifikasi WA ke tim internal (sales/admin Qinara) setiap ada lead
     * baru mendaftar trial lewat /daftar. Nomor tujuan diambil dari
     * LandingSetting->crm_notif_wa_numbers (dipisah koma, bisa lebih
     * dari satu nomor). Kalau field itu kosong, fungsi ini tidak
     * mengirim apa-apa (lead tetap tercatat di CRM seperti biasa).
     */
    public static function sendLeadBaruInternal(\App\Models\Lead $lead): bool
    {
        $setting = \App\Models\LandingSetting::current();
        $nomorMentah = $setting->crm_notif_wa_numbers ?? null;

        if (! $nomorMentah) {
            return false;
        }

        $pesan = \App\Models\NotificationTemplate::render('lead_baru_internal', [
            'nama_lembaga' => $lead->nama_lembaga,
            'nama_pic' => $lead->nama_pic ?? '-',
            'no_hp' => $lead->no_hp ?? '-',
            'email' => $lead->email ?? '-',
            'tanggal' => $lead->created_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '-',
        ], default:
            "*LEAD BARU MASUK*\n\nLembaga: {$lead->nama_lembaga}\nPIC: " .
            ($lead->nama_pic ?? '-') . "\nNo. HP: " . ($lead->no_hp ?? '-') .
            "\nEmail: " . ($lead->email ?? '-') .
            "\n\nSegera follow up dari panel CRM."
        );

        $terkirim = false;

        foreach (explode(',', $nomorMentah) as $nomor) {
            $nomor = trim($nomor);

            if ($nomor === '') {
                continue;
            }

            self::waPlatform(self::formatPhone($nomor), $pesan);
            $terkirim = true;
        }

        return $terkirim;
    }

    public static function sendTagihanSubscriptionTahunan($yayasan, $totalTagihan, $paymentUrl, $tahunPeriode)
    {
        $nomor = $yayasan->telepon ?? null;

        if (! $nomor) {
            return;
        }

        $nomor = self::formatPhone($nomor);

        $pesan = \App\Models\NotificationTemplate::render('tagihan_subscription_tahunan', [
            'nama_yayasan' => $yayasan->nama,
            'tahun_periode' => $tahunPeriode,
            'total_tagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'link_pembayaran' => $paymentUrl,
        ], default:
            "*TAGIHAN LANGGANAN TAHUNAN QINARA APPS*\n\n" .
            "Yth. {$yayasan->nama},\n\n" .
            "Tagihan perpanjangan langganan TAHUNAN untuk periode *{$tahunPeriode}* telah terbit.\n\n" .
            "Total Tagihan (1 Tahun) : *Rp " . number_format($totalTagihan, 0, ',', '.') . "*\n\n" .
            "Silakan lakukan pembayaran melalui link berikut:\n" .
            $paymentUrl . "\n\n" .
            "Terima kasih telah berlangganan Qinara Apps."
        );

        self::waPlatform($nomor, $pesan);
    }

}
