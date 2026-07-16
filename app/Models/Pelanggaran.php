<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Pelanggaran extends Model
{
    use BelongsToTenant;

    protected $fillable = [
    'lembaga_id',
    'nama',
    'kategori',
    'point',
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
