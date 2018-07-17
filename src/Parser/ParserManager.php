<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The manager of the parser classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParserManager
{
    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * The parsers to use.
     * @var array|AbstractParser[]
     */
    protected $parsers;

    /**
     * Initializes the parser manager.
     * @param Translator $translator
     * @param array|AbstractParser[] $parsers
     */
    public function __construct(Translator $translator, array $parsers)
    {
        $this->translator = $translator;
        $this->parsers = $parsers;
    }

    /**
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(Combination $combination, DataContainer $dumpData)
    {
        $this->translator->setEnabledModNames($combination->getLoadedModNames());
        foreach ($this->parsers as $parser) {
            $parser->parse($combination->getData(), $dumpData);
        }
        return $this;
    }
}
