<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        if (app()->environment('production') || env('VERCEL')) {
            URL::forceScheme('https');

            @mkdir('/tmp/storage/framework/views', 0777, true);
            @mkdir('/tmp/storage/framework/cache', 0777, true);
        }

        $this->runVercelMigrations();
    }

    private function runVercelMigrations(): void
    {
        static $migrated = false;

        if ($migrated || !env('VERCEL') || !env('VERCEL_AUTO_MIGRATE') || app()->runningInConsole()) {
            return;
        }

        $migrated = true;

        try {
            Artisan::call('migrate', [
                '--seed' => true,
                '--force' => true,
            ]);
        } catch (Throwable $exception) {
            Log::error('Vercel auto migration failed: ' . $exception->getMessage());

            throw $exception;
        }
    }
}
