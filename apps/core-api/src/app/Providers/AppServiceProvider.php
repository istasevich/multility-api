<?php

namespace App\Providers;

use App\Domain\Contract\RateProvider;
use App\Infrastructure\Provider\Rates\CryptoRateProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RateProvider::class, CryptoRateProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
