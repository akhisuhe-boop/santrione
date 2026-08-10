<?php

namespace App\Models;

use App\Models\Yayasan;
use Illuminate\Database\Eloquent\Model;

class PlatformBroadcast extends Model
{
    protected $fillable = [
        'judul',
        'pesan',
        'target_filter',
        'jumlah_penerima',
        'jumlah_berhasil',
        'status',
        'dikirim_oleh',
        'dikirim_pada',
    ];

    protected function casts(): array
    {
        return [
            'target_filter' => 'array',
            'dikirim_pada' => 'datetime',
        ];
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    /**
     * Resolve target_filter jadi Collection<Yayasan> sungguhan --
     * dipanggil SEKALI saat tombol kirim ditekan (bukan disimpan
     * sebagai daftar ID tetap), supaya selalu akurat terhadap kondisi
     * Yayasan saat itu.
     */
    public function resolveTargetYayasan()
    {
        $filter = $this->target_filter;

        return match ($filter['tipe'] ?? 'semua') {
            'status' => Yayasan::withoutGlobalScopes()
                ->whereIn('status', $filter['status'] ?? [])
                ->get(),

            'manual' => Yayasan::withoutGlobalScopes()
                ->whereIn('id', $filter['yayasan_ids'] ?? [])
                ->get(),

            default => Yayasan::withoutGlobalScopes()->get(),
        };
    }
}
