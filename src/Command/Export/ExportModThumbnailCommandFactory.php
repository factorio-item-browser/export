<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory for the export mod thumbnail command class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModThumbnailCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportModThumbnailCommand
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): ExportModThumbnailCommand {
        /* @var ImagineInterface $imagine */
        $imagine = $container->get(ImagineInterface::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);

        return new ExportModThumbnailCommand(
            $rawExportDataService->getIconRegistry(),
            $imagine,
            $modFileManager,
            $rawExportDataService->getModRegistry()
        );
    }
}
