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
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\RecipeParser
 */
class RecipeParserTest extends TestCase
{
    use ReflectionTrait;

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
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->iconParser = $this->createMock(IconParser::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new RecipeParser(
            $this->hashCalculator,
            $this->iconParser,
            $this->mapperManager,
            $this->translationParser,
        );

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
        $this->assertSame($this->iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($this->mapperManager, $this->extractProperty($parser, 'mapperManager'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dump = $this->createMock(Dump::class);

        $parser = new RecipeParser(
            $this->hashCalculator,
            $this->iconParser,
            $this->mapperManager,
            $this->translationParser,
        );
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     * @covers ::parse
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

        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->onlyMethods(['createRecipe'])
                       ->setConstructorArgs([
                           $this->hashCalculator,
                           $this->iconParser,
                           $this->mapperManager,
                           $this->translationParser,
                       ])
                       ->getMock();
        $parser->expects($this->exactly(4))
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

        $parser->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     * @covers ::createRecipe
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

        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->onlyMethods(['getIconId'])
                       ->setConstructorArgs([
                           $this->hashCalculator,
                           $this->iconParser,
                           $this->mapperManager,
                           $this->translationParser,
                       ])
                       ->getMock();
        $parser->expects($this->once())
               ->method('getIconId')
               ->with($this->equalTo($expectedRecipe))
               ->willReturn($iconId);

        $result = $this->invokeMethod($parser, 'createRecipe', $dumpRecipe, $mode);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getIconId
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

        $parser = new RecipeParser(
            $this->hashCalculator,
            $this->iconParser,
            $this->mapperManager,
            $this->translationParser,
        );
        $result = $this->invokeMethod($parser, 'getIconId', $recipe);

        $this->assertSame($iconId, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getIconId
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

        $parser = new RecipeParser(
            $this->hashCalculator,
            $this->iconParser,
            $this->mapperManager,
            $this->translationParser,
        );
        $result = $this->invokeMethod($parser, 'getIconId', $recipe);

        $this->assertSame($iconId, $result);
    }

    /**
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $parser = new RecipeParser(
            $this->hashCalculator,
            $this->iconParser,
            $this->mapperManager,
            $this->translationParser,
        );
        $parser->validate($exportData);

        $this->addToAssertionCount(1);
    }
}
