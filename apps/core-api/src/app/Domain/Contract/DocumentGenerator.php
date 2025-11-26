<?php

namespace App\Domain\Contract;


use App\Domain\DTO\GeneratedDocumentDto;
use App\Domain\Exception\DocumentGenerationException;

/**
 * Контракт для генераторов документов (PDF, изображений и т.д.)
 *
 * Идея:
 *  - единый интерфейс для разных генераторов (PDF, Screenshot, Markdown → PDF)
 *  - входные данные — строка или URL
 *  - выход — бинарный контент (PDF, PNG и т.п.)
 */
interface DocumentGenerator
{
    /**
     * Генерирует документ по HTML, URL или Markdown.
     *
     * @param   string                $source   HTML, Markdown или ссылка
     * @param   array<string, mixed>  $options  дополнительные параметры генерации (размер, стиль, ориентация)
     *
     * @return GeneratedDocumentDto бинарный контент (PDF или изображение)
     *
     * @throws DocumentGenerationException
     */
    public function generate(string $source, array $options = []): GeneratedDocumentDto;

    /**
     * Возвращает MIME-тип результата (например, application/pdf, image/png).
     */
    public function mime(): string;

    /**
     * Возвращает человекочитаемое имя провайдера (например, Browsershot, wkhtmltopdf).
     */
    public function name(): string;
}
