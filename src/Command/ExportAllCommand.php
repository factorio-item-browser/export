<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command to export all of the available mods. May take some hours.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportAllCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * Initializes the command.
     * @param ExportDataService $exportDataService
     */
    public function __construct(ExportDataService $exportDataService)
    {
        $this->exportDataService = $exportDataService;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $mods = $this->exportDataService->getMods();
        usort($mods, function (Mod $left, Mod $right): int {
            return $left->getOrder() <=> $right->getOrder();
        });

        $console->writeLine(' > Number of mods to process: ' . count($mods));
        $index = 0;
        foreach ($mods as $mod) {
            $consoleWidth = $console->getWidth() - 2;
            $progress = (int) ceil($index / count($mods) * $consoleWidth);
            $console->writeLine('[' . str_pad(str_pad('', $progress, '#'), $consoleWidth, ' ') . ']');

            $command = $_SERVER['PHP_SELF'] . ' export mod "' . $mod->getName() . '"';
            system($command);

            ++$index;
        }
    }
}
