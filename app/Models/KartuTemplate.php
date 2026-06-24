<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuTemplate extends Model
{
    protected $fillable = [
        'jenis',
        'background_depan',
        'background_belakang',
    ];
}
