<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
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

    protected static function booted(): void
    {
        // User baru yang dibuat lewat panel Filament (mis. resource
        // Pengguna) wajib otomatis terikat ke Yayasan (tenant) yang
        // sedang aktif saat itu. Tanpa ini, yayasan_id akan kosong
        // dan canAccessPanel() akan SELALU menolak login user
        // tersebut walau password/email sudah benar — karena user
        // non-platform-admin wajib punya yayasan_id untuk bisa masuk.
        static::creating(function (self $model) {
            if (empty($model->yayasan_id)) {
                $model->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    /**
     * Allow user to access Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Platform admin selalu boleh masuk (perlu buat support & monitoring
        // lintas yayasan, termasuk yayasan yang lagi di-suspend).
        if ($this->is_platform_admin) {
            return true;
        }

        // User biasa WAJIB terhubung ke sebuah yayasan untuk bisa masuk.
        if (empty($this->yayasan_id)) {
            return false;
        }

        // Yayasan yang masa trial-nya habis / langganannya tidak aktif
        // (belum bayar lewat masa tenggang) dikunci aksesnya — data
        // TIDAK dihapus, cuma tidak bisa login sampai berlangganan lagi.
        return (bool) $this->yayasan?->hasAccess();
    }

    /**
     * Daftar yayasan (tenant) yang boleh diakses user ini.
     * Platform admin bisa akses semua yayasan (untuk keperluan support);
     * user biasa hanya yayasan miliknya sendiri.
     */
    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->is_platform_admin) {
            return Yayasan::withoutGlobalScopes()->get();
        }

        return Yayasan::withoutGlobalScopes()
            ->where('id', $this->yayasan_id)
            ->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->is_platform_admin) {
            return true;
        }

        return $tenant->getKey() === $this->yayasan_id;
    }
}