<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;
    public $lembagaId;

    public function __construct($phone, $message, $lembagaId = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->lembagaId = $lembagaId;
    }

    public function handle()
    {
        WhatsappService::send($this->phone, $this->message, $this->lembagaId);
    }
}