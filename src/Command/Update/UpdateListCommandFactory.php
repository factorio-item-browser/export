<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModReader;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the update list command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateListCommandFactory implements FactoryInterface
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
        /* @var ModReader $modFileReader */
        $modFileReader = $container->get(ModReader::class);
        /* @var RawExportDataService $exportDataService */
        $exportDataService = $container->get(RawExportDataService::class);

        return new UpdateListCommand(
            $modFileManager,
            $modFileReader,
            $exportDataService->getModRegistry()
        );
    }
}
