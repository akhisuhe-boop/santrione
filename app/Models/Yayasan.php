<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yayasan extends Model
{
    protected $fillable = ['nama', 'ketua', 'logo'];

    public function lembagas()
    {
        return $this->hasMany(Lembaga::class);
    }
}