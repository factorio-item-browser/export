<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Technology;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Helper\HashCalculator;
use Generator;

/**
 * The data processor assigning the icons to the entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconAssigner implements DataProcessorInterface
{
    /** @var array<string, array<string, Icon>> */
    protected array $icons = [];

    public function __construct(
        private readonly Console $console,
        private readonly HashCalculator $hashCalculator,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        $progressBar = $this->console->createProgressBar('Assign icons to entities');
        $progressBar->setNumberOfSteps($this->countEntities($exportData));

        $this->prepareIcons($exportData);

        foreach ($this->getEntities($exportData) as $entity) {
            $this->processEntity($entity);
            $progressBar->finish('');
        }
    }

    protected function countEntities(ExportData $exportData): int
    {
        return $exportData->getItems()->count()
            + $exportData->getMods()->count()
            + $exportData->getMachines()->count()
            + $exportData->getRecipes()->count()
            + $exportData->getTechnologies()->count();
    }

    /**
     * @param ExportData $exportData
     * @return Generator<int, object, void, void>
     */
    protected function getEntities(ExportData $exportData): Generator
    {
        yield from $exportData->getItems();
        yield from $exportData->getMachines();
        yield from $exportData->getMods();
        yield from $exportData->getRecipes();
        yield from $exportData->getTechnologies();
    }

    protected function prepareIcons(ExportData $exportData): void
    {
        foreach ($exportData->getIcons() as $icon) {
            /* @var Icon $icon */
            switch ($icon->type) {
                case 'fluid':
                case 'item':
                case 'mod':
                case 'recipe':
                case 'resource':
                case 'technology':
                case 'tutorial':
                    $this->icons[$icon->type][$icon->name] = $icon;
                    break;

                default:
                    if (!isset($this->icons['item'][$icon->name])) {
                        $this->icons['item'][$icon->name] = $icon;
                    }
                    if (!isset($this->icons['machine'][$icon->name])) {
                        $this->icons['machine'][$icon->name] = $icon;
                    }
                    break;
            }
        }
    }

    protected function processEntity(object $entity): void
    {
        switch (get_class($entity)) {
            case Item::class:
                $entity->iconId = $this->getIconId($entity->type, $entity->name);
                break;

            case Machine::class:
                $entity->iconId = $this->getIconId('machine', $entity->name);
                break;

            case Mod::class:
                $entity->iconId = $this->getIconId('mod', $entity->name);
                break;

            case Recipe::class:
                $entity->iconId = $this->getIconId('recipe', $entity->name);
                if ($entity->iconId === '' && count($entity->products) > 0) {
                    $firstProduct = $entity->products[0];
                    $entity->iconId = $this->getIconId($firstProduct->type, $firstProduct->name);
                }
                break;

            case Technology::class:
                $entity->iconId = $this->getIconId('technology', $entity->name);
                break;
        }
    }

    protected function getIconId(string $type, string $name): string
    {
        if (!isset($this->icons[$type][$name])) {
            return '';
        }

        $icon = $this->icons[$type][$name];
        if ($icon->id === '') {
            $icon->id = $this->hashCalculator->hashEntity($icon);
        }
        return $icon->id;
    }
}
