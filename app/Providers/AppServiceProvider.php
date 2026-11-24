<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // App UI uses Bootstrap 5; default Tailwind pagination SVGs look broken.
        Paginator::useBootstrapFive();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Business day closes on Pakistan Standard Time (UTC+5).
        $tz = config('app.timezone', 'Asia/Karachi') ?: 'Asia/Karachi';
        date_default_timezone_set($tz);
        Carbon::setLocale(config('app.locale', 'en'));

        try {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                // Align MySQL session clock with Pakistan so NOW()/CURDATE() match the app day.
                DB::statement("SET time_zone = '+05:00'");
            }
        } catch (\Throwable $e) {
            // Ignore if DB is unavailable during early boot/artisan packaging.
        }
    }
}
