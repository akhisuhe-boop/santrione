<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaportKepribadian extends Model
{
    protected $table = 'raport_kepribadians';

    protected $fillable = [
        'raport_non_akademik_id',
        'aspek',
        'nilai',
        'grade',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function raport()
    {
        return $this->belongsTo(RaportNonAkademik::class);
    }
}