<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Yayasan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

use App\Http\Responses\LogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

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
        // SHARE DATA YAYASAN
        if (Schema::hasTable('yayasans')) {
            View::share('yayasan', Yayasan::first());
        }

        // PAGINATION TAILWIND
        Paginator::useTailwind();
    }
}