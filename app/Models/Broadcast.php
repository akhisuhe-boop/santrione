<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'judul', 'pesan', 'target_types', 'jadwal_kirim',
        'status', 'dikirim_pada', 'jumlah_terkirim', 'pesan_error',
    ];

    protected $casts = [
        'target_types' => 'array',
        'jadwal_kirim' => 'datetime',
        'dikirim_pada' => 'datetime',
    ];

    public const TARGET_TYPES = [
        'yayasan_semua' => 'Semua Yayasan',
        'yayasan_trial' => 'Yayasan (Status Trial)',
        'yayasan_aktif' => 'Yayasan (Langganan Aktif)',
        'lead_semua' => 'Semua Lead',
    ];

    public const STATUSES = [
        'terjadwal' => 'Terjadwal',
        'terkirim' => 'Terkirim',
        'gagal' => 'Gagal',
    ];

    public function targetLabels(): string
    {
        return collect($this->target_types ?? [])
            ->map(fn ($t) => self::TARGET_TYPES[$t] ?? $t)
            ->implode(', ');
    }
}
