<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasKode;
use App\Models\Kas;

class Pembayaran extends Model
{
    use HasKode;

    protected $fillable = [
        'tagihan_id',
        'siswa_id',
        'ppdb_id',
        'kode',
        'nominal',
        'metode',
        'status',
        'bukti_transfer',
        'tanggal_bayar',
        'reference',
        'payload',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'payload' => 'array',
    ];

    // =========================
    // RELASI
    // =========================
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class);
    }

    // =========================
    // BOOTED
    // =========================
    protected static function booted()
{
    /**
     * 🔢 AUTO GENERATE KODE
     */
    static::creating(function ($model) {
        if (!$model->kode) {
            $model->kode = self::generateKode('PAY', self::class);
        }
    });

    /**
     * 🚫 VALIDASI
     */
    static::saving(function ($pembayaran) {

        $tagihan = $pembayaran->tagihan;

        if (!$tagihan) return;

        // =========================
        // VALIDASI SISWA
        // =========================
        if ($tagihan->siswa_id && $pembayaran->siswa_id != $tagihan->siswa_id) {
            throw new \Exception('Siswa tidak sesuai dengan tagihan');
        }

        $isBulanan = $tagihan->jenisTagihan?->is_bulanan ?? false;

        $totalSebelumnya = $tagihan->pembayarans()
            ->where('status', 'sukses')
            ->where('id', '!=', $pembayaran->id)
            ->sum('nominal');

        $sisa = $tagihan->nominal - $totalSebelumnya;

        // 🔴 SPP (WAJIB LUNAS)
        if ($isBulanan) {

            $pembayaran->nominal = $sisa;

            if ($pembayaran->nominal != $sisa) {
                throw new \Exception(
                    'Tagihan ini harus dibayar lunas. Sisa: Rp ' . number_format($sisa, 0, ',', '.')
                );
            }

        } 
        // 🟢 CICILAN
        else {

            if ($pembayaran->nominal > $sisa) {
                throw new \Exception(
                    'Nominal melebihi sisa tagihan. Sisa: Rp ' . number_format($sisa, 0, ',', '.')
                );
            }

            if ($pembayaran->nominal <= 0) {
                throw new \Exception('Nominal harus lebih dari 0');
            }
        }
    });

    /**
     * 🧠 UPDATE TAGIHAN + AUTO KAS + AUTO PPDB
     */
    static::saved(function ($pembayaran) {

        $tagihan = $pembayaran->tagihan;

        if (!$tagihan) return;

        // =========================
        // UPDATE TAGIHAN
        // =========================
        $total = $tagihan->pembayarans()
            ->where('status', 'sukses')
            ->sum('nominal');

        $tagihan->nominal_terbayar = $total;

        if ($total == 0) {
            $tagihan->status = 'belum';
        } elseif ($total < $tagihan->nominal) {
            $tagihan->status = 'sebagian';
        } else {
            $tagihan->status = 'lunas';
        }

        $tagihan->save();

        // =========================
        // 🔥 AUTO UPDATE STATUS PPDB
        // =========================
        if ($tagihan->ppdb_id) {

            $ppdb = \App\Models\Ppdb::find($tagihan->ppdb_id);

            if ($ppdb && $tagihan->status === 'lunas') {

                $lembaga = $ppdb->lembaga;

                // 🟡 PENDAFTARAN
                if ($tagihan->jenisTagihan->nama === 'Pendaftaran') {

                    if ($lembaga && $lembaga->is_tes) {

                        $ppdb->update([
                            'status' => 'tes'
                        ]);

                    } else {

                        $ppdb->update([
                            'status' => 'lulus'
                        ]);

                        // 🔥 AUTO BUAT TAGIHAN DAFTAR ULANG
                        \App\Services\TagihanService::buatDaftarUlang($ppdb);
                    }
                }

                // 🟢 DAFTAR ULANG
                if ($tagihan->jenisTagihan->nama === 'Daftar Ulang') {

                    $ppdb->update([
                        'status' => 'daftar_ulang'
                    ]);
                }
            }
        }

        // =========================
        // HANDLE KAS
        // =========================

        if ($pembayaran->status !== 'sukses') {
            \App\Models\Kas::where('pembayaran_id', $pembayaran->id)->delete();
            return;
        }

        $exists = \App\Models\Kas::where('pembayaran_id', $pembayaran->id)->exists();

        if (!$exists) {

            $kategoriKasId = $tagihan->jenisTagihan->kategori_kas_id;

            if (!$kategoriKasId) {
                throw new \Exception(
                    'Kategori kas belum di-set di Jenis Tagihan: ' . $tagihan->jenisTagihan?->nama
                );
            }

            \App\Models\Kas::create([
                'tipe' => 'masuk',
                'kategori_id' => $kategoriKasId,
                'rekening_id' => $tagihan->rekening_id ?? null,
                'nominal' => $pembayaran->nominal,
                'pembayaran_id' => $pembayaran->id,
                'sumber' => 'pembayaran',
                'tanggal' => $pembayaran->tanggal_bayar ?? now(),
                'keterangan' => 'Pembayaran ' . $pembayaran->kode,
            ]);
        }
    });

    /**
     * 🧠 HANDLE DELETE
     */
    static::deleted(function ($pembayaran) {

        \App\Models\Kas::where('pembayaran_id', $pembayaran->id)->delete();

        $tagihan = $pembayaran->tagihan;

        if (!$tagihan) return;

        $total = $tagihan->pembayarans()
            ->where('status', 'sukses')
            ->sum('nominal');

        $tagihan->nominal_terbayar = $total;

        if ($total == 0) {
            $tagihan->status = 'belum';
        } elseif ($total < $tagihan->nominal) {
            $tagihan->status = 'sebagian';
        } else {
            $tagihan->status = 'lunas';
        }

        $tagihan->save();
    });
}
}