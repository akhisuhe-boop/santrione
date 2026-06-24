<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    protected $fillable = ['nama', 'jumlah_ayat', 'urutan'];
    public function setorans()
    {
        return $this->hasMany(TahfidzSetoran::class);
    }
}
