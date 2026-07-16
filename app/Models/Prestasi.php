<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Prestasi extends Model
{
    use BelongsToTenant;

    protected $fillable = [
    'lembaga_id',
    'nama',
    'point',
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
