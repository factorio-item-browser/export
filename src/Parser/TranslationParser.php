<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Collection\DictionaryInterface;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The parser of the translations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationParser implements ParserInterface
{
    protected Console $console;
    protected ModFileService $modFileService;
    protected Translator $translator;

    public function __construct(Console $console, ModFileService $modFileService, Translator $translator)
    {
        $this->console = $console;
        $this->modFileService = $modFileService;
        $this->translator = $translator;
    }

    /**
     * @param Dump $dump
     * @throws NoSupportedLoaderException
     */
    public function prepare(Dump $dump): void
    {
        $this->translator->loadMod($this->modFileService->getLocalDirectory('core'));
        foreach ($this->console->iterateWithProgressbar('Preparing translations', $dump->modNames) as $modName) {
            $this->translator->loadMod($this->modFileService->getLocalDirectory($modName));
        }
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
    }

    public function validate(ExportData $exportData): void
    {
    }

    /**
     * @param DictionaryInterface $translations
     * @param mixed $localisedString
     * @param mixed|null $fallbackLocalisedString
     */
    public function translate(
        DictionaryInterface $translations,
        $localisedString,
        $fallbackLocalisedString = null
    ): void {
        foreach ($this->translator->getAllLocales() as $locale) {
            $value = $this->translator->translate($locale, $localisedString);
            if ($value === '' && $fallbackLocalisedString !== null) {
                $value = $this->translator->translate($locale, $fallbackLocalisedString);
            }
            if ($value !== '') {
                $translations->set($locale, $value);
            }
        }
    }
}
