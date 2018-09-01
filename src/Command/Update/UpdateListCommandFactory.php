<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\Export\ModFile\ModFileReader;
use Interop\Container\ContainerInterface;

/**
 * The factory of the update list command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateListCommandFactory
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return UpdateListCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);
        /* @var ModFileReader $modFileReader */
        $modFileReader = $container->get(ModFileReader::class);
        /* @var RawExportDataService $exportDataService */
        $exportDataService = $container->get(RawExportDataService::class);

        return new UpdateListCommand(
            $modFileManager,
            $modFileReader,
            $exportDataService->getModRegistry()
        );
    }
}
