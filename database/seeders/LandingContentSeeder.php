<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use App\Models\LandingSetting;
use App\Models\PaketHarga;
use App\Models\Testimoni;
use Illuminate\Database\Seeder;

class LandingContentSeeder extends Seeder
{
    public function run(): void
    {
        LandingSetting::updateOrCreate(['id' => 1], [
            'brand_name' => 'Qinara Apps',
            'badge_text' => 'Sistem Manajemen Pesantren Modern',
            'headline_baris1' => 'Sistem Administrasi Masih Berantakan & Manual?',
            'headline_baris2' => 'Saatnya Beralih ke Sistem Pesantren Digital Terpadu',
            'subheadline' => 'Kelola keuangan, akademik, absensi, tahfidz, hingga laporan dalam satu sistem yang lebih cepat, rapi, dan real-time.',
            'whatsapp_number' => '62877724804500',
            'whatsapp_pesan_default' => "Assalamu'alaikum Admin Qinara Apps.\n\nSaya tertarik menggunakan Qinara Apps untuk pesantren kami dan ingin mendapatkan informasi lebih lanjut mengenai paket dan demo.",
            'email_kontak' => 'qinaraindonesia.id@gmail.com',
            'alamat' => 'Serang, Banten, Indonesia',
            'ig_url' => 'https://instagram.com/qinaraindonesia',
            'fb_url' => 'https://facebook.com/qinaraindonesia',
            'yt_url' => 'https://youtube.com/@qinaraindonesia',
            'x_url' => 'https://x.com/qinaraindonesia',
            'hero_kpi_keuangan' => 'Rp 142.850.000',
            'hero_kpi_keuangan_growth' => '+12% bulan ini',
            'hero_kpi_kehadiran_persen' => 98.2,
            'social_proof_text' => '120+ Pesantren',
            'stat_efisiensi' => '70%',
            'stat_modul' => '10+',
            'stat_akses' => '24/7',
            'stat_digitalisasi' => '100%',
            'footer_text' => '© '.date('Y').' Qinara Apps. Hak Cipta Dilindungi Undang-Undang.',
        ]);

        $testimonis = [
            [
                'nama' => 'K.H. Ahmad Dahlan',
                'jabatan' => 'Pimpinan Ponpes Al-Amanah',
                'isi' => "Semenjak menggunakan Qinara Apps, wali santri kami sangat terbantu memantau progres hafalan Al-Qur'an anak-anak. Transparansi keuangan Syahriah pun naik drastis, sehingga kepercayaan wali santri bertambah.",
                'rating' => 5, 'urutan' => 1,
            ],
            [
                'nama' => 'Ustadzah Maryam',
                'jabatan' => 'Kepala Tata Usaha Ponpes Darussalam',
                'isi' => 'Urusan administrasi PPDB dan Rapor yang biasanya memakan waktu berminggu-minggu kini bisa selesai dalam hitungan hari. Pekerjaan staf TU jadi jauh lebih ringan dan tertata.',
                'rating' => 5, 'urutan' => 2,
            ],
            [
                'nama' => 'Gus Zaki',
                'jabatan' => 'Dewan Pengasuh Ponpes Krapyak Mandiri',
                'isi' => 'Aplikasi ini sangat gampang dipahami, bahkan untuk asatidzah sepuh yang awalnya kaku dengan teknologi. Customer service-nya juga super responsif mendampingi pondok kami.',
                'rating' => 5, 'urutan' => 3,
            ],
        ];
        foreach ($testimonis as $t) {
            Testimoni::updateOrCreate(['nama' => $t['nama']], $t);
        }

        $pakets = [
            [
                'nama' => 'Starter', 'tagline' => 'Cocok untuk rintisan / madrasah diniyah', 'target_pasar' => 'Pesantren Kecil',
                'harga_bulanan' => 299000, 'diskon_tahunan_persen' => 15, 'is_recommended' => false,
                'fitur' => [
                    ['label' => 'Maksimal 150 Santri', 'included' => true],
                    ['label' => 'Administrasi Dasar', 'included' => true],
                    ['label' => 'Keuangan & Syahriah Pokok', 'included' => true],
                    ['label' => 'Monitoring Hafalan', 'included' => true],
                    ['label' => 'Wallet Non-Tunai', 'included' => false],
                ],
                'cta_text' => 'Coba Versi Starter', 'urutan' => 1,
            ],
            [
                'nama' => 'Professional', 'tagline' => 'Solusi komprehensif pesantren berkembang', 'target_pasar' => 'Paling Direkomendasikan',
                'harga_bulanan' => 499000, 'diskon_tahunan_persen' => 15, 'is_recommended' => true,
                'fitur' => [
                    ['label' => 'Maksimal 500 Santri', 'included' => true],
                    ['label' => 'Semua Fitur Utama Terbuka', 'included' => true],
                    ['label' => 'PPDB Online Premium', 'included' => true],
                    ['label' => 'Keuangan Otomatis & Tagihan WA', 'included' => true],
                    ['label' => 'Sistem Rapor & Absensi Digital', 'included' => true],
                    ['label' => 'Priority CS Support 24/7', 'included' => true],
                ],
                'cta_text' => 'Pilih Paket Terpopuler', 'urutan' => 2,
            ],
            [
                'nama' => 'Enterprise', 'tagline' => 'Untuk pondok dengan ribuan santri', 'target_pasar' => 'Pesantren Besar',
                'harga_bulanan' => 999000, 'diskon_tahunan_persen' => 15, 'is_recommended' => false,
                'fitur' => [
                    ['label' => 'Santri Tanpa Batas (Unlimited)', 'included' => true],
                    ['label' => 'Semua Fitur Pro & Custom Request', 'included' => true],
                    ['label' => 'Dedicated Database Server', 'included' => true],
                    ['label' => 'Integrasi API Custom', 'included' => true],
                    ['label' => 'Training Onsite / Kunjungan Langsung', 'included' => true],
                ],
                'cta_text' => 'Hubungi via WhatsApp', 'urutan' => 3,
            ],
        ];
        foreach ($pakets as $p) {
            PaketHarga::updateOrCreate(['nama' => $p['nama']], $p);
        }

        $faqs = [
            ['pertanyaan' => 'Apakah Qinara Apps sulit digunakan untuk pemula?', 'jawaban' => 'Sama sekali tidak. Qinara Apps dirancang khusus dengan antarmuka yang sangat ramah pengguna (user-friendly), menggunakan Bahasa Indonesia yang luas dan layout minimalis. Kami juga menyediakan tim bantuan teknis gratis untuk mendampingi masa awal proses adaptasi pondok Anda.', 'urutan' => 1],
            ['pertanyaan' => 'Apakah data rahasia santri dan keuangan kami dijamin aman?', 'jawaban' => 'Keamanan data Anda adalah prioritas mutlak kami. Seluruh lalu lintas data dienkripsi dengan standar enkripsi SSL 256-bit kelas atas, serta cadangan berkala harian secara otomatis guna menghindari kehilangan data akibat gangguan perangkat keras lokal Anda.', 'urutan' => 2],
            ['pertanyaan' => 'Apakah kami bisa mengajukan kustomisasi atau fitur tambahan tertentu?', 'jawaban' => 'Bisa sekali. Melalui paket Enterprise, tim pengembang internal Qinara Apps siap melayani diskusi kebutuhan khusus, modifikasi sistem, integrasi metode eksternal, atau kustomisasi alur bisnis khusus yang sesuai dengan keunikan kurikulum pondok Anda.', 'urutan' => 3],
            ['pertanyaan' => 'Apakah pihak pesantren harus menginstal aplikasi tertentu di komputer?', 'jawaban' => 'Tidak perlu. Qinara Apps adalah aplikasi berbasis awan (Cloud-based SaaS). Anda cukup mengaksesnya melalui peramban internet (browser) di laptop, PC, komputer, tablet, maupun ponsel pintar Anda kapan saja dan di mana saja.', 'urutan' => 4],
            ['pertanyaan' => 'Apakah Qinara Apps cocok untuk semua jenis dan ukuran pesantren?', 'jawaban' => 'Sangat cocok. Mulai dari madrasah rintisan berskala kecil hingga pondok modern berkapasitas ribuan santri dengan asrama kompleks, sistem kami sangat elastis untuk disesuaikan berkat pilihan paket Starter, Professional, dan Enterprise yang kami sediakan.', 'urutan' => 5],
            ['pertanyaan' => 'Bagaimana dengan proses migrasi data dari sistem lama atau file Excel?', 'jawaban' => 'Kami menyediakan fitur import data santri langsung melalui template format Excel yang mudah dipahami. Selain itu, tim Customer Success kami siap memandu dan membantu proses migrasi data dari sistem lama Anda ke basis data Qinara Apps secara gratis hingga tuntas.', 'urutan' => 6],
        ];
        foreach ($faqs as $f) {
            FaqItem::updateOrCreate(['pertanyaan' => $f['pertanyaan']], $f);
        }
    }
}
