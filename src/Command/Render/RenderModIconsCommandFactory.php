<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the render mod icons command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderModIconsCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RenderModIconsCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);

        return new RenderModIconsCommand(
            $reducedExportDataService->getCombinationRegistry(),
            $rawExportDataService->getModRegistry()
        );
    }
}
