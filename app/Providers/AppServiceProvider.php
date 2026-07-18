<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Yayasan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

use App\Http\Responses\LogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LogoutResponseContract::class,
            LogoutResponse::class,
        );
    }

    public function boot(): void
    {
        if (Schema::hasTable('yayasans')) {
            // PENTING: pakai View::composer, BUKAN View::share.
            // View::share dieksekusi saat service provider boot -- SEBELUM
            // middleware StartSession jalan, jadi session() SELALU kosong
            // di titik itu. View::composer dieksekusi saat view benar-benar
            // di-render (setelah middleware jalan), jadi timing-nya benar.
            View::composer(
                [
                    'auth.role-login',
                    'wali.*',
                    'guru.*',
                    'ppdb.*',
                    'kwitansi.*',
                    'slip-gaji.*',
                ],
                function ($view) {
                    // Kalau controller sudah pass $yayasan sendiri secara
                    // eksplisit, jangan ditimpa -- itu sumber yang paling akurat.
                    if (! array_key_exists('yayasan', $view->getData())) {
                        $view->with('yayasan', $this->resolvePublicYayasan());
                    }
                }
            );
        }

        // PAGINATION TAILWIND
        Paginator::useTailwind();
    }

    /**
     * Resolusi yayasan untuk konteks di luar Filament panel.
     *
     * Prioritas:
     * 1. Session portal publik (diisi lewat /y/{slug}) -- untuk Wali,
     *    Guru, PPDB, dan halaman role-gateway.
     * 2. User yang sedang login (kwitansi/slip-gaji dicetak dari
     *    dalam panel admin oleh staff yayasan).
     */
    protected function resolvePublicYayasan(): ?Yayasan
    {
        $sessionId = session('active_public_yayasan_id');

        if ($sessionId) {
            return Yayasan::withoutGlobalScopes()->find($sessionId);
        }

        $user = auth()->user();

        if ($user) {
            if ($user->is_platform_admin) {
                $tenant = Filament::getTenant();

                if ($tenant instanceof Yayasan) {
                    return $tenant;
                }
            }

            if (! empty($user->yayasan_id)) {
                return Yayasan::withoutGlobalScopes()->find($user->yayasan_id);
            }
        }

        return null;
    }
}
