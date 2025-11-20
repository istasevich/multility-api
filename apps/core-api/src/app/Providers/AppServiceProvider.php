<?php

namespace App\Providers;

use app\Domain\Contract\DocumentGenerator;
use App\Domain\Contract\RateProvider;
use App\Infrastructure\Document\PdfGenerator;
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

        $this->app->bind(DocumentGenerator::class, function (): DocumentGenerator {
            return new PdfGenerator(
                disk: 'public',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
