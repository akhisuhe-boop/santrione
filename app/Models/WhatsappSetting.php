<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'lembaga_id',
        'provider',
        'api_url',
        'token',
        'sender',
        'no_admin_absensi',
        'is_active'
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
