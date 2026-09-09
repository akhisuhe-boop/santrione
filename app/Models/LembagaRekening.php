<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembagaRekening extends Model
{
    protected $fillable = [
        'lembaga_id',
        'kategori',
        'nama',
        'rekening_id',
        'doku_sub_account_id',
        'doku_account_no',
        'doku_split_rule_id',
        'doku_split_rule_id_flat',
        'doku_status',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    /**
     * DITAMBAHKAN -- rekening bank ASLI (tabel `rekenings`) tujuan
     * pencairan untuk kategori DOKU ini. Murni referensi untuk job
     * Disbursement nanti; TIDAK dipakai sama sekali oleh alur
     * pembuatan VA/QRIS atau split rule yang sudah jalan.
     */
    public function rekeningTujuan(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }
}
