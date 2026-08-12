<?php

namespace App\Support;

/**
 * Katalog SEMUA jenis notifikasi WA level-sekolah (22 jenis, sesuai
 * method di NotificationService) -- pola SAMA seperti FeatureGate
 * (daftar statis di kode, BUKAN tabel database), dipakai sebagai
 * referensi untuk UI toggle & daftar pilihan.
 *
 * BARU dipakai untuk fondasi (toggle + katalog) -- method
 * NotificationService yang sebenarnya BELUM diubah untuk cek toggle
 * ini (itu pekerjaan lanjutan "konversi bertahap" yang disebutkan
 * sebelumnya). Menambah baris di sini TIDAK otomatis mengubah
 * perilaku pengiriman apapun sampai method terkait benar-benar
 * memanggil NotificationTypeToggle::isEnabled().
 */
class NotificationType
{
    public const ABSENSI_SISWA = 'absensi_siswa';
    public const ABSENSI_GURU = 'absensi_guru';
    public const ABSENSI_HARIAN = 'absensi_harian';
    public const ABSENSI_HARIAN_GURU = 'absensi_harian_guru';
    public const PELANGGARAN = 'pelanggaran';
    public const PRESTASI = 'prestasi';
    public const TAHFIDZ = 'tahfidz';
    public const PERIZINAN_APPROVED = 'perizinan_approved';
    public const PERIZINAN_DIJEMPUT = 'perizinan_dijemput';
    public const PERIZINAN_KEMBALI = 'perizinan_kembali';
    public const PPDB_BARU = 'ppdb_baru';
    public const PPDB_PEMBAYARAN = 'ppdb_pembayaran';
    public const PPDB_TES = 'ppdb_tes';
    public const PPDB_LULUS = 'ppdb_lulus';
    public const PPDB_TIDAK_LULUS = 'ppdb_tidak_lulus';
    public const PPDB_DAFTAR_ULANG = 'ppdb_daftar_ulang';
    public const PPDB_AKTIF = 'ppdb_aktif';
    public const PPDB_RESET_PASSWORD = 'ppdb_reset_password';
    public const TAGIHAN = 'tagihan';
    public const PEMBAYARAN = 'pembayaran';
    public const ANNOUNCEMENT = 'announcement';
    public const IZIN_HARIAN_DIPROSES = 'izin_harian_diproses';

    /**
     * @return array<string, array{nama: string, kategori: string, placeholder: string}>
     */
    public static function all(): array
    {
        return [
            self::ABSENSI_SISWA => ['nama' => 'Absensi Siswa', 'kategori' => 'Absensi', 'placeholder' => '{nama_siswa}, {kegiatan}, {status}, {jam}'],
            self::ABSENSI_GURU => ['nama' => 'Absensi Guru', 'kategori' => 'Absensi', 'placeholder' => '{nama_guru}, {niy}, {kegiatan}, {status}, {jam}'],
            self::ABSENSI_HARIAN => ['nama' => 'Rekap Absensi Harian (Siswa)', 'kategori' => 'Absensi', 'placeholder' => '{nama_siswa}, {jenis} (masuk/pulang), {status}, {jam}'],
            self::ABSENSI_HARIAN_GURU => ['nama' => 'Rekap Absensi Harian (Guru)', 'kategori' => 'Absensi', 'placeholder' => '{nama_guru}, {niy}, {jenis} (masuk/pulang), {status}, {jam}'],
            self::PELANGGARAN => ['nama' => 'Pelanggaran Siswa', 'kategori' => 'Kedisiplinan', 'placeholder' => '{nama_siswa}, {nama_pelanggaran}, {poin}'],
            self::PRESTASI => ['nama' => 'Prestasi Siswa', 'kategori' => 'Kedisiplinan', 'placeholder' => '{nama_siswa}, {nama_prestasi}'],
            self::TAHFIDZ => ['nama' => 'Progres Tahfidz', 'kategori' => 'Akademik', 'placeholder' => '{nama_siswa}, {jenis} (ziyadah/murajaah), {surah}, {ayat_dari}, {ayat_sampai}, {nilai}, {musyrif}'],
            self::PERIZINAN_APPROVED => ['nama' => 'Perizinan Disetujui', 'kategori' => 'Perizinan', 'placeholder' => '{nama_siswa}, {tipe_izin}, {keperluan}, {tanggal_izin}, {batas_kembali}'],
            self::PERIZINAN_DIJEMPUT => ['nama' => 'Perizinan — Sudah Dijemput', 'kategori' => 'Perizinan', 'placeholder' => '{nama_siswa}, {penjemput}, {hubungan}, {jam_keluar}'],
            self::PERIZINAN_KEMBALI => ['nama' => 'Perizinan — Sudah Kembali', 'kategori' => 'Perizinan', 'placeholder' => '{nama_siswa}, {jam_kembali}, {status}'],
            self::IZIN_HARIAN_DIPROSES => ['nama' => 'Izin Harian Diproses', 'kategori' => 'Perizinan', 'placeholder' => '{nama} (siswa/guru), {jenis_izin}, {status_izin} (DISETUJUI/DITOLAK), {tanggal_mulai}, {tanggal_selesai}, {alasan_ditolak}'],
            self::PPDB_BARU => ['nama' => 'PPDB — Pendaftaran Baru', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {nisn} (juga jadi password awal)'],
            self::PPDB_PEMBAYARAN => ['nama' => 'PPDB — Konfirmasi Pembayaran', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {judul_tagihan}, {nominal}, {jatuh_tempo}'],
            self::PPDB_TES => ['nama' => 'PPDB — Jadwal Tes', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {nama_lembaga}'],
            self::PPDB_LULUS => ['nama' => 'PPDB — Lulus', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {nama_lembaga}'],
            self::PPDB_TIDAK_LULUS => ['nama' => 'PPDB — Tidak Lulus', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {nama_lembaga}'],
            self::PPDB_DAFTAR_ULANG => ['nama' => 'PPDB — Daftar Ulang', 'kategori' => 'PPDB', 'placeholder' => '{nama_lengkap}, {judul_tagihan}, {nominal}'],
            self::PPDB_AKTIF => ['nama' => 'PPDB — Siswa Aktif', 'kategori' => 'PPDB', 'placeholder' => '{nama_siswa}, {nis}, {kelas}'],
            self::PPDB_RESET_PASSWORD => ['nama' => 'PPDB — Reset Password', 'kategori' => 'PPDB', 'placeholder' => '{nisn} (juga jadi password baru)'],
            self::TAGIHAN => ['nama' => 'Tagihan SPP/Lainnya', 'kategori' => 'Keuangan', 'placeholder' => '{nama_siswa}, {judul_tagihan}, {total_tagihan}, {terbayar}, {status}, {sisa}, {jatuh_tempo}'],
            self::PEMBAYARAN => ['nama' => 'Konfirmasi Pembayaran', 'kategori' => 'Keuangan', 'placeholder' => '{nama_siswa}, {judul_tagihan}, {nominal}, {tanggal}'],
            self::ANNOUNCEMENT => ['nama' => 'Pengumuman Sekolah', 'kategori' => 'Umum', 'placeholder' => 'Tidak ada placeholder — isi pesan ditulis bebas setiap kali kirim, bukan template tetap.'],
        ];
    }

    public static function kategoriList(): array
    {
        return collect(self::all())->pluck('kategori')->unique()->values()->all();
    }
}
