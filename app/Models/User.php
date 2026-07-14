<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'yayasan_id',
        'is_platform_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    /**
     * Allow user to access Filament panel.
     *
     * TODO Fase 5: tambahkan cek status subscription yayasan di sini
     * (redirect/block kalau status suspended karena belum bayar).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Platform admin selalu boleh masuk.
        if ($this->is_platform_admin) {
            return true;
        }

        // User biasa WAJIB terhubung ke sebuah yayasan untuk bisa masuk.
        return ! empty($this->yayasan_id);
    }
}