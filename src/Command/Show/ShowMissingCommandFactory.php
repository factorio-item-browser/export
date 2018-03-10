<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Show;

use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the show missing command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ShowMissingCommandFactory
{
    /**
     * Creates the show missing command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ShowMissingCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);

        return new ShowMissingCommand($exportDataService);
    }
}