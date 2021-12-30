<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\DataProcessor\ExpensiveRecipeFilter;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExpensiveRecipeFilter class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\ExpensiveRecipeFilter
 */
class ExpensiveRecipeFilterTest extends TestCase
{
    /** @var Console&MockObject */
    private Console $console;
    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->hashCalculator = $this->createMock(HashCalculator::class);
    }

    private function createInstance(): ExpensiveRecipeFilter
    {
        return new ExpensiveRecipeFilter(
            $this->console,
            $this->hashCalculator,
        );
    }

    public function testProcess(): void
    {
        $recipe1 = new Recipe();
        $recipe1->name = 'abc';
        $recipe1->mode = RecipeMode::EXPENSIVE;

        $recipe2 = new Recipe();
        $recipe2->name = 'def';
        $recipe2->mode = RecipeMode::EXPENSIVE;

        $recipe3 = new Recipe();
        $recipe3->name = 'abc';
        $recipe3->mode = RecipeMode::NORMAL;

        $recipe4 = new Recipe();
        $recipe4->name = 'abc';
        $recipe4->mode = RecipeMode::EXPENSIVE;

        $recipes = $this->createMock(ChunkedCollection::class);
        $recipes->expects($this->once())
                ->method('remove')
                ->with($this->identicalTo($recipe1));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getRecipes')
                   ->willReturn($recipes);

        $this->console->expects($this->exactly(2))
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo($recipes))
                      ->willReturnCallback(fn() => yield from [$recipe1, $recipe2, $recipe3, $recipe4]);

        $this->hashCalculator->expects($this->any())
                             ->method('hashRecipe')
                             ->willReturnMap([
                                 [$recipe1, 'ghi'],
                                 [$recipe2, 'jkl'],
                                 [$recipe3, 'ghi'],
                                 [$recipe4, 'mno'],
                             ]);

        $instance = $this->createInstance();
        $instance->process($exportData);
    }
}
