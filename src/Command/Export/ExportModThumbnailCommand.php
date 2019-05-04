<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Constant\Config;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use ZF\Console\Route;

/**
 * The command for exporting the thumbnail of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModThumbnailCommand extends AbstractModCommand
{
    /**
     * The icon registry.
     * @var EntityRegistry
     */
    protected $iconRegistry;

    /**
     * The imagine instance.
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * ExportModThumbnailCommand constructor.
     * @param EntityRegistry $iconRegistry
     * @param ImagineInterface $imagine
     * @param ModFileManager $modFileManager
     * @param ModRegistry $modRegistry
     */
    public function __construct(
        EntityRegistry $iconRegistry,
        ImagineInterface $imagine,
        ModFileManager $modFileManager,
        ModRegistry $modRegistry
    ) {
        parent::__construct($modRegistry);

        $this->iconRegistry = $iconRegistry;
        $this->imagine = $imagine;
        $this->modFileManager = $modFileManager;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     */
    protected function processMod(Route $route, Mod $mod): void
    {
        $thumbnail = $this->getThumbnailImage($mod);
        if ($thumbnail !== null) {
            $icon = $this->createIconEntityFromThumbnail($mod, $thumbnail);
            $thumbnailHash = $this->iconRegistry->set($icon);
            $mod->setThumbnailHash($thumbnailHash);

            $this->persistMod($mod);
        }
    }

    /**
     * Checks whether the mod provides a thumbnail.
     * @param Mod $mod
     * @return ImageInterface|null
     */
    protected function getThumbnailImage(Mod $mod): ?ImageInterface
    {
        try {
            $imageContent = $this->modFileManager->getFile($mod, Config::THUMBNAIL_FILENAME);
            $result = $this->imagine->load($imageContent);
        } catch (ExportException $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * Creates the icon entity from the thumbnail.
     * @param Mod $mod
     * @param ImageInterface $thumbnail
     * @return Icon
     */
    protected function createIconEntityFromThumbnail(Mod $mod, ImageInterface $thumbnail): Icon
    {
        $layer = new Icon\Layer();
        $layer->setFileName(sprintf('__%s__/%s', $mod->getName(), Config::THUMBNAIL_FILENAME));

        $result = new Icon();
        $result->addLayer($layer)
               ->setSize($thumbnail->getSize()->getWidth())
               ->setRenderedSize(Config::THUMBNAIL_SIZE);

        return $result;
    }
}
