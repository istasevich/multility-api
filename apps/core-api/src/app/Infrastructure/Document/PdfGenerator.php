<?php
declare(strict_types=1);

namespace App\Infrastructure\Document;

use App\Domain\Contract\DocumentGenerator;
use App\Domain\DTO\GeneratedDocumentDto;
use App\Domain\Exception\DocumentGenerationException;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

final class PdfGenerator implements DocumentGenerator
{
    public function __construct(
        protected string $disk = 'public'
    ) {
        // Nothing
    }

    /**
     * @param   string                $source  HTML, Markdown или URL
     * @param   array<string, mixed>  $options
     *
     * @throws DocumentGenerationException
     */
    public function generate(string $source, array $options = []): GeneratedDocumentDto
    {
        try {
            $html = $this->prepareHtml($source, $options);

            $tmpDir = storage_path('app/tmp');

            if (! File::exists($tmpDir)) {
                File::makeDirectory($tmpDir, 0755, true);
            }

            $tempFile = storage_path('app/tmp/pdf_'.Str::random(10).'.pdf');

            Browsershot::html($html)
                ->setNodeModulePath(base_path('node_modules'))
                ->setNodeBinary('/usr/bin/node')
                ->setChromePath('/usr/bin/chromium')
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-dev-shm-usage',
                ])
                ->format($options['format'] ?? 'A4')
                ->landscape(($options['orientation'] ?? 'portrait') === 'landscape')
                ->savePdf($tempFile);

            $filename = 'documents/'.Str::uuid().'.pdf';
            $readStream = fopen($tempFile, 'r');

            Storage::disk($this->disk)->writeStream($filename, $readStream);

            // закрываем и чистим
            fclose($readStream);
            File::delete($tempFile);

            $url = Storage::disk($this->disk)->url($filename);
            $size = Storage::disk($this->disk)->size($filename);

            return new GeneratedDocumentDto(
                path: $filename,
                url: $url,
                mime: $this->mime(),
                size: $size,
                provider: $this->name()
            );
        } catch (\Throwable $e) {
            throw DocumentGenerationException::fromThrowable($e);
        }
    }

    public function mime(): string
    {
        return 'application/pdf';
    }

    public function name(): string
    {
        return 'Browsershot';
    }

    // --- helpers ---
    protected function prepareHtml(string $source, array $options): string
    {
        if (str_starts_with($source, 'http')) {
            return file_get_contents($source);
        }

        if (!str_contains($source, '<html')) {
            $markdown = \Parsedown::instance()->text($source);
            return "<html><body>{$markdown}</body></html>";
        }

        return $source;
    }
}
