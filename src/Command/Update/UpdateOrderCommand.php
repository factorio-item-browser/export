<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for updating the absolute order of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateOrderCommand extends AbstractCommand
{
    /**
     * The dependency resolver.
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param DependencyResolver $dependencyResolver
     * @param ModRegistry $modRegistry
     */
    public function __construct(DependencyResolver $dependencyResolver, ModRegistry $modRegistry)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     */
    protected function execute(Route $route): void
    {
        $this->console->writeLine('Updating order...');

        $orderedModNames = $this->getOrderedModNames();
        $this->assignModOrder($orderedModNames);
    }

    /**
     * Returns the ordered mod names.
     * @return array|string[]
     */
    protected function getOrderedModNames(): array
    {
        $modNames = $this->modRegistry->getAllNames();
        return $this->dependencyResolver->resolveMandatoryDependencies($modNames);
    }

    /**
     * Assigns the order to the mods.
     * @param array|string[] $orderedModNames
     */
    protected function assignModOrder(array $orderedModNames): void
    {
        $order = 1;
        foreach ($orderedModNames as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $mod->setOrder($order);
                ++$order;
                $this->modRegistry->set($mod);
            }
        }
        $this->modRegistry->saveMods();
    }
}
