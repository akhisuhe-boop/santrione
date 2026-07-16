<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    use BelongsToTenant;

    // Scoping lewat payroll.pegawai.lembagas
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('payroll.pegawai.lembagas', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

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