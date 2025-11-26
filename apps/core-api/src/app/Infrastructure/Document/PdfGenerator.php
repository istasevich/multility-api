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
     * Генерация PDF из HTML (уже готового!)
     */
    public function generate(string $source, array $options = []): GeneratedDocumentDto
    {
        try {
            return $this->generateInternal(fn (string $tempFile) =>
            $this->buildBrowsershotForHtml($source, $options)->savePdf($tempFile)
            );
        } catch (\Throwable $e) {
            throw DocumentGenerationException::fromThrowable($e);
        }
    }

    /**
     * Генерация PDF по URL — реальный рендер сайта браузером.
     */
    public function generateFromUrl(string $url, array $options = []): GeneratedDocumentDto
    {
        try {
            return $this->generateInternal(fn (string $tempFile) =>
            $this->buildBrowsershotForUrl($url, $options)->savePdf($tempFile)
            );
        } catch (\Throwable $e) {
            throw DocumentGenerationException::fromThrowable($e);
        }
    }

    // --- общая часть сохранения ---
    protected function generateInternal(callable $producer): GeneratedDocumentDto
    {
        $tmpDir = storage_path('app/tmp');

        if (! File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $tempFile = $tmpDir . '/pdf_' . Str::random(12) . '.pdf';

        $producer($tempFile);

        $filename = 'documents/' . Str::uuid() . '.pdf';
        $readStream = fopen($tempFile, 'r');

        Storage::disk($this->disk)->writeStream($filename, $readStream);

        fclose($readStream);
        File::delete($tempFile);

        return new GeneratedDocumentDto(
            path: $filename,
            url: Storage::disk($this->disk)->url($filename),
            mime: 'application/pdf',
            size: Storage::disk($this->disk)->size($filename),
            provider: 'Browsershot',
        );
    }

    // --- Browsershot фабрики ---
    protected function buildBrowsershotForHtml(string $html, array $options): Browsershot
    {
        return $this->configureBrowsershot(
            Browsershot::html($html),
            $options
        );
    }

    protected function buildBrowsershotForUrl(string $url, array $options): Browsershot
    {
        return $this->configureBrowsershot(
            Browsershot::url($url),
            $options
        );
    }

    protected function configureBrowsershot(Browsershot $browsershot, array $options): Browsershot
    {
        return $browsershot
            ->setNodeModulePath(base_path('node_modules'))
            ->setNodeBinary('/usr/bin/node')
            ->setChromePath('/usr/bin/chromium')
            ->addChromiumArguments([
                'no-sandbox',
                'disable-gpu',
                'disable-dev-shm-usage',
                'disable-setuid-sandbox',
                'disable-software-rasterizer',
            ])
            ->format($options['format'] ?? 'A4')
            ->landscape(($options['orientation'] ?? 'portrait') === 'landscape');
    }

    public function mime(): string
    {
        return 'application/pdf';
    }

    public function name(): string
    {
        return 'Browsershot';
    }
}
