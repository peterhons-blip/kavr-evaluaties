<?php

namespace App\Providers;

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
        // Links in e-mails moeten ook achter Apache/FastCGI altijd
        // het publieke HTTPS-adres gebruiken (nooit http of localhost).
        $publicUrl = (string) config('app.url');
        if (app()->isProduction() && str_starts_with($publicUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(rtrim($publicUrl, '/'));
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Support\PlatformMailConfig::apply();
    }
}
