<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    protected $fillable = [
        'provider',
        'api_url',
        'token',
        'sender',
        'no_admin_absensi',
        'is_active'
    ];
}
