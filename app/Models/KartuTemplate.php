<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class KartuTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'lembaga_id',
        'jenis',
        'background_depan',
        'background_belakang',
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
