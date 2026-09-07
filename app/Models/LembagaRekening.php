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
}
