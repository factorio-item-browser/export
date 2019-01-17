<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use Interop\Container\ContainerInterface;

/**
 * The factory of the render icon command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconCommandFactory
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RenderIconCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);
        /* @var IconRenderer $iconRenderer */
        $iconRenderer = $container->get(IconRenderer::class);

        return new RenderIconCommand(
            $rawExportDataService->getIconRegistry(),
            $reducedExportDataService->getRenderedIconRegistry(),
            $iconRenderer
        );
    }
}
