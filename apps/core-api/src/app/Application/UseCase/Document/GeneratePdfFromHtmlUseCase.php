<?php

declare(strict_types=1);

namespace App\Application\UseCase\Document;

use App\Application\Service\Markdown\CommonMarkConverter;
use App\Domain\Contract\DocumentGenerator;

final class GeneratePdfFromHtmlUseCase
{
    public function __construct(
        protected DocumentGenerator $generator,
        protected CommonMarkConverter $markdown,
    ) {
        // Nothing
    }

    /**
     * @param array<string,mixed> $options
     */
    public function execute(string $source, array $options = [])
    {
        $options = [];

        if (! empty($params['format'])) {
            $options['format'] = $params['format']; // 'A4', 'A3', 'Letter'
        }

        // Если Markdown (есть #, **, *, - и нет <html)
        $isMarkdown = $options['is_markdown']
            ?? (! str_contains($source, '<') && preg_match('/[#\*\-\`\>]/', $source));

        if ($isMarkdown) {
            $source = $this->markdown->convert($source);
        }

        return $this->generator->generate($source, $options);
    }
}
