<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the export all command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportAllCommandFactory
{
    /**
     * Creates the export all command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportAllCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);

        return new ExportAllCommand($exportDataService);
    }
}
