<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Translator\Processor;

use BluePsyduck\FactorioTranslator\Processor\Placeholder\AbstractControlPlaceholderProcessor;
use BluePsyduck\FactorioTranslator\TranslatorAwareInterface;
use BluePsyduck\FactorioTranslator\TranslatorAwareTrait;

/**
 * The implementation of the control placeholder processor.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ControlPlaceholderProcessor extends AbstractControlPlaceholderProcessor implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    /**
     * Processes the control placeholder.
     * @param string $locale The locale the translator is currently running on, e.g. "en".
     * @param string $controlName The name of the control, e.g. "build".
     * @param int $version The alternative version of the placeholder in case of __ALT_CONTROL__ syntax. 0 if the
     * placeholder was the default __CONTROL__ one.
     * @return string|null The replacement for the placeholder, or null to keep the placeholder as-is.
     */
    protected function processControl(string $locale, string $controlName, int $version): ?string
    {
        $control = $this->translator->translateWithFallback($locale, ["controls.{$controlName}"]);
        return "[{$control}]";
    }
}
