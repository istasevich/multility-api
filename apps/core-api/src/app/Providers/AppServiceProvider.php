<?php

namespace App\Providers;

use app\Domain\Contract\DocumentGenerator;
use App\Domain\Contract\DocumentJobStatusRepository;

use App\Domain\Contract\RateProvider;
use App\Infrastructure\Document\PdfGenerator;
use App\Infrastructure\Persistence\Document\RedisDocumentJobStatusRepository;
use App\Infrastructure\Provider\Rates\CryptoRateProvider;
use Illuminate\Support\ServiceProvider;
use App\Application\Service\Markdown\CommonMarkConverter;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CommonMarkConverter::class, function (): CommonMarkConverter {
            return new CommonMarkConverter();
        });

        $this->app->bind(RateProvider::class, CryptoRateProvider::class);

        $this->app->singleton(DocumentJobStatusRepository::class, function (): RedisDocumentJobStatusRepository {
            return new RedisDocumentJobStatusRepository();
        });

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
