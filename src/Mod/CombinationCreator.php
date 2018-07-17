<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\Entity\ExportCombination;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class creating the combinations to a mod to be exported.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationCreator
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The dependency resolver.
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * The parent combination finder.
     * @var ParentCombinationFinder
     */
    protected $parentCombinationFinder;

    /**
     * The mod for which the combinations are created.
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
     * The created combinations.
     * @var array|ExportCombination[]
     */
    protected $combinations = [];

    /**
     * Initializes the combination creator.
     * @param ExportDataService $exportDataService
     * @param DependencyResolver $dependencyResolver
     * @param ParentCombinationFinder $parentCombinationFinder
     */
    public function __construct(
        ExportDataService $exportDataService,
        DependencyResolver $dependencyResolver,
        ParentCombinationFinder $parentCombinationFinder
    ) {
        $this->exportDataService = $exportDataService;
        $this->dependencyResolver = $dependencyResolver;
        $this->parentCombinationFinder = $parentCombinationFinder;
    }

    /**
     * Creates the combinations of the specified mod.
     * @param Mod $mod
     * @return array|ExportCombination[]
     */
    public function createCombinations(Mod $mod): array
    {
        $this->mod = $mod;
        $this->mandatoryModNames = $this->dependencyResolver->resolveMandatoryDependencies([$mod->getName()]);
        $this->optionalModNames = $this->dependencyResolver->resolveOptionalMods($this->mandatoryModNames);

        $this->combinations = [
            $this->createCombinationEntity([])
        ];
        $this->createCombinationsWithOptionalMods(1);

        return $this->combinations;
    }

    /**
     * Creates the combination with the specified names of optional mods.
     * @param array|string[] $optionalModNames
     * @return ExportCombination
     */
    protected function createCombinationEntity(array $optionalModNames): ExportCombination
    {
        $name = implode('-', array_merge([$this->mod->getName()], $optionalModNames));

        $combination = new ExportCombination();
        $combination->setName($name)
                    ->setMainModName($this->mod->getName())
                    ->setLoadedModNames(array_merge($this->mandatoryModNames, $optionalModNames))
                    ->setLoadedOptionalModNames($optionalModNames)
                    ->setParentCombinations(
                        $this->parentCombinationFinder->findParentCombinations($combination, $this->combinations)
                    );
        return $combination;
    }

    /**
     * Creates combinations with the specified number of optional mods.
     * @param int $numberOfOptionalMods
     * @return $this
     */
    protected function createCombinationsWithOptionalMods(int $numberOfOptionalMods)
    {
        $continueRecursion = false;
        foreach ($this->combinations as $combination) {
            if (count($combination->getLoadedOptionalModNames()) === $numberOfOptionalMods - 1) {
                foreach ($this->optionalModNames as $optionalModName) {
                    if ($this->isNewOptionalModNameValid($combination->getLoadedOptionalModNames(), $optionalModName)) {
                        $newCombination = $this->createCombinationEntity(array_merge(
                            $combination->getLoadedOptionalModNames(),
                            [$optionalModName]
                        ));

                        if ($numberOfOptionalMods === 1 || $this->hasDirectParentCombination($newCombination)) {
                            $this->combinations[] = $newCombination;
                            $continueRecursion = true;
                        }
                    }
                }
            }
        }

        if ($continueRecursion) {
            $this->createCombinationsWithOptionalMods($numberOfOptionalMods + 1);
        }
        return $this;
    }

    /**
     * Checks whether the new mod name is valid as an additional optional mod.
     * @param array|string $existingOptionalModNames
     * @param string $newOptionalModName
     * @return bool
     */
    protected function isNewOptionalModNameValid(array $existingOptionalModNames, string $newOptionalModName): bool
    {
        $newModOrder = $this->exportDataService->getMod($newOptionalModName)->getOrder();

        $result = true;
        foreach ($existingOptionalModNames as $existingOptionalModName) {
            $order = $this->exportDataService->getMod($existingOptionalModName)->getOrder();
            if ($order >= $newModOrder) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Checks whether the direct parent combination is present.
     * @param ExportCombination $combination
     * @return bool
     */
    protected function hasDirectParentCombination(ExportCombination $combination): bool
    {
        $result = false;
        $expectedCombinationName = implode('-', $combination->getLoadedOptionalModNames());
        foreach ($combination->getParentCombinations() as $parentCombination) {
            if ($parentCombination->getName() === $expectedCombinationName) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}
