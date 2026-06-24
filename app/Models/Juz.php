<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juz extends Model
{
    protected $table = 'juzs'; // 🔥 WAJIB kalau mau aman

    protected $fillable = ['nama', 'total_ayat'];

    public function setorans()
    {
        return $this->hasMany(TahfidzSetoran::class);
    }
}
