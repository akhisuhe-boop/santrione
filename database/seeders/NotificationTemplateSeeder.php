<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        NotificationTemplate::updateOrCreate(
            ['key' => 'tagihan_subscription'],
            [
                'nama' => 'Tagihan Langganan Terbit',
                'template' =>
                    "*TAGIHAN LANGGANAN QINARA APPS*\n\n" .
                    "Yth. {nama_yayasan},\n\n" .
                    "Tagihan langganan periode *{periode}* telah terbit.\n\n" .
                    "Total Tagihan : *{total_tagihan}*\n\n" .
                    "Silakan lakukan pembayaran melalui link berikut:\n" .
                    "{link_pembayaran}\n\n" .
                    "Terima kasih telah menggunakan Qinara Apps.",
                'keterangan_placeholder' => '{nama_yayasan}, {periode} (contoh: Agustus 2026), {total_tagihan} (sudah format Rp), {link_pembayaran}',
            ]
        );

        NotificationTemplate::updateOrCreate(
            ['key' => 'trial_reminder'],
            [
                'nama' => 'Reminder Trial Akan Berakhir',
                'template' =>
                    "*MASA TRIAL QINARA APPS AKAN BERAKHIR*\n\n" .
                    "Yth. {nama_yayasan},\n\n" .
                    "Masa coba gratis Anda tinggal *{sisa_hari} hari lagi* (berakhir {tanggal_berakhir}).\n\n" .
                    "Segera pilih modul yang mau dilanjutkan dan lakukan pembayaran di menu \"Langganan\" supaya akses tidak terputus.\n\n" .
                    "Terima kasih telah mencoba Qinara Apps.",
                'keterangan_placeholder' => '{nama_yayasan}, {sisa_hari} (angka, contoh: 7), {tanggal_berakhir} (format tanggal Indonesia)',
            ]
        );

        NotificationTemplate::updateOrCreate(
            ['key' => 'pendaftaran_berhasil'],
            [
                'nama' => 'Pendaftaran Berhasil (Selamat Datang)',
                'template' =>
                    "*SELAMAT DATANG DI QINARA APPS!*\n\n" .
                    "Yth. {nama_admin},\n\n" .
                    "Pendaftaran *{nama_yayasan}* berhasil. Masa coba gratis *14 hari* sudah aktif sampai *{tanggal_trial_berakhir}*.\n\n" .
                    "Berikut info login Anda:\n" .
                    "Email  : {email}\n" .
                    "Password : {password}\n\n" .
                    "Login di: {link_login}\n\n" .
                    "Selamat mencoba semua fitur Qinara Apps!",
                'keterangan_placeholder' => '{nama_admin}, {nama_yayasan}, {tanggal_trial_berakhir}, {email}, {password} (plaintext, cuma sekali di pesan ini), {link_login}',
            ]
        );

        NotificationTemplate::updateOrCreate(
            ['key' => 'aplikasi_aktif'],
            [
                'nama' => 'Langganan/Aplikasi Aktif (Setelah Bayar)',
                'template' =>
                    "*LANGGANAN QINARA APPS AKTIF*\n\n" .
                    "Yth. {nama_yayasan},\n\n" .
                    "Terima kasih! Pembayaran Anda berhasil dikonfirmasi dan langganan sekarang *AKTIF PENUH*.\n\n" .
                    "Semua modul yang Anda pilih dapat langsung digunakan.\n\n" .
                    "Terima kasih telah mempercayai Qinara Apps.",
                'keterangan_placeholder' => '{nama_yayasan}',
            ]
        );
    }
}
