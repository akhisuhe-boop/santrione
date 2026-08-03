<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class IzinHarian extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where(function (Builder $q) use ($yayasanId) {
            $q->whereHas('siswa.lembaga', fn ($sub) => $sub->where('yayasan_id', $yayasanId))
              ->orWhereHas('pegawai.lembagas', fn ($sub) => $sub->where('yayasan_id', $yayasanId));
        });
    }

    protected $table = 'izin_harians';

    protected $fillable = [
        'siswa_id',
        'pegawai_id',
        'tipe',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'lampiran',
        'status',
        'diajukan_oleh',
        'diproses_oleh',
        'catatan_admin',
        'diproses_pada',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diproses_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function diajukanOleh()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /**
     * Terapkan status Izin/Sakit ke absensi_harians untuk setiap tanggal
     * dalam rentang izin ini. Dipanggil saat izin di-approve.
     * Tidak menimpa hari yang sudah ada jam_masuk (sudah scan = Hadir/Terlambat).
     */
    public function terapkanKeAbsensi(): void
    {
        $periode = \Carbon\CarbonPeriod::create($this->tanggal_mulai, $this->tanggal_selesai);

        foreach ($periode as $tanggal) {

            $absen = AbsensiHarian::firstOrNew([
                'tanggal' => $tanggal->format('Y-m-d'),
                'siswa_id' => $this->siswa_id,
                'pegawai_id' => $this->pegawai_id,
            ]);

            // Jangan timpa kalau hari itu sudah ada catatan masuk (sudah scan)
            if ($absen->exists && $absen->jam_masuk) {
                continue;
            }

            $absen->tipe = $this->tipe;
            $absen->status_masuk = $this->jenis; // 'Izin' atau 'Sakit'
            $absen->metode_masuk = 'Pengajuan Izin';
            $absen->save();
        }
    }
}
