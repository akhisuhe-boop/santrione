<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'yayasan_id', 'nama_lembaga', 'nama_pic', 'email', 'no_hp', 'sumber',
        'status', 'prioritas', 'next_follow_up_at', 'alasan_batal',
    ];

    protected $casts = [
        'next_follow_up_at' => 'date',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'dihubungi' => 'Sudah Dihubungi',
        'follow_up' => 'Follow-up',
        'deal' => 'Deal / Berlangganan',
        'batal' => 'Batal / Tidak Lanjut',
    ];

    public const PRIORITAS = [
        'panas' => 'Panas',
        'hangat' => 'Hangat',
        'dingin' => 'Dingin',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function prioritasLabel(): string
    {
        return self::PRIORITAS[$this->prioritas] ?? $this->prioritas;
    }

    public function yayasan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }

    /**
     * Status urgensi follow-up berdasarkan next_follow_up_at, dipakai
     * untuk pewarnaan kolom di tabel (terlewat/hari-ini/mendatang).
     */
    public function followUpUrgency(): ?string
    {
        if (! $this->next_follow_up_at) {
            return null;
        }

        if ($this->next_follow_up_at->isPast() && ! $this->next_follow_up_at->isToday()) {
            return 'terlewat';
        }

        if ($this->next_follow_up_at->isToday()) {
            return 'hari_ini';
        }

        return 'mendatang';
    }

    public function scopePerluFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '<=', now()->toDateString())
            ->whereNotIn('status', ['deal', 'batal']);
    }
}
