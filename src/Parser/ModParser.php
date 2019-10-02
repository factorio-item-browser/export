<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashingHelper;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
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
     * The hashing helper.
     * @var HashingHelper
     */
    protected $hashingHelper;

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
     * @param HashingHelper $hashingHelper
     * @param ModFileManager $modFileManager
     * @param TranslationParser $translationParser
     */
    public function __construct(
        HashingHelper $hashingHelper,
        ModFileManager $modFileManager,
        TranslationParser $translationParser
    ) {
        $this->hashingHelper = $hashingHelper;
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
     * @throws ExportException
     */
    public function parse(Dump $dump, Combination $combination): void
    {
        foreach ($dump->getModNames() as $modName) {
            $mod = $this->mapMod($modName);
            $thumbnail = $this->mapThumbnail($mod);
            if ($thumbnail !== null) {
                $mod->setThumbnailHash($thumbnail->getHash());
                $combination->addIcon($thumbnail);
            }
            $combination->addMod($mod);
        }
    }

    /**
     * Maps the mod entity of the specified name.
     * @param string $modName
     * @return Mod
     * @throws ExportException
     */
    protected function mapMod(string $modName): Mod
    {
        $info = $this->modFileManager->getInfo($modName);

        $mod = new Mod();
        $mod->setName($modName)
            ->setVersion($info->getVersion())
            ->setAuthor($info->getAuthor());

        $mod->getTitles()->addTranslation('en', $info->getTitle());
        $mod->getDescriptions()->addTranslation('en', $info->getDescription());

        $this->translationParser->translateModNames($mod->getTitles(), $modName);
        $this->translationParser->translateModDescriptions($mod->getDescriptions(), $modName);

        return $mod;
    }

    /**
     * Maps the thumbnail of the mod to an entity, if there is one to map.
     * @param Mod $mod
     * @return Icon|null
     */
    protected function mapThumbnail(Mod $mod): ?Icon
    {
        try {
            $this->modFileManager->readFile($mod->getName(), self::THUMBNAIL_FILENAME);
        } catch (ExportException $e) {
            return null;
        }

        $layer = new Layer();
        $layer->setFileName(sprintf('__%s__/%s', $mod->getName(), self::THUMBNAIL_FILENAME));

        $thumbnail = new Icon();
        $thumbnail->setSize(self::RENDERED_THUMBNAIL_SIZE)
                  ->setRenderedSize(self::RENDERED_THUMBNAIL_SIZE)
                  ->addLayer($layer);

        $thumbnail->setHash($this->hashingHelper->hashIcon($thumbnail));
        return $thumbnail;
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }
}
