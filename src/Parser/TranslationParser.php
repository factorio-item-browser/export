<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;

/**
 * The parser of the translations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationParser implements ParserInterface
{
    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the parser.
     * @param ModFileManager $modFileManager
     * @param Translator $translator
     */
    public function __construct(ModFileManager $modFileManager, Translator $translator)
    {
        $this->modFileManager = $modFileManager;
        $this->translator = $translator;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     * @throws NoSupportedLoaderException
     */
    public function prepare(Dump $dump): void
    {
        $this->translator->loadMod($this->modFileManager->getLocalDirectory('core'));
        foreach ($dump->getModNames() as $modName) {
            $this->translator->loadMod($this->modFileManager->getLocalDirectory($modName));
        }
    }

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     */
    public function parse(Dump $dump, Combination $combination): void
    {
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }

    /**
     * Translates the localised string into all the languages.
     * @param LocalisedString $entity
     * @param mixed $translation
     * @param mixed|null $secondaryTranslation
     */
    public function translate(LocalisedString $entity, $translation, $secondaryTranslation = null): void
    {
        foreach ($this->translator->getAllLocales() as $locale) {
            $value = $this->translator->translate($locale, $translation);
            if ($value === '' && $secondaryTranslation !== null) {
                $value = $this->translator->translate($locale, $secondaryTranslation);
            }
            if ($value !== '') {
                $entity->addTranslation($locale, $value);
            }
        }
    }
}
