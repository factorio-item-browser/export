<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data processing filtering expensive recipes which are identical to their normal version.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExpensiveRecipeFilter implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly HashCalculator $hashCalculator,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        $expensiveRecipes = [];

        foreach (
            $this->console->iterateWithProgressbar('Hash expensive recipes', $exportData->getRecipes()) as $recipe
        ) {
            /* @var Recipe $recipe */
            if ($recipe->mode === RecipeMode::EXPENSIVE) {
                $hash = $this->hashCalculator->hashRecipe($recipe);
                $expensiveRecipes[$hash] = $recipe;
            }
        }

        foreach ($this->console->iterateWithProgressbar('Hash normal recipes', $exportData->getRecipes()) as $recipe) {
            /* @var Recipe $recipe */
            if ($recipe->mode === RecipeMode::NORMAL) {
                $hash = $this->hashCalculator->hashRecipe($recipe);
                if (isset($expensiveRecipes[$hash])) {
                    $exportData->getRecipes()->remove($expensiveRecipes[$hash]);
                }
            }
        }
    }
}
