<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [

        'pegawai_id',
        'bulan',
        'tahun',

        'subtotal',
        'bonus',
        'potongan',
        'total_gaji',

        'status',
        'tanggal_bayar',
        'catatan',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function kas()
    {
        return $this->hasOne(\App\Models\Kas::class);
    }
}