<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the merger manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MergerManagerFactory implements FactoryInterface
{
    /**
     * The merger classes to use.
     */
    const MERGER_CLASSES = [
        ItemMerger::class,
        RecipeMerger::class,
        IconMerger::class,
    ];

    /**
     * Creates the merger manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MergerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);

        $mergers = [];
        foreach (self::MERGER_CLASSES as $parserClass) {
            $mergers[] = $container->get($parserClass);
        }

        return new MergerManager($exportDataService, $mergers);
    }
}