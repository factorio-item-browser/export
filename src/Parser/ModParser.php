<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Mod;

/**
 * The parser of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModParser implements ParserInterface
{
    /**
     * The size of the rendered thumbnails.
     */
    protected const RENDERED_THUMBNAIL_SIZE = 144;

    /**
     * The filename of the thumbnail.
     */
    protected const THUMBNAIL_FILENAME = 'thumbnail.png';

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The translation parser.
     * @var TranslationParser
     */
    protected $translationParser;

    /**
     * Initializes the parser.
     * @param ModFileManager $modFileManager
     * @param TranslationParser $translationParser
     */
    public function __construct(ModFileManager $modFileManager, TranslationParser $translationParser)
    {
        $this->modFileManager = $modFileManager;
        $this->translationParser = $translationParser;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     */
    public function prepare(Dump $dump): void
    {
    }

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     */
    public function parse(Dump $dump, Combination $combination): void
    {
        foreach ($dump->getModNames() as $modName) {
            $combination->addMod($this->createEntity($modName));
        }
    }

    /**
     * Creates the mod entity of the specified name.
     * @param string $modName
     * @return Mod
     */
    protected function createEntity(string $modName): Mod
    {
        $mod = new Mod();
        $mod->setName($modName)
            ->setVersion($this->modFileManager->getVersion($modName));

        // @todo Read English translation from info.json file.
        $this->translationParser->translateModNames($mod->getTitles(), $modName);
        $this->translationParser->translateModDescriptions($mod->getDescriptions(), $modName);

        return $mod;
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }
}
