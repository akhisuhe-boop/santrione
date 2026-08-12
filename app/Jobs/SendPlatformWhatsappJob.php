<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * SENGAJA job terpisah dari SendWhatsappJob (bukan menambah
 * parameter/flag ke situ) -- supaya job existing yang sudah dipakai
 * fitur lain (absensi, tagihan SPP, dll) tidak perlu diubah sama
 * sekali. Job ini SELALU pakai WhatsappService::sendPlatform()
 * (kredensial Qinara sendiri), tidak pernah meminjam WhatsappSetting
 * Lembaga manapun.
 */
class SendPlatformWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;

    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle()
    {
        WhatsappService::sendPlatform($this->phone, $this->message);
    }
}
