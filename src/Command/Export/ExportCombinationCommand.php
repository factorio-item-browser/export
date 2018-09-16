<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use ZF\Console\Route;

/**
 * The command for exporting a combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportCombinationCommand extends AbstractCommand
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
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $combinationHash = $route->getMatchedParam('combinationHash', '');
        $combination = $this->combinationRegistry->get($combinationHash);

        if ($combination instanceof Combination) {
            $this->console->writeLine('Exporting combination ' . $combination->getName() . '...');

            $dumpData = $this->instance->run($combination);
            $this->parserManager->parse($combination, $dumpData);
            $this->combinationRegistry->set($combination);
        } else {
            throw new CommandException('Combination with hash #' . $combinationHash . ' not found.', 404);
        }
    }
}
