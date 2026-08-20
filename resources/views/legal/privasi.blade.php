@extends('legal.layout')

@section('title', 'Kebijakan Privasi')

@section('content')
<p>
    {{ $setting->brand_name }} ("kami", "platform") berkomitmen melindungi privasi dan
    keamanan data setiap pihak yang menggunakan layanan kami — lembaga pendidikan
    (Yayasan/Lembaga), tenaga pendidik, siswa, hingga wali siswa. Kebijakan Privasi ini
    menjelaskan data apa saja yang kami kumpulkan, bagaimana data itu digunakan, dan
    hak Anda atas data tersebut, sesuai dengan Undang-Undang Nomor 27 Tahun 2022
    tentang Pelindungan Data Pribadi ("UU PDP").
</p>

<h2>1. Informasi yang Kami Kumpulkan</h2>
<p>Kami mengumpulkan beberapa kategori data, tergantung peran Anda:</p>
<ul>
    <li><strong>Data Lembaga</strong>: nama Yayasan/Lembaga, alamat, nomor kontak, dan dokumen administratif yang diinput saat pendaftaran.</li>
    <li><strong>Data Pengguna (Admin, Guru, Tata Usaha)</strong>: nama, email, nomor telepon, jabatan, dan kredensial akun.</li>
    <li><strong>Data Siswa</strong>: diinput oleh pihak lembaga ke dalam sistem untuk keperluan administrasi akademik — meliputi nama, data kehadiran, nilai, catatan tahfidz, catatan disiplin, dan data terkait lainnya. Data ini adalah milik dan tanggung jawab lembaga yang menginputnya; kami memprosesnya semata-mata sebagai penyedia layanan (data processor).</li>
    <li><strong>Data Wali Siswa</strong>: nama, nomor WhatsApp, dan riwayat transaksi pembayaran yang berkaitan dengan anak yang diwakilinya.</li>
    <li><strong>Data Transaksi & Pembayaran</strong>: riwayat tagihan, status pembayaran, dan metode pembayaran. Detail kartu/rekening diproses langsung oleh mitra payment gateway kami (lihat Bagian 4) — kami tidak menyimpan nomor kartu pembayaran secara langsung di server kami.</li>
    <li><strong>Data Teknis</strong>: alamat IP, jenis perangkat, log akses, dan aktivitas penggunaan sistem untuk keperluan keamanan dan pemeliharaan.</li>
</ul>

<h2>2. Data Anak dan Perlindungan Khusus</h2>
<p>
    Kami menyadari bahwa sebagian data yang diproses dalam sistem — yaitu data siswa —
    adalah data milik anak di bawah umur. Akses terhadap data siswa dibatasi hanya untuk
    pihak yang berwenang di lingkungan lembaga terkait (admin, guru sesuai kelasnya) dan
    wali siswa yang bersangkutan. Kami tidak menggunakan data siswa untuk tujuan
    pemasaran, profiling, atau dibagikan ke pihak ketiga di luar keperluan operasional
    layanan yang dijelaskan dalam kebijakan ini.
</p>

<h2>3. Bagaimana Kami Menggunakan Informasi</h2>
<ul>
    <li>Menyediakan dan mengoperasikan fitur-fitur platform (akademik, keuangan, absensi, dan modul lain yang diaktifkan lembaga).</li>
    <li>Memproses pembayaran dan mengirimkan notifikasi tagihan/pembayaran.</li>
    <li>Mengirim notifikasi operasional melalui WhatsApp (misalnya konfirmasi pendaftaran, tagihan, pengumuman) sesuai pengaturan yang diaktifkan lembaga.</li>
    <li>Menjaga keamanan sistem, mencegah penyalahgunaan, dan melakukan pemeliharaan teknis.</li>
    <li>Memenuhi kewajiban hukum yang berlaku.</li>
</ul>

<h2>4. Berbagi Data dengan Pihak Ketiga</h2>
<p>Kami hanya membagikan data dengan pihak ketiga yang diperlukan untuk operasional layanan, meliputi:</p>
<ul>
    <li><strong>Penyedia payment gateway</strong> (seperti DOKU atau Xendit) untuk memproses transaksi pembayaran.</li>
    <li><strong>Penyedia layanan pesan</strong> (WhatsApp Business API) untuk mengirim notifikasi yang diaktifkan lembaga.</li>
    <li><strong>Penyedia infrastruktur cloud</strong> untuk penyimpanan data dan berkas secara aman.</li>
</ul>
<p>
    Kami tidak menjual, menyewakan, atau memperdagangkan data pribadi pengguna kepada
    pihak ketiga mana pun untuk tujuan pemasaran.
</p>

<h2>5. Keamanan Data</h2>
<p>
    Kami menerapkan enkripsi SSL 256-bit untuk seluruh lalu lintas data, pencadangan
    (backup) data secara berkala, serta pembatasan akses bertingkat sesuai peran
    pengguna (multi-level access control). Meski demikian, tidak ada sistem yang
    sepenuhnya bebas risiko — kami terus melakukan pembaruan keamanan secara berkala.
</p>

<h2>6. Penyimpanan & Retensi Data</h2>
<p>
    Data disimpan selama akun/lembaga aktif menggunakan layanan kami. Jika lembaga
    mengakhiri langganan, data akan disimpan untuk jangka waktu wajar guna keperluan
    pemulihan atas permintaan, kemudian dihapus atau dianonimkan sesuai kebijakan
    retensi internal kami, kecuali diwajibkan lain oleh hukum yang berlaku.
</p>

<h2>7. Hak Anda atas Data Pribadi</h2>
<p>Sesuai UU PDP, Anda berhak untuk:</p>
<ul>
    <li>Mendapatkan informasi tentang data pribadi yang kami proses;</li>
    <li>Meminta koreksi atas data yang tidak akurat;</li>
    <li>Meminta penghapusan data pribadi, sepanjang tidak bertentangan dengan ketentuan hukum lain (misalnya kewajiban penyimpanan dokumen pendidikan);</li>
    <li>Menarik persetujuan pemrosesan data, dalam hal pemrosesan didasarkan pada persetujuan.</li>
</ul>
<p>
    Untuk data siswa, permintaan tersebut umumnya diajukan melalui lembaga tempat
    siswa terdaftar, karena lembagalah yang menjadi pengendali data (data controller)
    untuk data tersebut.
</p>

<h2>8. Cookie dan Teknologi Serupa</h2>
<p>
    Situs dan aplikasi kami dapat menggunakan cookie atau teknologi serupa untuk
    menjaga sesi login dan meningkatkan pengalaman penggunaan. Anda dapat mengatur
    preferensi cookie melalui pengaturan peramban Anda.
</p>

<h2>9. Perubahan Kebijakan Ini</h2>
<p>
    Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan
    signifikan akan diinformasikan melalui platform atau kontak resmi yang terdaftar.
</p>

<h2>10. Hubungi Kami</h2>
<p>
    Untuk pertanyaan seputar privasi atau permintaan terkait data pribadi Anda,
    silakan hubungi kami melalui:
</p>
<ul>
    @if($setting->email_kontak)
    <li>Email: <a href="mailto:{{ $setting->email_kontak }}">{{ $setting->email_kontak }}</a></li>
    @endif
    @if($setting->whatsapp_number)
    <li>WhatsApp: {{ $setting->whatsapp_number }}</li>
    @endif
    @if($setting->alamat)
    <li>Alamat: {{ $setting->alamat }}</li>
    @endif
</ul>
@endsection
