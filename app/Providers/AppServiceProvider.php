<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // The UI is built with Bootstrap 5, so use its pagination markup.
        Paginator::useBootstrapFive();

        // Demo aid: when DB_LOG_QUERIES=true, every SQL statement the app sends
        // to Oracle is written to storage/logs/laravel.log with its bound values
        // and run time. Lets you SHOW the exact queries Eloquent generates.
        if (env('DB_LOG_QUERIES', false)) {
            DB::listen(function ($query) {
                Log::channel('single')->info('SQL', [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms'  => $query->time,
                ]);
            });
        }
    }
}
