<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToTenant;

class JenisTagihan extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $table = 'jenis_tagihans';

    protected $fillable = [
        'yayasan_id',
        'nama',
        'kode',
        'default_nominal',
        'is_bulanan',
        'is_cicilan',
        'kategori_kas_id',
        'tipe_sistem',
    ];

    protected $casts = [
        'is_bulanan' => 'boolean',
        'is_cicilan' => 'boolean',
    ];

    // ======================
    // 🔥 AUTO LOGIC
    // ======================
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->yayasan_id)) {
                $model->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });

        static::saving(function ($model) {

            // 🔥 AUTO GENERATE KODE (jika kosong)
            if (!$model->kode && $model->nama) {
                $model->kode = strtolower(Str::slug($model->nama, '_'));
            }

            // 🔥 RULE: bulanan tidak boleh cicilan
            if ($model->is_bulanan) {
                $model->is_cicilan = false;
            }
        });
    }

    // ======================
    // 🔗 RELATION
    // ======================

    // ke tagihan
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    // ke kategori kas
    public function kategoriKas()
    {
        return $this->belongsTo(\App\Models\KategoriKas::class);
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    // ke setting nominal tagihan
    public function settingNominalTagihans()
    {
        return $this->hasMany(\App\Models\SettingNominalTagihan::class);
    }
}