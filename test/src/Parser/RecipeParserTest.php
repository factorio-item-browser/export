<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
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
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $recipeRegistry, $translator);
        $this->assertSame($iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($recipeRegistry, $this->extractProperty($parser, 'recipeRegistry'));
        $this->assertSame($translator, $this->extractProperty($parser, 'translator'));
    }
    
    
    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        $combination = new Combination();
        $dumpData = new DataContainer([
            'recipes' => [
                'normal' => [
                    ['abc' => 'def'],
                    ['ghi' => 'jkl'],
                ],
                'expensive' => [
                    ['mno' => 'pqr'],
                    ['stu' => 'vwx'],
                ],
            ],
        ]);

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['processRecipe'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('processRecipe')
               ->withConsecutive(
                   [$combination, $this->equalTo(new DataContainer(['abc' => 'def'])), 'normal'],
                   [$combination, $this->equalTo(new DataContainer(['ghi' => 'jkl'])), 'normal'],
                   [$combination, $this->equalTo(new DataContainer(['mno' => 'pqr'])), 'expensive'],
                   [$combination, $this->equalTo(new DataContainer(['stu' => 'vwx'])), 'expensive']
               );
        $parser->parse($combination, $dumpData);
    }


    /**
     * Tests the processRecipe method.
     * @throws ReflectionException
     * @covers ::processRecipe
     */
    public function testProcessRecipe(): void
    {
        $recipeData = new DataContainer(['abc' => 'def']);
        $type = 'ghi';
        $recipe = new Recipe();
        $recipeHash = 'jkl';

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        /* @var EntityRegistry|MockObject $recipeRegistry */
        $recipeRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $recipeRegistry->expects($this->once())
                     ->method('set')
                     ->with($recipe)
                     ->willReturn($recipeHash);

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['addRecipeHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('addRecipeHash')
                    ->with($recipeHash);

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['parseRecipe', 'addTranslations', 'assignIconHash'])
                       ->setConstructorArgs([$iconParser, $recipeRegistry, $translator])
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseRecipe')
               ->with($recipeData, $type)
               ->willReturn($recipe);
        $parser->expects($this->once())
               ->method('addTranslations')
               ->with($recipe, $recipeData);
        $parser->expects($this->once())
               ->method('assignIconHash')
               ->with($combination, $recipe);

        $this->invokeMethod($parser, 'processRecipe', $combination, $recipeData, $type);
    }

    /**
     * Tests the parseRecipe method.
     * @throws ReflectionException
     * @covers ::parseRecipe
     */
    public function testParseRecipe(): void
    {
        $mode = 'abc';
        $recipeData = new DataContainer([
            'name' => 'Def',
            'craftingTime' => 13.37,
            'craftingCategory' => 'ghi',
            'ingredients' => ['jkl' => 'mno'],
            'products' => ['pqr' => 'stu'],
        ]); 
        $ingredients = [new Ingredient(), new Ingredient()];
        $products = [new Product(), new Product()];
        $expectedResult = new Recipe();
        $expectedResult->setMode('abc')
                       ->setName('def')
                       ->setCraftingTime(13.37)
                       ->setCraftingCategory('ghi')
                       ->setIngredients($ingredients)
                       ->setProducts($products);
        
        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['parseIngredients', 'parseProducts'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseIngredients')
               ->with($recipeData)
               ->willReturn($ingredients); 
        $parser->expects($this->once())
               ->method('parseProducts')
               ->with($recipeData)
               ->willReturn($products); 

        $result = $this->invokeMethod($parser, 'parseRecipe', $recipeData, $mode);
        $this->assertEquals($expectedResult, $result);
    }
    
    /**
     * Tests the parseIngredients method.
     * @throws ReflectionException
     * @covers ::parseIngredients
     */
    public function testParseIngredients(): void
    {
        $recipeData = new DataContainer([
            'ingredients' => [
                ['abc' => 'def'],
                ['ghi' => 'jkl'],
                ['mno' => 'pqr'],
            ]
        ]);
        
        /* @var Ingredient|MockObject $ingredient1 */
        $ingredient1 = $this->getMockBuilder(Ingredient::class)
                            ->setMethods(['setOrder'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $ingredient1->expects($this->once())
                    ->method('setOrder')
                    ->with(1);
        
        /* @var Ingredient|MockObject $ingredient2 */
        $ingredient2 = $this->getMockBuilder(Ingredient::class)
                            ->setMethods(['setOrder'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $ingredient2->expects($this->once())
                    ->method('setOrder')
                    ->with(2);

        $expectedResult = [$ingredient1, $ingredient2];

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['parseIngredient'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(3))
               ->method('parseIngredient')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['abc' => 'def']))],
                   [$this->equalTo(new DataContainer(['ghi' => 'jkl']))],
                   [$this->equalTo(new DataContainer(['mno' => 'pqr']))]
               )
               ->willReturnOnConsecutiveCalls(
                   $ingredient1,
                   null,
                   $ingredient2
               );

        $result = $this->invokeMethod($parser, 'parseIngredients', $recipeData);
        $this->assertEquals($expectedResult, $result);
    }
    
    /**
     * Provides the data for the parseIngredient test.
     * @return array
     */
    public function provideParseIngredient(): array
    {
        $data1 = new DataContainer([
            'type' => 'abc',
            'name' => 'Def',
            'amount' => 13.37,
        ]);
        $ingredient1 = new Ingredient();
        $ingredient1->setType('abc')
                    ->setName('def')
                    ->setAmount(13.37);

        $data2 = new DataContainer([
            'type' => 'abc',
            'name' => 'Def',
            'amount' => 0.,
        ]);

        return [
            [$data1, $ingredient1],
            [$data2, null]
        ];
    }

    /**
     * Tests the parseIngredient method.
     * @param DataContainer $ingredientData
     * @param Ingredient|null $expectedResult
     * @throws ReflectionException
     * @covers ::parseIngredient
     * @dataProvider provideParseIngredient
     */
    public function testParseIngredient(DataContainer $ingredientData, ?Ingredient $expectedResult): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $recipeRegistry, $translator);
        $result = $this->invokeMethod($parser, 'parseIngredient', $ingredientData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the parseProducts method.
     * @throws ReflectionException
     * @covers ::parseProducts
     */
    public function testParseProducts(): void
    {
        $recipeData = new DataContainer([
            'products' => [
                ['abc' => 'def'],
                ['ghi' => 'jkl'],
                ['mno' => 'pqr'],
            ]
        ]);
        
        /* @var Product|MockObject $product1 */
        $product1 = $this->getMockBuilder(Product::class)
                         ->setMethods(['setOrder'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $product1->expects($this->once())
                 ->method('setOrder')
                 ->with(1);
        
        /* @var Product|MockObject $product2 */
        $product2 = $this->getMockBuilder(Product::class)
                         ->setMethods(['setOrder'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $product2->expects($this->once())
                 ->method('setOrder')
                 ->with(2);

        $expectedResult = [$product1, $product2];

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['parseProduct'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(3))
               ->method('parseProduct')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['abc' => 'def']))],
                   [$this->equalTo(new DataContainer(['ghi' => 'jkl']))],
                   [$this->equalTo(new DataContainer(['mno' => 'pqr']))]
               )
               ->willReturnOnConsecutiveCalls(
                   $product1,
                   null,
                   $product2
               );

        $result = $this->invokeMethod($parser, 'parseProducts', $recipeData);
        $this->assertEquals($expectedResult, $result);
    }
    
    
    /**
     * Provides the data for the parseProduct test.
     * @return array
     */
    public function provideParseProduct(): array
    {
        $data1 = new DataContainer([
            'type' => 'abc',
            'name' => 'Def',
            'amountMin' => 2.1,
            'amountMax' => 2.7,
            'probability' => 0.42,
        ]);
        $product1 = new Product();
        $product1->setType('abc')
                 ->setName('def')
                 ->setAmountMin(2.1)
                 ->setAmountMax(2.7)
                 ->setProbability(0.42);

        $data2 = new DataContainer([
            'type' => 'abc',
            'name' => 'Def',
            'amountMin' => 2.1,
            'amountMax' => 2.7,
            'probability' => 0.,
        ]);

        return [
            [$data1, $product1],
            [$data2, null]
        ];
    }

    /**
     * Tests the parseProduct method.
     * @param DataContainer $productData
     * @param Product|null $expectedResult
     * @throws ReflectionException
     * @covers ::parseProduct
     * @dataProvider provideParseProduct
     */
    public function testParseProduct(DataContainer $productData, ?Product $expectedResult): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $recipeRegistry, $translator);
        $result = $this->invokeMethod($parser, 'parseProduct', $productData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the addTranslations method.
     * @throws ReflectionException
     * @covers ::addTranslations
     */
    public function testAddTranslations(): void
    {
        $labels = (new LocalisedString())->setTranslation('en', 'abc');
        $descriptions = (new LocalisedString())->setTranslation('en', 'def');

        $recipeData = new DataContainer([
            'localised' => [
                'name' => ['ghi'],
                'description' => ['jkl'],
            ]
        ]);

        /* @var Recipe|MockObject $recipe */
        $recipe = $this->getMockBuilder(Recipe::class)
                     ->setMethods(['getLabels', 'getDescriptions'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $recipe->expects($this->once())
             ->method('getLabels')
             ->willReturn($labels);
        $recipe->expects($this->once())
             ->method('getDescriptions')
             ->willReturn($descriptions);

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['addTranslationsToEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('addTranslationsToEntity')
                   ->withConsecutive(
                       [$labels, 'name', ['ghi'], null],
                       [$descriptions, 'description', ['jkl'], null]
                   );

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $parser = new RecipeParser($iconParser, $recipeRegistry, $translator);
        $this->invokeMethod($parser, 'addTranslations', $recipe, $recipeData);
    }

}
