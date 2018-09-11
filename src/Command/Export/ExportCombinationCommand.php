<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\CommandInterface;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportCombinationCommand implements CommandInterface
{
    /**
     * The combination registry.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

    /**
     * The Factorio instance.
     * @var Instance
     */
    protected $instance;

    /**
     * The parser manager.
     * @var ParserManager
     */
    protected $parserManager;

    /**
     * Initializes the command.
     * @param EntityRegistry $combinationRegistry
     * @param Instance $instance
     * @param ParserManager $parserManager
     */
    public function __construct(EntityRegistry $combinationRegistry, Instance $instance, ParserManager $parserManager)
    {
        $this->combinationRegistry = $combinationRegistry;
        $this->instance = $instance;
        $this->parserManager = $parserManager;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     * @throws ExportException
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $combinationHash = $route->getMatchedParam('combinationHash', '');
        $combination = $this->combinationRegistry->get($combinationHash);

        if ($combination instanceof Combination) {
            $console->writeLine('Exporting combination ' . $combination->getName() . '...');

            $dumpData = $this->instance->run($combination);
            $this->parserManager->parse($combination, $dumpData);
            $this->combinationRegistry->set($combination);

            $exitCode = 0;
        } else {
            $console->writeLine('Combination with hash #' . $combinationHash . ' not found.', ColorInterface::RED);
            $exitCode = 404;
        }
        return $exitCode;
    }
}
