<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahfidzTarget extends Model
{
    protected $fillable = ['siswa_id', 'target_juz'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}