<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Technology;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Helper\HashCalculator;
use Generator;

/**
 * The data processing filtering expensive entities which are identical to their normal version.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExpensiveEntityFilter implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly HashCalculator $hashCalculator,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        $expensiveEntities = [];

        $progressBar = $this->console->createProgressBar('Hash expensive entities');
        $progressBar->setNumberOfSteps($this->countEntities($exportData));
        foreach ($this->getEntities($exportData) as $entity) {
            if (($entity instanceof Recipe || $entity instanceof Technology) && $entity->mode === 'expensive') {
                $hash = $this->hashCalculator->hashEntity($entity);
                $expensiveEntities[get_class($entity)][$hash] = $entity;
            }
            $progressBar->step();
        }

        $progressBar = $this->console->createProgressBar('Hash normal entities');
        $progressBar->setNumberOfSteps($this->countEntities($exportData));
        foreach ($this->getEntities($exportData) as $entity) {
            if (($entity instanceof Recipe || $entity instanceof Technology) && $entity->mode === 'normal') {
                $hash = $this->hashCalculator->hashEntity($entity);
                $expensiveEntity = $expensiveEntities[get_class($entity)][$hash] ?? null;

                if ($expensiveEntity instanceof $entity) {
                    switch (get_class($expensiveEntity)) {
                        case Recipe::class:
                            $exportData->getRecipes()->remove($expensiveEntity);
                            break;

                        case Technology::class:
                            $exportData->getTechnologies()->remove($expensiveEntity);
                            break;
                    }
                }
            }
            $progressBar->step();
        }
    }

    private function countEntities(ExportData $exportData): int
    {
        return $exportData->getRecipes()->count()
            + $exportData->getTechnologies()->count();
    }

    /**
     * @param ExportData $exportData
     * @return Generator<int, object, void, void>
     */
    private function getEntities(ExportData $exportData): Generator
    {
        yield from $exportData->getRecipes();
        yield from $exportData->getTechnologies();
    }
}
