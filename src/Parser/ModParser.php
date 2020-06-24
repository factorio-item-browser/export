<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
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
     * The hash calculator.
     * @var HashCalculator
     */
    protected $hashCalculator;

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
     * @param HashCalculator $hashCalculator
     * @param ModFileManager $modFileManager
     * @param TranslationParser $translationParser
     */
    public function __construct(
        HashCalculator $hashCalculator,
        ModFileManager $modFileManager,
        TranslationParser $translationParser
    ) {
        $this->hashCalculator = $hashCalculator;
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
                $mod->setThumbnailId($thumbnail->getId());
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

        $this->translationParser->translate($mod->getTitles(), ["mod-name.${modName}"]);
        $this->translationParser->translate($mod->getDescriptions(), ["mod-description.${modName}"]);

        return $mod;
    }

    /**
     * Maps the thumbnail of the mod to an entity, if there is one to map.
     * @param Mod $mod
     * @return Icon|null
     */
    protected function mapThumbnail(Mod $mod): ?Icon
    {
        $thumbnailSize = $this->getThumbnailSize($mod);
        if ($thumbnailSize === 0) {
            return null;
        }

        $layer = new Layer();
        $layer->setFileName(sprintf('__%s__/%s', $mod->getName(), self::THUMBNAIL_FILENAME))
              ->setSize($thumbnailSize);

        $thumbnail = new Icon();
        $thumbnail->setSize(self::RENDERED_THUMBNAIL_SIZE)
                  ->addLayer($layer);

        $thumbnail->setId($this->hashCalculator->hashIcon($thumbnail));
        return $thumbnail;
    }

    /**
     * Returns the size of the thumbnail, or 0 if no thumbnail is available.
     * @param Mod $mod
     * @return int
     */
    protected function getThumbnailSize(Mod $mod): int
    {
        try {
            $content = $this->modFileManager->readFile($mod->getName(), self::THUMBNAIL_FILENAME);
        } catch (ExportException $e) {
            return 0;
        }

        $image = @imagecreatefromstring($content);
        if ($image === false) {
            return 0;
        }

        return imagesx($image);
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }
}
