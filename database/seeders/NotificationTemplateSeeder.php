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
    }
}
