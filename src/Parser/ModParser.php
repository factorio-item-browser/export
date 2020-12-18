<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;

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

    protected Console $console;
    protected HashCalculator $hashCalculator;
    protected ModFileService $modFileService;
    protected TranslationParser $translationParser;

    public function __construct(
        Console $console,
        HashCalculator $hashCalculator,
        ModFileService $modFileService,
        TranslationParser $translationParser
    ) {
        $this->console = $console;
        $this->hashCalculator = $hashCalculator;
        $this->modFileService = $modFileService;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
        foreach ($this->console->iterateWithProgressbar('Parsing mods', $dump->modNames) as $modName) {
            $mod = $this->createMod($modName);
            $thumbnail = $this->createThumbnail($mod);
            if ($thumbnail !== null) {
                $mod->thumbnailId = $thumbnail->id;
                $exportData->getIcons()->add($thumbnail);
            }
            $exportData->getMods()->add($mod);
        }
    }

    /**
     * @param string $modName
     * @return Mod
     * @throws ExportException
     */
    protected function createMod(string $modName): Mod
    {
        $info = $this->modFileService->getInfo($modName);

        $mod = new Mod();
        $mod->name = $modName;
        $mod->version = (string) $info->version;
        $mod->author = $info->author;

        $mod->titles->set('en', $info->title);
        $mod->descriptions->set('en', $info->description);

        $this->translationParser->translate($mod->titles, ["mod-name.${modName}"]);
        $this->translationParser->translate($mod->descriptions, ["mod-description.${modName}"]);

        return $mod;
    }

    protected function createThumbnail(Mod $mod): ?Icon
    {
        $thumbnailSize = $this->getThumbnailSize($mod);
        if ($thumbnailSize === 0) {
            return null;
        }

        $layer = new Layer();
        $layer->fileName = sprintf('__%s__/%s', $mod->name, self::THUMBNAIL_FILENAME);
        $layer->size = $thumbnailSize;

        $thumbnail = new Icon();
        $thumbnail->size = self::RENDERED_THUMBNAIL_SIZE;
        $thumbnail->layers[] = $layer;
        $thumbnail->id = $this->hashCalculator->hashIcon($thumbnail);
        return $thumbnail;
    }

    /**
     * @param Mod $mod
     * @return int The size of the thumbnail, or 0 if no thumbnail is available.
     */
    protected function getThumbnailSize(Mod $mod): int
    {
        try {
            $content = $this->modFileService->readFile($mod->name, self::THUMBNAIL_FILENAME);
        } catch (ExportException $e) {
            return 0;
        }

        $image = @imagecreatefromstring($content);
        if ($image === false) {
            return 0;
        }

        return imagesx($image);
    }

    public function validate(ExportData $exportData): void
    {
    }
}
