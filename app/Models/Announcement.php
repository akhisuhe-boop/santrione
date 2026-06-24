<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
    'title',
    'content',
    'target_type',
    'target_role',
    'kelas_id',
    'send_whatsapp',
    'is_pinned',
    'attachment',
    'created_by',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}