<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class creating the combinations to be exported for a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationCreator
{
    /**
     * The dependency resolver.
     *
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * The mod to create combinations for.
     * @var Mod
     */
    protected $mod;

    /**
     * The names of the mandatory mods.
     * @var array|string[]
     */
    protected $mandatoryModNames = [];

    /**
     * The names of the optional mods.
     * @var array|string[]
     */
    protected $optionalModNames = [];

    /**
     * Initializes the combination creator.
     * @param DependencyResolver $dependencyResolver
     */
    public function __construct(DependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Sets up the dependency resolver for the specified mod.
     * @param Mod $mod
     * @return $this
     */
    public function setupForMod(Mod $mod)
    {
        $this->mod = $mod;
        $this->mandatoryModNames = $this->dependencyResolver->resolveMandatoryDependencies([$mod->getName()]);
        $this->optionalModNames = $this->dependencyResolver->resolveOptionalDependencies(
            [$mod->getName()],
            $this->mandatoryModNames
        );

        return $this;
    }

    /**
     * Creates the main combination without any optional mods loaded.
     * @return Combination
     * @throws ExportException
     */
    public function createMainCombination(): Combination
    {
        if ($this->mod === null) {
            throw new ExportException('Unable to create combination without a mod.');
        }

        return $this->createCombination([]);
    }

    /**
     * Creates a new combination with the specified optional mods.
     * @param array|string[] $optionalModNames
     * @return Combination
     */
    protected function createCombination(array $optionalModNames): Combination
    {
        $combination = new Combination();
        $combination->setName(implode('-', array_merge([$this->mod->getName()], $optionalModNames)))
                    ->setMainModName($this->mod->getName())
                    ->setLoadedModNames(array_merge($this->mandatoryModNames, $optionalModNames))
                    ->setLoadedOptionalModNames($optionalModNames);

        return $combination;
    }
}
