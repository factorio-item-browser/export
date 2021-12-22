<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Translator\Processor;

use BluePsyduck\FactorioTranslator\Processor\ProcessorInterface;

/**
 * The processor erasing all rich text tags from the strings. We don't want you. Go away.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RichTextEraser implements ProcessorInterface
{
    private const PATTERNS = [
        '#\[([a-z-]+=.+|[./][a-z-]+)\]#U' => '', // Erase rich-text tags
        '#[ ]+([ .:,;])#' => '\\1', // Fix additional spaces
    ];

    /**
     * Processes the passed string.
     * @param string $locale The locale the translator is currently running on, e.g. "en".
     * @param string $string The string to process.
     * @param array<mixed> $parameters The additional parameters of the localised string.
     * @return string The processed string.
     */
    public function process(string $locale, string $string, array $parameters): string
    {
        foreach (self::PATTERNS as $pattern => $replacement) {
            $string = (string) preg_replace($pattern, $replacement, $string);
        }
        return $string;
    }
}
