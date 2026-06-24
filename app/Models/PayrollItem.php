<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [

        'payroll_id',
        'pegawai_lembaga_id',

        'nama_komponen',
        'jenis',

        'qty',
        'tarif',
        'subtotal',

        'keterangan',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function pegawaiLembaga()
    {
        return $this->belongsTo(PegawaiLembaga::class);
    }
}