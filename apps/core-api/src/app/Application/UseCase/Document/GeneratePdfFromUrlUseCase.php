<?php

namespace App\Application\UseCase\Document;

use App\Domain\DTO\GeneratedDocumentDto;
use App\Infrastructure\Document\PdfGenerator;

final class GeneratePdfFromUrlUseCase
{
    public function __construct(
        protected PdfGenerator $generator,
    ) {
        // Nothing
    }

    /**
     * @param array<string, mixed> $params
     */
    public function execute(string $url, array $params = []): GeneratedDocumentDto
    {
        $options = [];

        if (! empty($params['format'])) {
            $options['format'] = $params['format']; // 'A4', 'A3', 'Letter'
        }

        if (! empty($params['orientation'])) {
            $options['orientation'] = $params['orientation']; // 'portrait' | 'landscape'
        }

        // сюда позже добавим margins, header/footer, scale, page_ranges, background

        return $this->generator->generateFromUrl($url, $options);
    }
}
