<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
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
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the parser.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     * @throws ExportException
     */
    public function prepare(Dump $dump): void
    {
        $this->translator->loadFromModNames($dump->getModNames());
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
     * Translates the names into the specified localised string.
     * @param LocalisedString $names
     * @param mixed $translation
     * @param mixed $secondaryTranslation
     */
    public function translateNames(LocalisedString $names, $translation, $secondaryTranslation = null): void
    {
        $this->translator->addTranslationsToEntity($names, 'name', $translation, $secondaryTranslation);
    }

    /**
     * Translates the descriptions into the specified localised string.
     * @param LocalisedString $descriptions
     * @param mixed $translation
     * @param mixed $secondaryTranslation
     */
    public function translateDescriptions(
        LocalisedString $descriptions,
        $translation,
        $secondaryTranslation = null
    ): void {
        $this->translator->addTranslationsToEntity($descriptions, 'description', $translation, $secondaryTranslation);
    }

    /**
     * Translates the names of a mod into the specified localised string.
     * @param LocalisedString $names
     * @param string $modName
     */
    public function translateModNames(LocalisedString $names, string $modName): void
    {
        $this->translator->addTranslationsToEntity($names, 'mod-name', ['mod-name.' . $modName]);
    }

    /**
     * Translates the descriptions of a mod into the specified localised string.
     * @param LocalisedString $descriptions
     * @param string $modName
     */
    public function translateModDescriptions(LocalisedString $descriptions, string $modName): void
    {
        $this->translator->addTranslationsToEntity($descriptions, 'mod-description', ['mod-description.' . $modName]);
    }
}
