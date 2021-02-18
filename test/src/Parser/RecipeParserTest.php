<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Parser\RecipeParser
 */
class RecipeParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;
    /** @var IconParser&MockObject */
    private IconParser $iconParser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->iconParser = $this->createMock(IconParser::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @param array<string> $methods
     * @return RecipeParser&MockObject
     */
    private function createInstance(array $methods = []): RecipeParser
    {
        return $this->getMockBuilder(RecipeParser::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($methods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->hashCalculator,
                        $this->iconParser,
                        $this->mapperManager,
                        $this->translationParser,
                    ])
                    ->getMock();
    }

    /**
     * @throws ExportException
     */
    public function testEmptyMethods(): void
    {
        $dump = $this->createMock(Dump::class);
        $exportData = $this->createMock(ExportData::class);

        $instance = $this->createInstance();
        $instance->prepare($dump);
        $instance->validate($exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     */
    public function testParse(): void
    {
        $dumpRecipe1 = $this->createMock(DumpRecipe::class);
        $dumpRecipe2 = $this->createMock(DumpRecipe::class);
        $dumpRecipe3 = $this->createMock(DumpRecipe::class);
        $dumpRecipe4 = $this->createMock(DumpRecipe::class);
        $normalRecipe1 = $this->createMock(ExportRecipe::class);
        $normalRecipe2 = $this->createMock(ExportRecipe::class);
        $expensiveRecipe1 = $this->createMock(ExportRecipe::class);
        $expensiveRecipe2 = $this->createMock(ExportRecipe::class);

        $normalRecipeHash1 = 'abc';
        $normalRecipeHash2 = 'def';
        $expensiveRecipeHash1 = 'ghi';
        $expensiveRecipeHash2 = 'abc';

        $dump = new Dump();
        $dump->normalRecipes = [$dumpRecipe1, $dumpRecipe2];
        $dump->expensiveRecipes = [$dumpRecipe3, $dumpRecipe4];

        $recipes = $this->createMock(ChunkedCollection::class);
        $recipes->expects($this->exactly(3))
                ->method('add')
                ->withConsecutive(
                    [$this->identicalTo($normalRecipe1)],
                    [$this->identicalTo($normalRecipe2)],
                    [$this->identicalTo($expensiveRecipe1)],
                );

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getRecipes')
                   ->willReturn($recipes);

        $this->console->expects($this->exactly(2))
                      ->method('iterateWithProgressbar')
                      ->withConsecutive(
                          [$this->isType('string'), $this->identicalTo([$dumpRecipe1, $dumpRecipe2])],
                          [$this->isType('string'), $this->identicalTo([$dumpRecipe3, $dumpRecipe4])],
                      )
                      ->willReturnOnConsecutiveCalls(
                          $this->returnCallback(fn() => yield from [$dumpRecipe1, $dumpRecipe2]),
                          $this->returnCallback(fn() => yield from [$dumpRecipe3, $dumpRecipe4]),
                      );

        $this->hashCalculator->expects($this->exactly(4))
                             ->method('hashRecipe')
                             ->withConsecutive(
                                 [$this->identicalTo($normalRecipe1)],
                                 [$this->identicalTo($normalRecipe2)],
                                 [$this->identicalTo($expensiveRecipe1)],
                                 [$this->identicalTo($expensiveRecipe2)]
                             )
                             ->willReturnOnConsecutiveCalls(
                                 $normalRecipeHash1,
                                 $normalRecipeHash2,
                                 $expensiveRecipeHash1,
                                 $expensiveRecipeHash2
                             );

        $instance = $this->createInstance(['createRecipe']);
        $instance->expects($this->exactly(4))
                 ->method('createRecipe')
                 ->withConsecutive(
                     [$this->identicalTo($dumpRecipe1)],
                     [$this->identicalTo($dumpRecipe2)],
                     [$this->identicalTo($dumpRecipe3)],
                     [$this->identicalTo($dumpRecipe4)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $normalRecipe1,
                     $normalRecipe2,
                     $expensiveRecipe1,
                     $expensiveRecipe2
                 );

        $instance->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateRecipe(): void
    {
        $iconId = 'abc';
        $mode = 'def';

        $dumpRecipe = new DumpRecipe();
        $dumpRecipe->name = 'ghi';
        $dumpRecipe->localisedName = 'jkl';
        $dumpRecipe->localisedDescription = 'mno';

        $exportRecipe = new ExportRecipe();
        $exportRecipe->name = 'ghi';

        $expectedRecipe = new ExportRecipe();
        $expectedRecipe->name = 'ghi';
        $expectedRecipe->mode = 'def';

        $expectedResult = new ExportRecipe();
        $expectedResult->name = 'ghi';
        $expectedResult->mode = 'def';
        $expectedResult->iconId = 'abc';

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($dumpRecipe), $this->isInstanceOf(ExportRecipe::class))
                            ->willReturn($exportRecipe);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->identicalTo($exportRecipe->labels),
                                        $this->identicalTo('jkl'),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->identicalTo($exportRecipe->descriptions),
                                        $this->identicalTo('mno'),
                                        $this->isNull(),
                                    ],
                                );

        $instance = $this->createInstance(['getIconId']);
        $instance->expects($this->once())
                 ->method('getIconId')
                 ->with($this->equalTo($expectedRecipe))
                 ->willReturn($iconId);

        $result = $this->invokeMethod($instance, 'createRecipe', $dumpRecipe, $mode);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIconId(): void
    {
        $iconId = 'abc';
        $recipeName = 'def';

        $recipe = new ExportRecipe();
        $recipe->name = $recipeName;

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::RECIPE), $this->identicalTo($recipeName))
                         ->willReturn($iconId);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getIconId', $recipe);

        $this->assertSame($iconId, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapIconIdWithFirstProductId(): void
    {
        $iconId = 'abc';
        $recipeName = 'def';

        $product = new ExportProduct();
        $product->type = 'ghi';
        $product->name = 'jkl';

        $recipe = new ExportRecipe();
        $recipe->name = $recipeName;
        $recipe->products = [$product];

        $this->iconParser->expects($this->exactly(2))
                         ->method('getIconId')
                         ->withConsecutive(
                             [$this->identicalTo(EntityType::RECIPE), $this->identicalTo($recipeName)],
                             [$this->identicalTo('ghi'), $this->identicalTo('jkl')]
                         )
                         ->willReturnOnConsecutiveCalls(
                             '',
                             $iconId
                         );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getIconId', $recipe);

        $this->assertSame($iconId, $result);
    }
}
