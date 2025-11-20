<?php

namespace App\Jobs\Document;

use App\Application\UseCase\Document\GeneratePdfFromHtmlUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GeneratePdfFromHtmlJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $html,
        /** @var array<string, mixed> */
        public readonly array $options = [],
    ) {
        // Nothing
    }

    public function handle(GeneratePdfFromHtmlUseCase $useCase): void
    {
        $useCase->execute($this->html, $this->options);
    }
}
