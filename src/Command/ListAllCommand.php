<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for listing all available mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListAllCommand implements CommandInterface
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
        usort($mods, function(Mod $left, Mod $right): int {
            return $left->getOrder() <=> $right->getOrder();
        });

        foreach ($mods as $mod) {
            $console->write(str_pad($mod->getName() . ': ', 64, ' ', STR_PAD_LEFT));
            $console->write(str_pad($mod->getVersion(), 10, ' ', STR_PAD_RIGHT), ColorInterface::GREEN);
            $console->writeLine();
        }
    }
}