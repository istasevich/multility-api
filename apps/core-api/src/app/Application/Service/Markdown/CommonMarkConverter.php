<?php

namespace app\Application\Service\Markdown;

use League\CommonMark\CommonMarkConverter as BaseCommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

final class CommonMarkConverter
{
    private BaseCommonMarkConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'allow',   // можно 'strip', если хочешь жёсткую санитацию
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());

        $this->converter = new BaseCommonMarkConverter([], $environment);
    }

    public function convert(string $markdown): string
    {
        return $this->converter
            ->convert($markdown)
            ->getContent();
    }
}
