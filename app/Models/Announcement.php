<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'lembaga_id',
        'title',
        'content',
        'target_type',
        'target_role',
        'kelas_id',
        'send_whatsapp',
        'is_pinned',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'send_whatsapp' => 'boolean',
        'is_pinned'     => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE
    |--------------------------------------------------------------------------
    |
    | target_type :
    | - all
    | - role
    | - kelas
    |
    */

    public function scopeVisibleFor(
        Builder $query,
        string $role,
        ?int $kelasId = null
    ): Builder {

        return $query
            ->where(function ($q) use ($role, $kelasId) {

                /*
                |------------------------------------
                | Semua Portal
                |------------------------------------
                */
                $q->where('target_type', 'all');

                /*
                |------------------------------------
                | Berdasarkan Role
                |------------------------------------
                */
                $q->orWhere(function ($qq) use ($role) {

                    $qq->where('target_type', 'role')
                       ->where('target_role', $role);

                });

                /*
                |------------------------------------
                | Berdasarkan Kelas
                |------------------------------------
                */
                if ($kelasId) {

                    $q->orWhere(function ($qq) use ($kelasId) {

                        $qq->where('target_type', 'kelas')
                           ->where('kelas_id', $kelasId);

                    });

                }

            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}