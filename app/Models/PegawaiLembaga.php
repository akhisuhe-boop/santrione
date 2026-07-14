<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lembaga;

class PegawaiLembaga extends Model
{
    use BelongsToTenant;

    protected $table = 'pegawai_lembaga';

    protected $fillable = [

        'pegawai_id',
        'lembaga_id',

        'jabatan',
        'status',

        'metode_penggajian',
        'nominal_tetap',
        'tarif_per_jp',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}