<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Ingredient as DumpIngredient;
use FactorioItemBrowser\Export\Entity\Dump\Product as DumpProduct;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;
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

    /**
     * The mocked hash calculator.
     * @var HashCalculator&MockObject
     */
    protected $hashCalculator;

    /**
     * The mocked icon parser.
     * @var IconParser&MockObject
     */
    protected $iconParser;

    /**
     * The mocked translation parser.
     * @var TranslationParser&MockObject
     */
    protected $translationParser;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->iconParser = $this->createMock(IconParser::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
        $this->assertSame($this->iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * Tests the prepare method.
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);

        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var DumpRecipe&MockObject $dumpRecipe1 */
        $dumpRecipe1 = $this->createMock(DumpRecipe::class);
        /* @var DumpRecipe&MockObject $dumpRecipe2 */
        $dumpRecipe2 = $this->createMock(DumpRecipe::class);
        /* @var DumpRecipe&MockObject $dumpRecipe3 */
        $dumpRecipe3 = $this->createMock(DumpRecipe::class);
        /* @var DumpRecipe&MockObject $dumpRecipe4 */
        $dumpRecipe4 = $this->createMock(DumpRecipe::class);

        /* @var ExportRecipe&MockObject $normalRecipe1 */
        $normalRecipe1 = $this->createMock(ExportRecipe::class);
        /* @var ExportRecipe&MockObject $normalRecipe2 */
        $normalRecipe2 = $this->createMock(ExportRecipe::class);
        /* @var ExportRecipe&MockObject $expensiveRecipe1 */
        $expensiveRecipe1 = $this->createMock(ExportRecipe::class);
        /* @var ExportRecipe&MockObject $expensiveRecipe2 */
        $expensiveRecipe2 = $this->createMock(ExportRecipe::class);

        $normalRecipeHash1 = 'abc';
        $normalRecipeHash2 = 'def';
        $expensiveRecipeHash1 = 'ghi';
        $expensiveRecipeHash2 = 'abc';

        $expectedRecipes = [$normalRecipe1, $normalRecipe2, $expensiveRecipe1];

        $dump = new Dump();
        $dump->getControlStage()->setNormalRecipes([$dumpRecipe1, $dumpRecipe2])
                                ->setExpensiveRecipes([$dumpRecipe3, $dumpRecipe4]);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('setRecipes')
                    ->with($this->identicalTo($expectedRecipes));

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

        /* @var RecipeParser&MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->onlyMethods(['mapRecipe'])
                       ->setConstructorArgs([$this->hashCalculator, $this->iconParser, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('mapRecipe')
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

        $parser->parse($dump, $combination);
    }

    /**
     * Tests the mapRecipe method.
     * @throws ReflectionException
     * @covers ::mapRecipe
     */
    public function testMapRecipe(): void
    {
        $iconId = 'abc';
        $mode = 'def';

        /* @var DumpIngredient&MockObject $dumpIngredient1 */
        $dumpIngredient1 = $this->createMock(DumpIngredient::class);
        /* @var DumpIngredient&MockObject $dumpIngredient2 */
        $dumpIngredient2 = $this->createMock(DumpIngredient::class);
        /* @var DumpProduct&MockObject $dumpProduct1 */
        $dumpProduct1 = $this->createMock(DumpProduct::class);
        /* @var DumpProduct&MockObject $dumpProduct2 */
        $dumpProduct2 = $this->createMock(DumpProduct::class);
        /* @var ExportIngredient&MockObject $exportIngredient1 */
        $exportIngredient1 = $this->createMock(ExportIngredient::class);
        /* @var ExportIngredient&MockObject $exportIngredient2 */
        $exportIngredient2 = $this->createMock(ExportIngredient::class);
        /* @var ExportProduct&MockObject $exportProduct1 */
        $exportProduct1 = $this->createMock(ExportProduct::class);
        /* @var ExportProduct&MockObject $exportProduct2 */
        $exportProduct2 = $this->createMock(ExportProduct::class);

        $dumpRecipe = new DumpRecipe();
        $dumpRecipe->setName('Ghi')
                   ->setCraftingTime(13.37)
                   ->setCraftingCategory('jkl')
                   ->setIngredients([$dumpIngredient1, $dumpIngredient2])
                   ->setProducts([$dumpProduct1, $dumpProduct2]);

        $expectedRecipe = new ExportRecipe();
        $expectedRecipe->setName('ghi')
                       ->setMode('def')
                       ->setCraftingTime(13.37)
                       ->setCraftingCategory('jkl')
                       ->setIngredients([$exportIngredient1])
                       ->setProducts([$exportProduct1]);

        $expectedResult = new ExportRecipe();
        $expectedResult->setName('ghi')
                       ->setMode('def')
                       ->setCraftingTime(13.37)
                       ->setCraftingCategory('jkl')
                       ->setIngredients([$exportIngredient1])
                       ->setProducts([$exportProduct1])
                       ->setIconId($iconId);

        $this->translationParser->expects($this->once())
                                ->method('translateNames')
                                ->with(
                                    $this->isInstanceOf(LocalisedString::class),
                                    $this->identicalTo($dumpRecipe->getLocalisedName()),
                                    $this->isNull()
                                );
        $this->translationParser->expects($this->once())
                                ->method('translateDescriptions')
                                ->with(
                                    $this->isInstanceOf(LocalisedString::class),
                                    $this->identicalTo($dumpRecipe->getLocalisedDescription()),
                                    $this->isNull()
                                );

        /* @var RecipeParser&MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->onlyMethods([
                           'mapIngredient',
                           'isIngredientValid',
                           'mapProduct',
                           'isProductValid',
                           'mapIconId',
                       ])
                       ->setConstructorArgs([$this->hashCalculator, $this->iconParser, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('mapIngredient')
               ->withConsecutive(
                   [$this->identicalTo($dumpIngredient1)],
                   [$this->identicalTo($dumpIngredient2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportIngredient1,
                   $exportIngredient2
               );
        $parser->expects($this->exactly(2))
               ->method('isIngredientValid')
               ->withConsecutive(
                   [$this->identicalTo($exportIngredient1)],
                   [$this->identicalTo($exportIngredient2)]
               )
               ->willReturnOnConsecutiveCalls(
                   true,
                   false
               );
        $parser->expects($this->exactly(2))
               ->method('mapProduct')
               ->withConsecutive(
                   [$this->identicalTo($dumpProduct1)],
                   [$this->identicalTo($dumpProduct2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportProduct1,
                   $exportProduct2
               );
        $parser->expects($this->exactly(2))
               ->method('isProductValid')
               ->withConsecutive(
                   [$this->identicalTo($exportProduct1)],
                   [$this->identicalTo($exportProduct2)]
               )
               ->willReturnOnConsecutiveCalls(
                   true,
                   false
               );
        $parser->expects($this->once())
               ->method('mapIconId')
               ->with($this->equalTo($expectedRecipe))
               ->willReturn($iconId);

        $result = $this->invokeMethod($parser, 'mapRecipe', $dumpRecipe, $mode);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapIngredient method.
     * @throws ReflectionException
     * @covers ::mapIngredient
     */
    public function testMapIngredient(): void
    {
        $dumpIngredient = new DumpIngredient();
        $dumpIngredient->setType('Abc')
                       ->setName('Def')
                       ->setAmount(13.37);

        $expectedResult = new ExportIngredient();
        $expectedResult->setType('abc')
                       ->setName('def')
                       ->setAmount(13.37);

        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapIngredient', $dumpIngredient);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the isIngredientValid test.
     * @return array<mixed>
     */
    public function provideIsIngredientValid(): array
    {
        $ingredient1 = new ExportIngredient();
        $ingredient1->setAmount(42);

        $ingredient2 = new ExportIngredient();
        $ingredient2->setAmount(0);

        return [
            [$ingredient1, true],
            [$ingredient2, false],
        ];
    }

    /**
     * Tests the isIngredientValid method.
     * @param ExportIngredient $ingredient
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isIngredientValid
     * @dataProvider provideIsIngredientValid
     */
    public function testIsIngredientValid(ExportIngredient $ingredient, bool $expectedResult): void
    {
        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'isIngredientValid', $ingredient);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the mapProduct method.
     * @throws ReflectionException
     * @covers ::mapProduct
     */
    public function testMapProduct(): void
    {
        $dumpProduct = new DumpProduct();
        $dumpProduct->setType('Abc')
                    ->setName('Def')
                    ->setAmountMin(12.34)
                    ->setAmountMax(23.45)
                    ->setProbability(34.56);
        
        $expectedResult = new ExportProduct();
        $expectedResult->setType('abc')
                      ->setName('def')
                      ->setAmountMin(12.34)
                      ->setAmountMax(23.45)
                      ->setProbability(34.56);
        
        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapProduct', $dumpProduct);
        
        $this->assertEquals($expectedResult, $result);
    }
    
    /**
     * Provides the data for the isProductValid test.
     * @return array<mixed>
     */
    public function provideIsProductValid(): array
    {
        $product1 = new ExportProduct();
        $product1->setAmountMin(21)
                 ->setAmountMax(42)
                 ->setProbability(1.);

        $product2 = new ExportProduct();
        $product2->setAmountMin(0)
                 ->setAmountMax(0)
                 ->setProbability(1.);

        $product3 = new ExportProduct();
        $product3->setAmountMin(21)
                 ->setAmountMax(42)
                 ->setProbability(0.);


        return [
            [$product1, true],
            [$product2, false],
            [$product3, false],
        ];
    }

    /**
     * Tests the isProductValid method.
     * @param ExportProduct $product
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isProductValid
     * @dataProvider provideIsProductValid
     */
    public function testIsProductValid(ExportProduct $product, bool $expectedResult): void
    {
        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'isProductValid', $product);

        $this->assertSame($expectedResult, $result);
    }
    
    /**
     * Tests the mapIconId method.
     * @throws ReflectionException
     * @covers ::mapIconId
     */
    public function testMapIconId(): void
    {
        $iconId = 'abc';
        $recipeName = 'def';

        $recipe = new ExportRecipe();
        $recipe->setName($recipeName);

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::RECIPE), $this->identicalTo($recipeName))
                         ->willReturn($iconId);

        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapIconId', $recipe);

        $this->assertSame($iconId, $result);
    }

    /**
     * Tests the mapIconId method.
     * @throws ReflectionException
     * @covers ::mapIconId
     */
    public function testMapIconIdWithFirstProductId(): void
    {
        $iconId = 'abc';
        $recipeName = 'def';

        $product = new ExportProduct();
        $product->setType('ghi')
                ->setName('jkl');

        $recipe = new ExportRecipe();
        $recipe->setName($recipeName)
               ->setProducts([$product]);

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

        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapIconId', $recipe);

        $this->assertSame($iconId, $result);
    }

    /**
     * Tests the validate method.
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new RecipeParser($this->hashCalculator, $this->iconParser, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
