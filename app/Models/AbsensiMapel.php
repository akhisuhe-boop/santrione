<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsensiMapel extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    use HasFactory;

    protected $fillable = [
        'jadwal_pelajaran_id',
        'siswa_id',
        'tanggal',
        'status',
        'keterangan',
        'jurnal_mengajar_id',
        'diabsen_oleh',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function diabsenOleh()
    {
        return $this->belongsTo(User::class, 'diabsen_oleh');
    }

    public function jurnalMengajar()
    {
        return $this->belongsTo(JurnalMengajar::class);
    }
}