<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The abstract class of the parsers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractParser
{
    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * TInitializes the parser.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Parses the dump data into the combination.
     * @param CombinationData $combinationData
     * @param DataContainer $dumpData
     * @return $this
     */
    abstract public function parse(CombinationData $combinationData, DataContainer $dumpData);

    /**
     * Checks whether the child string duplicates the parent one.
     * @param LocalisedString $leftString
     * @param LocalisedString $rightString
     * @return bool
     */
    protected function areLocalisedStringsIdentical(LocalisedString $leftString, LocalisedString $rightString): bool
    {
        $result = true;
        foreach ($leftString->getTranslations() as $locale => $leftTranslation) {
            $rightTranslation = $rightString->getTranslation($locale);

            if (strlen($leftTranslation) > 0 && strlen($rightTranslation) > 0
                && $leftTranslation !== $rightTranslation
            ) {
                $result = false;
                break;
            }
        }
        return $result;
    }
}