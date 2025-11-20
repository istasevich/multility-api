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

    public function execute(string $html, array $options = []): GeneratedDocumentDto
    {
        return $this->generator->generate($html, $options);
    }
}
