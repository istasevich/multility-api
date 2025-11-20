<?php
declare(strict_types=1);

namespace App\Application\UseCase\Document;

use App\Domain\Contract\DocumentGenerator;
use App\Domain\DTO\GeneratedDocumentDto;
use App\Infrastructure\Document\PdfGenerator;

final class GeneratePdfFromHtmlUseCase
{
    public function __construct(
        protected PdfGenerator $generator
    ) {
        // Nothing
    }

    public function execute(string $source, array $params = []): GeneratedDocumentDto
    {
        // Маппим HTTP-параметры в options для генератора
        $options = [];

        if (! empty($params['format'])) {
            $options['format'] = $params['format']; // 'A4', 'A3', 'Letter'
        }


        if (! empty($params['orientation'])) {
            // просто пробрасываем строку, без логики
            $options['orientation'] = $params['orientation']; // 'portrait' | 'landscape'
        }


        // сюда позже добавим margins, header/footer, и т.д.
        return $this->generator->generate($source, $options);
    }
}
