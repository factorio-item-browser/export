<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the instances.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InstanceFactory implements FactoryInterface
{
    /**
     * The next index to use.
     * @var int
     */
    static protected $nextIndex = 1;

    /**
     * Creates an instance.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return Instance
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $container->get(DumpExtractor::class);
        /* @var ParserManager $parserManager */
        $parserManager = $container->get(ParserManager::class);
        /* @var Options $options */
        $options = $container->get(Options::class);

        return new Instance(
            $exportDataService,
            $dumpExtractor,
            $parserManager,
            Console::getInstance(),
            $options,
            self::$nextIndex++
        );
    }
}