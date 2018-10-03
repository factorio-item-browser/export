<?php

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\DumpInfoGenerator;
use ZF\Console\Route;

/**
 * The command for preparing exports.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportPrepareCommand extends AbstractCommand
{
    /**
     * The dump info generator.
     * @var DumpInfoGenerator
     */
    protected $dumpInfoGenerator;

    /**
     * Initializes the command.
     * @param DumpInfoGenerator $dumpInfoGenerator
     */
    public function __construct(DumpInfoGenerator $dumpInfoGenerator)
    {
        $this->dumpInfoGenerator = $dumpInfoGenerator;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $this->console->writeLine('Generating info.json for the dump mod...');
        $this->dumpInfoGenerator->generate();
    }
}
