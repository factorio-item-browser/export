<?php

namespace FactorioItemBrowser\Export\Command\Export;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the export mod command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportModCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var CombinationCreator $combinationCreator */
        $combinationCreator = $container->get(CombinationCreator::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ProcessManager $processManager */
        $processManager = $container->get(ProcessManager::class);

        return new ExportModCommand(
            $combinationCreator,
            $rawExportDataService->getCombinationRegistry(),
            $rawExportDataService->getModRegistry(),
            $processManager
        );
    }
}
