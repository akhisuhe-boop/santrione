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

    /**
     * Cek apakah $yayasan termasuk penerima broadcast ini -- evaluasi
     * ULANG kriteria target_filter terhadap yayasan spesifik (bukan
     * simpan daftar penerima permanen), supaya tetap akurat kalau
     * kriteria awalnya "berdasarkan status" dan status yayasan itu
     * berubah setelahnya. Murni untuk keperluan TAMPILAN riwayat "info
     * yang pernah saya terima" di halaman Langganan Saya.
     */
    public function includesYayasan(Yayasan $yayasan): bool
    {
        $filter = $this->target_filter;

        return match ($filter['tipe'] ?? 'semua') {
            'status' => in_array($yayasan->status, $filter['status'] ?? [], true),
            'manual' => in_array($yayasan->id, $filter['yayasan_ids'] ?? [], true),
            default => true,
        };
    }
}
