<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use ArrayIterator;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\DataProcessor\IconAssigner;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;
use FactorioItemBrowser\ExportData\Entity\Technology;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Helper\HashCalculator;
use FactorioItemBrowser\ExportData\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconAssigner class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\IconAssigner
 */
class IconAssignerTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->hashCalculator = $this->createMock(HashCalculator::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return IconAssigner&MockObject
     */
    private function createInstance(array $mockedMethods = []): IconAssigner
    {
        return $this->getMockBuilder(IconAssigner::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->hashCalculator,
                    ])
                    ->getMock();
    }

    public function testProcess(): void
    {
        $expectedCount = 10;

        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);
        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);
        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $recipe1 = $this->createMock(Recipe::class);
        $recipe2 = $this->createMock(Recipe::class);
        $technology1 = $this->createMock(Technology::class);
        $technology2 = $this->createMock(Technology::class);

        $exportData = new ExportData($this->createMock(Storage::class), 'test');
        $exportData->getItems()->add($item1)
                               ->add($item2);
        $exportData->getMachines()->add($machine1)
                                  ->add($machine2);
        $exportData->getMods()->add($mod1)
                              ->add($mod2);
        $exportData->getRecipes()->add($recipe1)
                                 ->add($recipe2);
        $exportData->getTechnologies()->add($technology1)
                                      ->add($technology2);

        $progressBar = $this->createMock(ProgressBar::class);
        $progressBar->expects($this->once())
                    ->method('setNumberOfSteps')
                    ->with($this->identicalTo($expectedCount));
        $progressBar->expects($this->exactly($expectedCount))
                    ->method('finish');

        $this->console->expects($this->once())
                      ->method('createProgressBar')
                      ->with($this->isType('string'))
                      ->willReturn($progressBar);

        $instance = $this->createInstance(['prepareIcons', 'processEntity']);
        $instance->expects($this->once())
                 ->method('prepareIcons')
                 ->with($this->identicalTo($exportData));
        $instance->expects($this->exactly(10))
                 ->method('processEntity')
                 ->withConsecutive(
                     [$this->identicalTo($item1)],
                     [$this->identicalTo($item2)],
                     [$this->identicalTo($machine1)],
                     [$this->identicalTo($machine2)],
                     [$this->identicalTo($mod1)],
                     [$this->identicalTo($mod2)],
                     [$this->identicalTo($recipe1)],
                     [$this->identicalTo($recipe2)],
                     [$this->identicalTo($technology1)],
                     [$this->identicalTo($technology2)],
                 );

        $instance->process($exportData);
    }

    /**
     * @return array<mixed>
     */
    public function provideProcessEntity(): array
    {
        $item = new Item();
        $item->type = 'abc';
        $item->name = 'def';
        $expectedItem = new Item();
        $expectedItem->type = 'abc';
        $expectedItem->name = 'def';
        $expectedItem->iconId = 'foo';

        $machine = new Machine();
        $machine->name = 'abc';
        $expectedMachine = new Machine();
        $expectedMachine->name = 'abc';
        $expectedMachine->iconId = 'foo';

        $mod = new Mod();
        $mod->name = 'abc';
        $expectedMod = new Mod();
        $expectedMod->name = 'abc';
        $expectedMod->iconId = 'foo';

        $product1 = new Product();
        $product1->type = 'def';
        $product1->name = 'ghi';
        $product2 = new Product();
        $product2->type = 'jkl';
        $product2->name = 'mno';

        $recipe1 = new Recipe();
        $recipe1->name = 'abc';
        $recipe1->products = [$product1, $product2];
        $expectedRecipe1 = new Recipe();
        $expectedRecipe1->name = 'abc';
        $expectedRecipe1->products = [$product1, $product2];
        $expectedRecipe1->iconId = 'foo';

        $recipe2 = new Recipe();
        $recipe2->name = 'abc';

        $technology = new Technology();
        $technology->name = 'abc';
        $expectedTechnology = new Technology();
        $expectedTechnology->name = 'abc';
        $expectedTechnology->iconId = 'foo';

        return [
            [$item, [['abc', 'def', 'foo']], $expectedItem],
            [$machine, [['machine', 'abc', 'foo']], $expectedMachine],
            [$mod, [['mod', 'abc', 'foo']], $expectedMod],
            [$recipe1, [['recipe', 'abc', 'foo']], $expectedRecipe1],
            [$recipe1, [['recipe', 'abc', ''], ['def', 'ghi', 'foo']], $expectedRecipe1],
            [$recipe2, [['recipe', 'abc', '']], $recipe2],
            [$technology, [['technology', 'abc', 'foo']], $expectedTechnology],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testPrepareIcons(): void
    {
        $createIcon = function (string $type, string $name): Icon {
            $icon = new Icon();
            $icon->type = $type;
            $icon->name = $name;
            return $icon;
        };

        $icon1 = $createIcon('fluid', 'abc');
        $icon2 = $createIcon('item', 'def');
        $icon3 = $createIcon('mod', 'ghi');
        $icon4 = $createIcon('recipe', 'jkl');
        $icon5 = $createIcon('resource', 'mno');
        $icon6 = $createIcon('technology', 'pqr');
        $icon7 = $createIcon('tutorial', 'stu');
        $icon8 = $createIcon('foo', 'vwx');
        $icon9 = $createIcon('bar', 'def');

        $expectedIcons = [
            'fluid' => [
                'abc' => $icon1,
            ],
            'item' => [
                'def' => $icon2,
                'vwx' => $icon8,
            ],
            'machine' => [
                'vwx' => $icon8,
                'def' => $icon9,
            ],
            'mod' => [
                'ghi' => $icon3,
            ],
            'recipe' => [
                'jkl' => $icon4,
            ],
            'resource' => [
                'mno' => $icon5,
            ],
            'technology' => [
                'pqr' => $icon6,
            ],
            'tutorial' => [
                'stu' => $icon7,
            ],
        ];

        $icons = $this->createMock(ChunkedCollection::class);
        $icons->expects($this->any())
              ->method('getIterator')
              ->willReturn(new ArrayIterator([$icon1, $icon2, $icon3, $icon4, $icon5, $icon6, $icon7, $icon8, $icon9]));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getIcons')
                   ->willReturn($icons);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'prepareIcons', $exportData);

        $this->assertEquals($expectedIcons, $this->extractProperty($instance, 'icons'));
    }

    /**
     * @param array<array<mixed>> $getIconIdMap
     * @throws ReflectionException
     * @dataProvider provideProcessEntity
     */
    public function testProcessEntity(object $entity, array $getIconIdMap, object $expectedEntity): void
    {
        $instance = $this->createInstance(['getIconId']);
        $instance->expects($this->any())
                 ->method('getIconId')
                 ->willReturnMap($getIconIdMap);

        $this->invokeMethod($instance, 'processEntity', $entity);

        $this->assertEquals($expectedEntity, $entity);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIconIdWithExistingId(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = 'ghi';

        $icon = new Icon();
        $icon->id = 'ghi';

        $icons = [
            'abc' => [
                'def' => $icon,
                'foo' => $this->createMock(Icon::class),
            ]
        ];

        $this->hashCalculator->expects($this->never())
                             ->method('hashEntity');

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'icons', $icons);
        $result = $this->invokeMethod($instance, 'getIconId', $type, $name);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIconIdWithMissingId(): void
    {
        $type = 'abc';
        $name = 'def';
        $iconHash = 'ghi';
        $icon = new Icon();

        $expectedIcon = new Icon();
        $expectedIcon->id = 'ghi';

        $icons = [
            'abc' => [
                'def' => $icon,
                'foo' => $this->createMock(Icon::class),
            ]
        ];

        $this->hashCalculator->expects($this->once())
                             ->method('hashEntity')
                             ->with($this->identicalTo($icon))
                             ->willReturn($iconHash);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'icons', $icons);
        $result = $this->invokeMethod($instance, 'getIconId', $type, $name);

        $this->assertSame($iconHash, $result);
        $this->assertEquals($expectedIcon, $icon);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIconIdWithUnknownTypeAndName(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = '';

        $icons = [
            'abc' => [
                'foo' => $this->createMock(Icon::class),
            ]
        ];

        $this->hashCalculator->expects($this->never())
                             ->method('hashEntity');

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'icons', $icons);
        $result = $this->invokeMethod($instance, 'getIconId', $type, $name);

        $this->assertSame($expectedResult, $result);
    }
}
