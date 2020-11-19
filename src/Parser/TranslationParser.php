<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Collection\Translations;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The parser of the translations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationParser implements ParserInterface
{
    protected ModFileManager $modFileManager;
    protected Translator $translator;

    public function __construct(ModFileManager $modFileManager, Translator $translator)
    {
        $this->modFileManager = $modFileManager;
        $this->translator = $translator;
    }

    /**
     * @param Dump $dump
     * @throws NoSupportedLoaderException
     */
    public function prepare(Dump $dump): void
    {
        $this->translator->loadMod($this->modFileManager->getLocalDirectory('core'));
        foreach ($dump->modNames as $modName) {
            $this->translator->loadMod($this->modFileManager->getLocalDirectory($modName));
        }
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
    }

    public function validate(ExportData $exportData): void
    {
    }

    /**
     * @param Translations $translations
     * @param mixed $localisedString
     * @param mixed|null $fallbackLocalisedString
     */
    public function translate(Translations $translations, $localisedString, $fallbackLocalisedString = null): void
    {
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
