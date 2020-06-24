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
    protected const PATTERN_ERASE = '#\[([a-z-]+=.+|[./][a-z-]+)\]#U';
    protected const PATTERN_FIX = '#[ ]+([ .:,;])#';

    /**
     * Processes the passed string.
     * @param string $locale The locale the translator is currently running on, e.g. "en".
     * @param string $string The string to process.
     * @param array<mixed> $parameters The additional parameters of the localised string.
     * @return string The processed string.
     */
    public function process(string $locale, string $string, array $parameters): string
    {
        $string = (string) preg_replace(self::PATTERN_ERASE, '', $string);
        $string = (string) preg_replace(self::PATTERN_FIX, '\\1', $string);
        return $string;
    }
}
