<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use ArrayIterator;
use FactorioItemBrowser\Export\DataProcessor\ExpensiveEntityFilter;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Technology;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Helper\HashCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExpensiveRecipeFilter class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\ExpensiveEntityFilter
 */
class ExpensiveEntityFilterTest extends TestCase
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

    private function createInstance(): ExpensiveEntityFilter
    {
        return new ExpensiveEntityFilter(
            $this->console,
            $this->hashCalculator,
        );
    }

    public function testProcess(): void
    {
        $recipe1 = new Recipe();
        $recipe1->name = 'abc';
        $recipe1->mode = 'expensive';

        $recipe2 = new Recipe();
        $recipe2->name = 'def';
        $recipe2->mode = 'expensive';

        $recipe3 = new Recipe();
        $recipe3->name = 'abc';
        $recipe3->mode = 'normal';

        $recipe4 = new Recipe();
        $recipe4->name = 'abc';
        $recipe4->mode = 'expensive';

        $technology1 = new Technology();
        $technology1->name = 'abc';
        $technology1->mode = 'expensive';

        $technology2 = new Technology();
        $technology2->name = 'def';
        $technology2->mode = 'expensive';

        $technology3 = new Technology();
        $technology3->name = 'abc';
        $technology3->mode = 'normal';

        $technology4 = new Technology();
        $technology4->name = 'abc';
        $technology4->mode = 'expensive';

        $recipes = $this->createMock(ChunkedCollection::class);
        $recipes->expects($this->any())
                ->method('count')
                ->willReturn(4);
        $recipes->expects($this->any())
                ->method('getIterator')
                ->willReturn(new ArrayIterator([$recipe1, $recipe2, $recipe3, $recipe4]));
        $recipes->expects($this->once())
                ->method('remove')
                ->with($this->identicalTo($recipe1));

        $technologies = $this->createMock(ChunkedCollection::class);
        $technologies->expects($this->any())
                     ->method('count')
                     ->willReturn(4);
        $technologies->expects($this->any())
                     ->method('getIterator')
                     ->willReturn(new ArrayIterator([$technology1, $technology2, $technology3, $technology4]));
        $technologies->expects($this->once())
                     ->method('remove')
                     ->with($this->identicalTo($technology1));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getRecipes')
                   ->willReturn($recipes);
        $exportData->expects($this->any())
                   ->method('getTechnologies')
                   ->willReturn($technologies);

        $progressBar = $this->createMock(ProgressBar::class);
        $progressBar->expects($this->exactly(2))
                    ->method('setNumberOfSteps')
                    ->with($this->identicalTo(8));
        $progressBar->expects($this->exactly(16))
                    ->method('step');

        $this->console->expects($this->exactly(2))
                      ->method('createProgressBar')
                      ->with($this->isType('string'))
                      ->willReturn($progressBar);

        $this->hashCalculator->expects($this->any())
                             ->method('hashEntity')
                             ->willReturnMap([
                                 [$recipe1, 'ghi'],
                                 [$recipe2, 'jkl'],
                                 [$recipe3, 'ghi'],
                                 [$recipe4, 'mno'],
                                 [$technology1, 'ghi'],
                                 [$technology2, 'jkl'],
                                 [$technology3, 'ghi'],
                                 [$technology4, 'mno'],
                             ]);

        $instance = $this->createInstance();
        $instance->process($exportData);
    }
}
