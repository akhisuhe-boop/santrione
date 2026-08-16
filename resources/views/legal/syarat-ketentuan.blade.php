@extends('legal.layout')

@section('title', 'Syarat & Ketentuan')

@section('content')
<p>
    Selamat datang di {{ $setting->brand_name }}. Dengan mendaftar dan menggunakan
    layanan kami, Anda (bertindak untuk dan atas nama Yayasan/Lembaga Anda) menyetujui
    Syarat & Ketentuan berikut. Mohon dibaca dengan saksama sebelum menggunakan layanan.
</p>

<h2>1. Definisi Layanan</h2>
<p>
    {{ $setting->brand_name }} adalah platform Software-as-a-Service (SaaS) berbasis
    awan yang menyediakan sistem manajemen dan digitalisasi untuk lembaga pendidikan
    Islam, mencakup modul administrasi, akademik, keuangan, absensi, dan modul lain
    yang tersedia sesuai paket langganan yang dipilih.
</p>

<h2>2. Pendaftaran Akun & Masa Uji Coba</h2>
<ul>
    <li>Pendaftaran akun mewajibkan Anda memberikan data yang benar dan akurat mengenai lembaga dan penanggung jawab akun.</li>
    <li>Akun baru mendapatkan masa uji coba gratis (trial) selama periode yang ditampilkan pada saat pendaftaran. Setelah masa uji coba berakhir, kelanjutan akses mengikuti paket langganan yang dipilih.</li>
    <li>Anda bertanggung jawab menjaga kerahasiaan kredensial akun (username dan password) dan segala aktivitas yang terjadi melalui akun Anda.</li>
</ul>

<h2>3. Langganan & Pembayaran</h2>
<ul>
    <li>Biaya langganan dihitung berdasarkan paket dasar (Akses Platform/Paket Full) ditambah modul tambahan yang diaktifkan, sesuai skema harga yang berlaku dan dapat dilihat pada halaman Harga.</li>
    <li>Pembayaran dilakukan di muka untuk periode berjalan (bulanan), melalui metode pembayaran yang tersedia pada platform.</li>
    <li>Keterlambatan pembayaran dapat mengakibatkan pembatasan akses ke fitur berbayar hingga pembayaran diselesaikan. Kami akan berupaya memberikan notifikasi sebelum pembatasan dilakukan.</li>
    <li>Kebijakan pengembalian dana (refund), jika ada, mengikuti ketentuan yang berlaku pada saat transaksi dan akan diinformasikan secara terpisah oleh tim kami.</li>
</ul>

<h2>4. Kepemilikan Data</h2>
<p>
    Seluruh data yang diinput oleh lembaga ke dalam platform — termasuk data siswa,
    data keuangan, dan dokumen terkait — tetap menjadi milik lembaga yang bersangkutan.
    Kami bertindak sebagai penyedia layanan pengolahan data (data processor) dan tidak
    mengklaim kepemilikan atas data tersebut. Lembaga dapat meminta ekspor data sesuai
    mekanisme yang tersedia pada platform.
</p>

<h2>5. Penggunaan yang Dilarang</h2>
<p>Anda setuju untuk tidak:</p>
<ul>
    <li>Menggunakan layanan untuk tujuan yang melanggar hukum yang berlaku di Indonesia;</li>
    <li>Mencoba mengakses sistem, data, atau akun pihak lain tanpa izin;</li>
    <li>Mengganggu atau membebani infrastruktur layanan secara tidak wajar (termasuk namun tidak terbatas pada upaya peretasan atau scraping data);</li>
    <li>Menyalahgunakan fitur notifikasi (WhatsApp/email) untuk mengirim pesan yang bersifat spam atau di luar tujuan operasional lembaga.</li>
</ul>

<h2>6. Ketersediaan Layanan</h2>
<p>
    Kami berupaya menjaga ketersediaan layanan pada tingkat yang tinggi, namun tidak
    menjamin layanan akan selalu bebas dari gangguan, kesalahan, atau downtime, baik
    yang direncanakan (pemeliharaan) maupun tidak. Kami akan berupaya menginformasikan
    pemeliharaan terjadwal sebelumnya bila memungkinkan.
</p>

<h2>7. Batasan Tanggung Jawab</h2>
<p>
    Sepanjang diizinkan oleh hukum yang berlaku, {{ $setting->brand_name }} tidak
    bertanggung jawab atas kerugian tidak langsung, kehilangan keuntungan, atau
    kerugian konsekuensial lain yang timbul dari penggunaan atau ketidakmampuan
    menggunakan layanan, termasuk namun tidak terbatas pada kesalahan input data oleh
    pengguna lembaga itu sendiri.
</p>

<h2>8. Penghentian Layanan</h2>
<p>
    Anda dapat menghentikan langganan kapan saja sesuai mekanisme yang tersedia pada
    platform. Kami berhak menangguhkan atau menghentikan akun yang terbukti melanggar
    Syarat & Ketentuan ini, dengan pemberitahuan terlebih dahulu apabila memungkinkan.
</p>

<h2>9. Perubahan Layanan & Ketentuan</h2>
<p>
    Kami dapat memperbarui fitur layanan maupun Syarat & Ketentuan ini dari waktu ke
    waktu. Perubahan signifikan akan diinformasikan melalui platform atau kontak resmi
    yang terdaftar. Penggunaan layanan yang berkelanjutan setelah perubahan berlaku
    dianggap sebagai persetujuan atas perubahan tersebut.
</p>

<h2>10. Hukum yang Berlaku</h2>
<p>
    Syarat & Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum Negara Republik
    Indonesia. Setiap perselisihan yang timbul akan diselesaikan secara musyawarah
    terlebih dahulu sebelum menempuh jalur hukum yang berlaku.
</p>

<h2>11. Hubungi Kami</h2>
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
