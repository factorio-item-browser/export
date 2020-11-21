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
    protected const RENDERED_THUMBNAIL_SIZE = 144;
    protected const THUMBNAIL_FILENAME = 'thumbnail.png';

    protected HashCalculator $hashCalculator;
    protected ModFileManager $modFileManager;
    protected TranslationParser $translationParser;

    public function __construct(
        HashCalculator $hashCalculator,
        ModFileManager $modFileManager,
        TranslationParser $translationParser
    ) {
        $this->hashCalculator = $hashCalculator;
        $this->modFileManager = $modFileManager;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, Combination $combination): void
    {
        foreach ($dump->modNames as $modName) {
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
     * @param Mod $mod
     * @return int The size of the thumbnail, or 0 if no thumbnail is available.
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

    public function validate(Combination $combination): void
    {
    }
}
