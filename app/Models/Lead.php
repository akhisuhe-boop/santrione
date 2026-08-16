<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'yayasan_id', 'nama_lembaga', 'nama_pic', 'email', 'no_hp', 'sumber', 'status',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'dihubungi' => 'Sudah Dihubungi',
        'follow_up' => 'Follow-up',
        'deal' => 'Deal / Berlangganan',
        'batal' => 'Batal / Tidak Lanjut',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function yayasan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }
}
