<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasKode;

class Kas extends Model
{
    use BelongsToTenant;

    use HasKode;

    protected $table = 'kas';

    protected $fillable = [
        'kode',
        'tipe',
        'kategori_id',
        'rekening_id',
        'nominal',
        'pembayaran_id',
        'sumber',
        'tanggal',
        'keterangan',
        'penanggung_jawab',
        'diinput_oleh',
        'bukti',
        'lembaga_id',
        'yayasan_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Scoping tenant sekarang LANGSUNG lewat kolom yayasan_id (bukan
     * lagi whereHas('lembaga', ...) bawaan trait) — supaya transaksi
     * level yayasan/pesantren (lembaga_id kosong) tetap kelihatan
     * oleh yayasan yang bikin, bukannya malah hilang tak kelihatan
     * siapapun.
     */
    protected static function applyTenantScope($builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected static function booted(): void
    {
        static::creating(function (self $kas) {
            if (empty($kas->yayasan_id)) {
                $kas->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });
    }

    // 🔗 relasi ke rekening
    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    // 🔗 relasi ke pembayaran
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    protected static function booted()
    {
        parent::booted();

        // =========================
        // AUTO GENERATE KODE
        // =========================
        static::creating(function ($model) {

            if (!$model->kode) {
                $prefix = $model->tipe === 'masuk' ? 'KM' : 'KK';
                $model->kode = self::generateKode($prefix, self::class);
            }
        });

        // =========================
        // 🚫 CEGAH SALDO MINUS
        // =========================
        static::saving(function ($kas) {

            // hanya berlaku untuk kas keluar
            if ($kas->tipe === 'keluar') {

                $rekening = $kas->rekening;

                if (!$rekening) return;

                $saldo = $rekening->saldo;

                // kalau edit → balikin nominal lama dulu
                if ($kas->exists) {
                    $oldNominal = $kas->getOriginal('nominal');
                    $saldo += $oldNominal;
                }

                if ($kas->nominal > $saldo) {
                    throw new \Exception(
                        'Saldo tidak cukup! Saldo saat ini: Rp ' . number_format($saldo, 0, ',', '.')
                    );
                }
            }

        });
    }

    // 🔗 relasi ke kategori kas
    public function kategori()
    {
        return $this->belongsTo(\App\Models\KategoriKas::class);
    }

    // 🔗 relasi ke lembaga    
    public function lembaga()
    {
        return $this->belongsTo(\App\Models\Lembaga::class);
    }

    public function payroll()
    {
        return $this->belongsTo(\App\Models\Payroll::class);
    }
}