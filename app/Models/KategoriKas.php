<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToTenant;

class KategoriKas extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $table = 'kategori_kas';

    protected $fillable = [
        'yayasan_id',
        'nama',
        'tipe',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->yayasan_id)) {
                $model->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }

            if (empty($model->kode)) {
                $model->kode = self::generateKode($model->nama, $model->tipe, $model->yayasan_id);
            }
        });
    }

    protected static function generateKode($nama, $tipe, $yayasanId = null)
    {
        $slug = Str::slug($nama, '_');

        $kode = $tipe . '_' . $slug;

        $original = $kode;
        $i = 1;

        // Cek duplikat kode HANYA dalam yayasan yang sama, bukan seluruh
        // sistem — supaya yayasan lain tetap boleh pakai kode yang sama.
        while (
            self::withoutGlobalScopes()
                ->where('yayasan_id', $yayasanId)
                ->where('kode', $kode)
                ->exists()
        ) {
            $kode = $original . '_' . $i++;
        }

        return $kode;
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }
}
