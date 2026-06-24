<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'payroll_id',
        'tipe',
        'nama_komponen',
        'qty',
        'nominal',
        'subtotal',
        'catatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO HITUNG SUBTOTAL
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->subtotal =
                ($model->qty ?? 1)
                *
                ($model->nominal ?? 0);
        });
    }
}