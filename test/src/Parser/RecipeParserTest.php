<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use FactorioItemBrowser\ExportData\Entity\Item;
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
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->assertSame($iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($itemParser, $this->extractProperty($parser, 'itemParser'));
        $this->assertSame($recipeRegistry, $this->extractProperty($parser, 'recipeRegistry'));
        $this->assertSame($translator, $this->extractProperty($parser, 'translator'));
    }

    /**
     * Tests the reset method.
     * @throws ReflectionException
     * @covers ::reset
     */
    public function testReset(): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->injectProperty($parser, 'parsedRecipes', ['fail' => new Recipe()]);

        $parser->reset();

        $this->assertSame([], $this->extractProperty($parser, 'parsedRecipes'));
    }

    /**
     * Tests the parse method.
     * @throws ReflectionException
     * @covers ::parse
     */
    public function testParse(): void
    {
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
        
        $recipe1 = new Recipe();
        $recipe1->setName('abc')
                ->setMode('def');
        $recipe2 = new Recipe();
        $recipe2->setName('ghi')
                ->setMode('jkl');
        $recipe3 = new Recipe();
        $recipe3->setName('mno')
                ->setMode('pqr');
        $recipe4 = new Recipe();
        $recipe4->setName('stu')
                ->setMode('vwx');
        
        $expectedParsedRecipes = [
            'abc|def' => $recipe1,
            'ghi|jkl' => $recipe2,
            'mno|pqr' => $recipe3,
            'stu|vwx' => $recipe4,
        ];

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['parseRecipe'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('parseRecipe')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['abc' => 'def'])), 'normal'],
                   [$this->equalTo(new DataContainer(['ghi' => 'jkl'])), 'normal'],
                   [$this->equalTo(new DataContainer(['mno' => 'pqr'])), 'expensive'],
                   [$this->equalTo(new DataContainer(['stu' => 'vwx'])), 'expensive']
               )
               ->willReturnOnConsecutiveCalls(
                   $recipe1,
                   $recipe2,
                   $recipe3,
                   $recipe4
               );

        $parser->parse($dumpData);
        $this->assertEquals($expectedParsedRecipes, $this->extractProperty($parser, 'parsedRecipes'));
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
                       ->setMethods(['parseIngredients', 'parseProducts', 'addTranslations'])
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
        $parser->expects($this->once())
               ->method('addTranslations')
               ->with($this->equalTo($expectedResult), $recipeData);

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
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
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
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
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
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->invokeMethod($parser, 'addTranslations', $recipe, $recipeData);
    }

    /**
     * Tests the check method.
     * @throws ReflectionException
     * @covers ::check
     */
    public function testCheck(): void
    {
        $recipe1 = (new Recipe())->setName('abc');
        $recipe2 = (new Recipe())->setName('def');
        $parsedRecipes = [$recipe1, $recipe2];

        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['checkIcon', 'checkTranslation'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('checkIcon')
               ->withConsecutive(
                   [$recipe1],
                   [$recipe2]
               );
        $parser->expects($this->exactly(2))
               ->method('checkTranslation')
               ->withConsecutive(
                   [$recipe1],
                   [$recipe2]
               );

        $this->injectProperty($parser, 'parsedRecipes', $parsedRecipes);
        $parser->check();
    }

    /**
     * Provides the data for the isUniqueRecipe test.
     * @return array
     */
    public function provideIsUniqueRecipe(): array
    {
        $recipe1 = new Recipe();
        $recipe1->setName('abc')
                ->setMode(RecipeMode::NORMAL);

        $recipe2 = new Recipe();
        $recipe2->setName('abc')
                ->setMode(RecipeMode::EXPENSIVE);

        return [
            [$recipe1, false, null, [], true],
            [$recipe2, true, null, [], true],
            [$recipe2, true, $recipe1, ['def', 'ghi'], true],
            [$recipe2, true, $recipe1, ['def', 'def'], false],
        ];
    }

    /**
     * Tests the isUniqueRecipe method.
     * @param Recipe $recipe
     * @param bool $expectFindRecipe
     * @param Recipe|null $resultFindRecipe
     * @param array $hashes
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isUniqueRecipe
     * @dataProvider provideIsUniqueRecipe
     */
    public function testIsUniqueRecipe(
        Recipe $recipe,
        bool $expectFindRecipe,
        ?Recipe $resultFindRecipe,
        array $hashes,
        bool $expectedResult
    ): void {
        /* @var RecipeParser|MockObject $parser */
        $parser = $this->getMockBuilder(RecipeParser::class)
                       ->setMethods(['findRecipeWithMode', 'calculateRecipeDataHash'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($expectFindRecipe ? $this->once() : $this->never())
               ->method('findRecipeWithMode')
               ->with($recipe, RecipeMode::NORMAL)
               ->willReturn($resultFindRecipe);
        $parser->expects($this->exactly(count($hashes)))
               ->method('calculateRecipeDataHash')
               ->withConsecutive(
                   [$recipe],
                   [$resultFindRecipe]
               )
               ->willReturnOnConsecutiveCalls(...$hashes);


        $result = $this->invokeMethod($parser, 'isUniqueRecipe', $recipe);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the findRecipeWithMode test.
     * @return array
     */
    public function provideFindRecipeWithMode(): array
    {
        $recipe1 = new Recipe();
        $recipe1->setName('abc')
                ->setMode('def');

        $recipe2 = new Recipe();
        $recipe2->setName('abc')
                ->setMode('jkl');

        $recipe3 = new Recipe();
        $recipe3->setName('mno')
                ->setMode('def');

        $parsedRecipes = [
            $recipe1->getIdentifier() => $recipe1,
            $recipe2->getIdentifier() => $recipe2,
            $recipe3->getIdentifier() => $recipe3,
        ];

        return [
            [$parsedRecipes, $recipe1, 'jkl', $recipe2],
            [$parsedRecipes, $recipe3, 'jkl', null],
        ];
    }

    /**
     * Tests the findRecipeWithMode method.
     * @param array $parsedRecipes
     * @param Recipe $recipe
     * @param string $mode
     * @param Recipe|null $expectedResult
     * @throws ReflectionException
     * @covers ::findRecipeWithMode
     * @dataProvider provideFindRecipeWithMode
     */
    public function testFindRecipeWithMode(
        array $parsedRecipes,
        Recipe $recipe,
        string $mode,
        ?Recipe $expectedResult
    ): void {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->injectProperty($parser, 'parsedRecipes', $parsedRecipes);

        $result = $this->invokeMethod($parser, 'findRecipeWithMode', $recipe, $mode);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the calculateRecipeDataHash method.
     * @throws ReflectionException
     * @covers ::calculateRecipeDataHash
     */
    public function testCalculateRecipeDataHash(): void
    {
        $recipe = new Recipe();
        $recipe->setName('abc')
               ->setMode('def')
               ->addIngredient(new Ingredient())
               ->setCraftingTime(13.37);

        $clonedRecipe = clone($recipe);
        $expectedResult = 'f8732bee53e0642f';

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);

        $result = $this->invokeMethod($parser, 'calculateRecipeDataHash', $recipe);
        $this->assertSame($expectedResult, $result);
        $this->assertEquals($clonedRecipe, $recipe);
    }
    
    /**
     * Provides the data for the checkIcon test.
     * @return array
     */
    public function provideCheckIcon(): array
    {
        return [
            [true, false, 'abc', null, 'abc'],
            [true, true, null, 'abc', 'abc'],
            [false, false, null, null, null],
        ];
    }

    /**
     * Tests the checkIcon method.
     * @param bool $withProducts
     * @param bool $expectSecondHash
     * @param null|string $resultHash1
     * @param null|string $resultHash2
     * @param null|string $expectedHash
     * @throws ReflectionException
     * @covers ::checkIcon
     * @dataProvider provideCheckIcon
     */
    public function testCheckIcon(
        bool $withProducts,
        bool $expectSecondHash,
        ?string $resultHash1,
        ?string $resultHash2,
        ?string $expectedHash
    ): void {
        $name = 'abc';
        $productType = 'def';
        $productName = 'ghi';

        $products = [];
        if ($withProducts) {
            $product = new Product();
            $product->setType($productType)
                    ->setName($productName);
            $products = [
                $product,
                new Product(),
            ];
        }

        /* @var Recipe|MockObject $recipe */
        $recipe = $this->getMockBuilder(Recipe::class)
                       ->setMethods(['getName', 'getProducts', 'setIconHash'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $recipe->expects($this->once())
               ->method('getName')
               ->willReturn($name);
        $recipe->expects($this->once())
               ->method('getProducts')
               ->willReturn($products);
        $recipe->expects($expectedHash === null ? $this->never() : $this->once())
               ->method('setIconHash')
               ->with($expectedHash);

        /* @var IconParser|MockObject $iconParser */
        $iconParser = $this->getMockBuilder(IconParser::class)
                           ->setMethods(['getIconHashForEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $iconParser->expects($this->exactly($expectSecondHash ? 2 : 1))
                   ->method('getIconHashForEntity')
                   ->withConsecutive(
                       ['recipe', $name],
                       [$productType, $productName]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $resultHash1,
                       $resultHash2
                   );

        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->invokeMethod($parser, 'checkIcon', $recipe);
    }

    /**
     * Tests the checkTranslation method.
     * @throws ReflectionException
     * @covers ::checkTranslation
     */
    public function testCheckTranslation(): void
    {
        $recipeName = 'foo';

        /* @var Item|MockObject $item1 */
        $item1 = $this->getMockBuilder(Item::class)
                      ->setMethods(['setProvidesRecipeLocalisation'])
                      ->getMock();
        $item1->expects($this->never())
              ->method('setProvidesRecipeLocalisation');
        $item1->getLabels()->setTranslation('en', 'ghi');

        /* @var Item|MockObject $item2 */
        $item2 = $this->getMockBuilder(Item::class)
                      ->setMethods(['setProvidesRecipeLocalisation'])
                      ->getMock();
        $item2->expects($this->once())
              ->method('setProvidesRecipeLocalisation')
              ->with(true);  
        $item2->getLabels()->setTranslation('en', 'abc');
        $item2->getDescriptions()->setTranslation('en', 'def');

        $recipe = new Recipe();
        $recipe->setName($recipeName);
        $recipe->getLabels()->setTranslation('en', 'abc');
        $recipe->getDescriptions()->setTranslation('en', 'def');

        $expectedRecipe = new Recipe();
        $expectedRecipe->setName($recipeName);

        /* @var ItemParser|MockObject $itemParser */
        $itemParser = $this->getMockBuilder(ItemParser::class)
                           ->setMethods(['getItemsWithName'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $itemParser->expects($this->once())
                   ->method('getItemsWithName')
                   ->with($recipeName)
                   ->willReturn([$item1, $item2]);

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);

        $this->invokeMethod($parser, 'checkTranslation', $recipe);
    }
    
    /**
     * Tests the persist method.
     * @throws ReflectionException
     * @covers ::persist
     */
    public function testPersist(): void
    {
        $recipe1 = (new Recipe())->setName('abc');
        $recipe2 = (new Recipe())->setName('def');
        $parsedRecipes = [$recipe1, $recipe2];
        $recipeHash1 = 'ghi';
        $recipeHash2 = 'jkl';
        $expectedRecipeHashes = [$recipeHash1, $recipeHash2];

        /* @var EntityRegistry|MockObject $recipeRegistry */
        $recipeRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $recipeRegistry->expects($this->exactly(2))
                     ->method('set')
                     ->withConsecutive(
                         [$recipe1],
                         [$recipe2]
                     )
                     ->willReturnOnConsecutiveCalls(
                         $recipeHash1,
                         $recipeHash2
                     );

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setRecipeHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setRecipeHashes')
                    ->with($expectedRecipeHashes);

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var ItemParser $itemParser */
        $itemParser = $this->createMock(ItemParser::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new RecipeParser($iconParser, $itemParser, $recipeRegistry, $translator);
        $this->injectProperty($parser, 'parsedRecipes', $parsedRecipes);

        $parser->persist($combination);
    }
}
