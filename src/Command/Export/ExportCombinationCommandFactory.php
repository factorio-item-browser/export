<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the export combination command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportCombinationCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportCombinationCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var Instance $instance */
        $instance = $container->get(Instance::class);
        /* @var ParserManager $parserManager */
        $parserManager = $container->get(ParserManager::class);

        return new ExportCombinationCommand(
            $rawExportDataService->getCombinationRegistry(),
            $instance,
            $parserManager
        );
    }
}
