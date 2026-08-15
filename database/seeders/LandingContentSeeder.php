<?php

namespace Database\Seeders;

use App\Models\EkosistemSolusi;
use App\Models\FaqItem;
use App\Models\LandingSetting;
use App\Models\MasalahSolusi;
use App\Models\ModulAplikasi;
use App\Models\Testimoni;
use Illuminate\Database\Seeder;

class LandingContentSeeder extends Seeder
{
    public function run(): void
    {
        LandingSetting::updateOrCreate(['id' => 1], [
            'brand_name' => 'Qinara Apps',
            'badge_text' => 'Sistem Manajemen Lembaga Pendidikan Islam Modern',
            'headline_baris1' => 'Sistem Administrasi Masih Berantakan & Manual?',
            'headline_baris2' => 'Saatnya Beralih ke Sistem Digital Terpadu untuk Lembaga Pendidikan Islam',
            'subheadline' => 'Kelola keuangan, akademik, absensi, tahfidz, hingga laporan dalam satu sistem yang lebih cepat, rapi, dan real-time — cocok untuk pesantren, madrasah, maupun sekolah Islam terpadu.',
            'whatsapp_number' => '62877724804500',
            'whatsapp_pesan_default' => "Assalamu'alaikum Admin Qinara Apps.\n\nSaya tertarik menggunakan Qinara Apps untuk lembaga kami dan ingin mendapatkan informasi lebih lanjut mengenai paket dan demo.",
            'email_kontak' => 'qinaraindonesia.id@gmail.com',
            'alamat' => 'Serang, Banten, Indonesia',
            'ig_url' => 'https://instagram.com/qinaraindonesia',
            'fb_url' => 'https://facebook.com/qinaraindonesia',
            'yt_url' => 'https://youtube.com/@qinaraindonesia',
            'x_url' => 'https://x.com/qinaraindonesia',
            'hero_kpi_keuangan' => 'Rp 142.850.000',
            'hero_kpi_keuangan_growth' => '+12% bulan ini',
            'hero_kpi_kehadiran_persen' => 98.2,
            'social_proof_text' => '120+ Lembaga Pendidikan Islam',
            'stat_efisiensi' => '70%',
            'stat_modul' => '10+',
            'stat_akses' => '24/7',
            'stat_digitalisasi' => '100%',
            'footer_text' => '© '.date('Y').' Qinara Apps. Hak Cipta Dilindungi Undang-Undang.',
            'footer_legalitas' => 'PT Qinara Indonesia',
        ]);

        $masalahSolusi = [
            ['teks_masalah' => 'Data siswa masih tersebar dan tidak terpusat', 'teks_solusi' => 'Manajemen data siswa terpusat dan mudah diakses'],
            ['teks_masalah' => 'PPDB masih manual dan sulit dipantau', 'teks_solusi' => 'PPDB online lebih cepat dan terstruktur'],
            ['teks_masalah' => 'Pengelolaan keuangan sering lambat dan tidak transparan', 'teks_solusi' => 'Keuangan otomatis, transparan, dan real-time'],
            ['teks_masalah' => 'Jurnal mengajar dan rapor masih dicatat manual', 'teks_solusi' => 'Jurnal mengajar & rapor digital terintegrasi'],
            ['teks_masalah' => 'Absensi masih manual dan tidak real-time', 'teks_solusi' => 'Absensi siswa otomatis dan real-time'],
            ['teks_masalah' => 'Prestasi dan pelanggaran sulit dipantau', 'teks_solusi' => 'Monitoring prestasi & pelanggaran lebih mudah'],
            ['teks_masalah' => 'Data tahfidz/hafalan tidak terdokumentasi dengan rapi', 'teks_solusi' => 'Pencatatan tahfidz/hafalan lebih rapi dan terdokumentasi'],
            ['teks_masalah' => 'Perizinan siswa masih manual dan bertele-tele', 'teks_solusi' => 'Sistem perizinan siswa digital dan cepat disetujui'],
        ];
        foreach ($masalahSolusi as $i => $ms) {
            MasalahSolusi::updateOrCreate(['teks_masalah' => $ms['teks_masalah']], $ms + ['urutan' => $i + 1]);
        }

        $ekosistem = [
            ['icon' => 'bar-chart-3', 'judul' => 'Solusi Pimpinan', 'deskripsi' => 'Akses dashboard statistik real-time untuk memantau grafik keuangan SPP, total kehadiran guru-siswa, serta performa akademik keseluruhan secara ringkas.', 'tag_text' => 'Monitoring Executive'],
            ['icon' => 'banknote', 'judul' => 'Solusi Bendahara', 'deskripsi' => 'Otomatisasi pengiriman invoice pembayaran SPP ke WhatsApp wali siswa, rekonsiliasi kas bank otomatis, dan manajemen uang jajan non-tunai.', 'tag_text' => 'Automated Billing'],
            ['icon' => 'book-open', 'judul' => 'Solusi Akademik & Guru', 'deskripsi' => 'Isi jurnal harian mengajar lewat HP, kelola absensi kelas, pantau setoran tahfidz siswa per juz, serta pengisian nilai rapor digital secara kolektif tanpa ribet.', 'tag_text' => 'Sistem Akademis'],
            ['icon' => 'smartphone', 'judul' => 'Solusi Wali Siswa', 'deskripsi' => 'Portal khusus untuk melihat tagihan bulanan, cek kedisiplinan dan poin sanksi siswa, riwayat izin, serta riwayat kelancaran hafalan anak.', 'tag_text' => 'Portal Orang Tua'],
        ];
        foreach ($ekosistem as $i => $e) {
            EkosistemSolusi::updateOrCreate(['judul' => $e['judul']], $e + ['urutan' => $i + 1]);
        }

        $modul = [
            ['icon' => 'wallet', 'judul' => 'Keuangan Lembaga', 'deskripsi' => 'Kelola tagihan SPP, uang makan, pembangunan, dan kas lembaga secara terotomatisasi. Laporan keuangan terbit seketika, mengurangi risiko kesalahan pencatatan manual.', 'tag_text' => 'Laporan Keuangan Otomatis'],
            ['icon' => 'credit-card', 'judul' => 'Wallet Siswa', 'deskripsi' => 'Batasi uang saku siswa secara digital dengan teknologi cashless. Mencegah kehilangan uang tunai, serta memantau transaksi harian secara real-time di koperasi sekolah.', 'tag_text' => 'Transaksi Non-Tunai'],
            ['icon' => 'user-plus', 'judul' => 'PPDB Online', 'deskripsi' => 'Penerimaan Siswa Baru terintegrasi satu pintu dari formulir digital, verifikasi berkas otomatis, ujian seleksi mandiri, hingga pengumuman kelulusan dan pembayaran biaya awal.', 'tag_text' => 'Penerimaan Siswa Mandiri'],
            ['icon' => 'file-check-2', 'judul' => 'Rapor Digital', 'deskripsi' => 'Penilaian otomatis berbasis kurikulum nasional maupun kurikulum keagamaan. Cetak format transkrip nilai rapi yang langsung dikonversi menjadi berkas PDF siap cetak kapan pun.', 'tag_text' => 'Evaluasi Akademis Praktis'],
            ['icon' => 'notebook-pen', 'judul' => 'Jurnal Mengajar', 'deskripsi' => 'Membantu guru mendokumentasikan agenda harian mengajar, cakupan topik kajian, serta mencatat catatan evaluasi kelas langsung melalui ponsel cerdas pengajar.', 'tag_text' => 'Sistem Agenda Pengajar'],
            ['icon' => 'book-marked', 'judul' => "Tahfidz & Al-Qur'an", 'deskripsi' => "Pantau capaian setoran hafalan harian siswa secara komprehensif. Catat progres juz, nomor surah, ketepatan makhraj, tajwid, hingga kelancaran muroja'ah.", 'tag_text' => 'Monitoring Progres Hafalan'],
            ['icon' => 'door-open', 'judul' => 'Perizinan Siswa', 'deskripsi' => 'Alur digital pengajuan izin keluar masuk lingkungan sekolah. Verifikasi instan melalui sistem pemindai QR Code guna melacak status siswa secara aman dan disiplin.', 'tag_text' => 'Sistem Keamanan Gerbang'],
            ['icon' => 'shield-alert', 'judul' => 'Pelanggaran Siswa', 'deskripsi' => 'Pencatatan akumulasi poin pelanggaran dan ketertiban secara transparan. Meminimalisir bias penilaian tindakan disipliner berlandaskan aturan formal lembaga.', 'tag_text' => 'Ketertiban Obyektif'],
            ['icon' => 'trophy', 'judul' => 'Prestasi Siswa', 'deskripsi' => 'Pendataan sertifikat kejuaraan, penghargaan hafalan tercepat, hingga capaian ekstra-kurikuler siswa demi menciptakan iklim motivasi belajar yang positif.', 'tag_text' => 'Portofolio Prestasi'],
            ['icon' => 'scan-face', 'judul' => 'Absensi Digital Real-time', 'deskripsi' => 'Rekam daftar hadir siswa secara instan pada saat jam masuk kelas, kegiatan ekstrakurikuler, maupun kegiatan keagamaan harian. Mengurangi risiko bolos, memudahkan pendeteksian dini kerawanan ketidakhadiran, serta didukung sistem notifikasi pengiriman ke orang tua.', 'tag_text' => null, 'is_featured' => true],
        ];
        foreach ($modul as $i => $m) {
            ModulAplikasi::updateOrCreate(['judul' => $m['judul']], $m + ['urutan' => $i + 1, 'is_featured' => $m['is_featured'] ?? false]);
        }

        $testimonis = [
            [
                'nama' => 'K.H. Ahmad Dahlan',
                'jabatan' => 'Pimpinan Yayasan Al-Amanah',
                'isi' => "Semenjak menggunakan Qinara Apps, wali siswa kami sangat terbantu memantau progres hafalan Al-Qur'an anak-anak. Transparansi keuangan SPP pun naik drastis, sehingga kepercayaan orang tua bertambah.",
                'rating' => 5, 'urutan' => 1,
            ],
            [
                'nama' => 'Ustadzah Maryam',
                'jabatan' => 'Kepala Tata Usaha SDIT Darussalam',
                'isi' => 'Urusan administrasi PPDB dan rapor yang biasanya memakan waktu berminggu-minggu kini bisa selesai dalam hitungan hari. Pekerjaan staf TU jadi jauh lebih ringan dan tertata.',
                'rating' => 5, 'urutan' => 2,
            ],
            [
                'nama' => 'Gus Zaki',
                'jabatan' => 'Dewan Pengasuh Pondok Pesantren Krapyak Mandiri',
                'isi' => 'Aplikasi ini sangat gampang dipahami, bahkan untuk asatidzah sepuh yang awalnya kaku dengan teknologi. Customer service-nya juga super responsif mendampingi lembaga kami.',
                'rating' => 5, 'urutan' => 3,
            ],
        ];
        foreach ($testimonis as $t) {
            Testimoni::updateOrCreate(['nama' => $t['nama']], $t);
        }

        $faqs = [
            ['pertanyaan' => 'Apakah Qinara Apps sulit digunakan untuk pemula?', 'jawaban' => 'Sama sekali tidak. Qinara Apps dirancang khusus dengan antarmuka yang sangat ramah pengguna (user-friendly), menggunakan Bahasa Indonesia yang luas dan layout minimalis. Kami juga menyediakan tim bantuan teknis gratis untuk mendampingi masa awal proses adaptasi lembaga Anda.', 'urutan' => 1],
            ['pertanyaan' => 'Apakah data rahasia siswa dan keuangan kami dijamin aman?', 'jawaban' => 'Keamanan data Anda adalah prioritas mutlak kami. Seluruh lalu lintas data dienkripsi dengan standar enkripsi SSL 256-bit kelas atas, serta cadangan berkala harian secara otomatis guna menghindari kehilangan data akibat gangguan perangkat keras lokal Anda.', 'urutan' => 2],
            ['pertanyaan' => 'Apakah kami bisa mengajukan kustomisasi atau fitur tambahan tertentu?', 'jawaban' => 'Bisa sekali. Tim pengembang internal Qinara Apps siap melayani diskusi kebutuhan khusus, modifikasi sistem, integrasi metode eksternal, atau kustomisasi alur bisnis khusus yang sesuai dengan keunikan kurikulum lembaga Anda.', 'urutan' => 3],
            ['pertanyaan' => 'Apakah pihak lembaga harus menginstal aplikasi tertentu di komputer?', 'jawaban' => 'Tidak perlu. Qinara Apps adalah aplikasi berbasis awan (Cloud-based SaaS). Anda cukup mengaksesnya melalui peramban internet (browser) di laptop, PC, komputer, tablet, maupun ponsel pintar Anda kapan saja dan di mana saja.', 'urutan' => 4],
            ['pertanyaan' => 'Apakah Qinara Apps cocok untuk pesantren, madrasah, maupun sekolah Islam terpadu?', 'jawaban' => 'Sangat cocok. Mulai dari madrasah rintisan berskala kecil, sekolah Islam terpadu, hingga pondok pesantren modern berkapasitas ribuan siswa, sistem kami sangat elastis untuk disesuaikan berkat biaya dasar yang ringan dan modul yang bisa ditambah satu-satu sesuai kebutuhan.', 'urutan' => 5],
            ['pertanyaan' => 'Bagaimana dengan proses migrasi data dari sistem lama atau file Excel?', 'jawaban' => 'Kami menyediakan fitur import data siswa langsung melalui template format Excel yang mudah dipahami. Selain itu, tim Customer Success kami siap memandu dan membantu proses migrasi data dari sistem lama Anda ke basis data Qinara Apps secara gratis hingga tuntas.', 'urutan' => 6],
        ];
        foreach ($faqs as $f) {
            FaqItem::updateOrCreate(['pertanyaan' => $f['pertanyaan']], $f);
        }
    }
}
