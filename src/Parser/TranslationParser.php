<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Common\Constant\Defaults;
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
    public function __construct(
        protected readonly Console $console,
        protected readonly ModFileService $modFileService,
        protected readonly Translator $translator,
    ) {
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
        mixed $localisedString,
        mixed $fallbackLocalisedString = null
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

        $this->filterDuplicates($translations);
    }

    protected function filterDuplicates(DictionaryInterface $translations): void
    {
        $defaultTranslation = $translations->get(Defaults::LOCALE);
        foreach ($translations as $locale => $translation) {
            if ($locale !== Defaults::LOCALE && $translation === $defaultTranslation) {
                $translations->set($locale, '');
            }
        }
    }
}
