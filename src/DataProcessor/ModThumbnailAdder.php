<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data processor adding the thumbnails of the mod to the export data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModThumbnailAdder implements DataProcessorInterface
{
    private const RENDERED_THUMBNAIL_SIZE = 144;
    private const THUMBNAIL_FILENAME = 'thumbnail.png';

    public function __construct(
        private readonly Console $console,
        private readonly ModFileService $modFileService,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        foreach ($this->console->iterateWithProgressbar('Add mod thumbnails', $exportData->getMods()) as $mod) {
            /* @var Mod $mod */
            $thumbnail = $this->createThumbnail($mod);
            if ($thumbnail !== null) {
                $exportData->getIcons()->add($thumbnail);
            }
        }
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

        $icon = new Icon();
        $icon->type = 'mod';
        $icon->name = $mod->name;
        $icon->size = self::RENDERED_THUMBNAIL_SIZE;
        $icon->layers[] = $layer;
        return $icon;
    }

    /**
     * @param Mod $mod
     * @return int The size of the thumbnail, or 0 if no thumbnail is available.
     */
    protected function getThumbnailSize(Mod $mod): int
    {
        try {
            $content = $this->modFileService->readFile($mod->name, self::THUMBNAIL_FILENAME);
        } catch (ExportException) {
            return 0;
        }

        $image = @imagecreatefromstring($content);
        if ($image === false) {
            return 0;
        }

        return imagesx($image);
    }
}
