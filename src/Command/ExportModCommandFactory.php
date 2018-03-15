<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Factorio\FactorioManager;
use FactorioItemBrowser\Export\Mod\CombinationCreator;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the export mod command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommandFactory
{
    /**
     * Creates the export mod command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportModCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        /* @var CombinationCreator $combinationCreator */
        $combinationCreator = $container->get(CombinationCreator::class);
        /* @var FactorioManager $factorioManager */
        $factorioManager = $container->get(FactorioManager::class);

        return new ExportModCommand($exportDataService, $combinationCreator, $factorioManager);
    }
}