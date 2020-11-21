<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The class parsing the recipes of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeParser implements ParserInterface
{
    protected HashCalculator $hashCalculator;
    protected IconParser $iconParser;
    protected MapperManagerInterface $mapperManager;
    protected TranslationParser $translationParser;

    public function __construct(
        HashCalculator $hashCalculator,
        IconParser $iconParser,
        MapperManagerInterface $mapperManager,
        TranslationParser $translationParser
    ) {
        $this->hashCalculator = $hashCalculator;
        $this->iconParser = $iconParser;
        $this->mapperManager = $mapperManager;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
        $recipes = [];
        foreach ($dump->normalRecipes as $dumpRecipe) {
            $normalRecipe = $this->createRecipe($dumpRecipe, RecipeMode::NORMAL);
            $recipes[$this->hashCalculator->hashRecipe($normalRecipe)] = $normalRecipe;
        }

        foreach ($dump->expensiveRecipes as $dumpRecipe) {
            $expensiveRecipe = $this->createRecipe($dumpRecipe, RecipeMode::EXPENSIVE);
            $hash = $this->hashCalculator->hashRecipe($expensiveRecipe);

            if (!isset($recipes[$hash])) {
                $recipes[$hash] = $expensiveRecipe;
            }
        }

        foreach ($recipes as $recipe) {
            $exportData->getRecipes()->add($recipe);
        }
    }

    protected function createRecipe(DumpRecipe $dumpRecipe, string $mode): ExportRecipe
    {
        $exportRecipe = $this->mapperManager->map($dumpRecipe, new ExportRecipe());
        $exportRecipe->mode = $mode;

        $this->translationParser->translate($exportRecipe->labels, $dumpRecipe->localisedName);
        $this->translationParser->translate($exportRecipe->descriptions, $dumpRecipe->localisedDescription);

        $exportRecipe->iconId = $this->getIconId($exportRecipe);
        return $exportRecipe;
    }

    protected function getIconId(ExportRecipe $recipe): string
    {
        $iconId = $this->iconParser->getIconId(EntityType::RECIPE, $recipe->name);

        // If the recipe does not have an own icon, it may fall back to its first product's icon.
        if ($iconId === '' && count($recipe->products) > 0) {
            $firstProduct = $recipe->products[0];
            $iconId = $this->iconParser->getIconId($firstProduct->type, $firstProduct->name);
        }

        return $iconId;
    }

    public function validate(ExportData $exportData): void
    {
    }
}
