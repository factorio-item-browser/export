<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\IconParser
 */
class IconParserTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $this->assertSame($iconRegistry, $this->extractProperty($parser, 'iconRegistry'));
    }

    /**
     * Tests the parse method.
     * @throws ReflectionException
     * @covers ::parse
     */
    public function testParse(): void
    {
        $icon1 = (new Icon())->setSize(12);
        $icon2 = (new Icon())->setSize(23);
        $icon3 = (new Icon())->setSize(34);
        $icon4 = (new Icon())->setSize(45);

        $dumpData = new DataContainer([
            'icons' => [
                ['name' => 'abc', 'type' => 'fluid'],
                ['name' => 'def', 'type' => 'item'],
                ['name' => 'ghi', 'type' => 'recipe'],
                ['name' => 'jkl', 'type' => 'foo'],
            ]
        ]);
        $expectedIcons = [
            'fluid|abc' => $icon1,
            'item|def' => $icon2,
            'recipe|ghi' => $icon3,
            'item|jkl' => $icon4,
            'machine|jkl' => $icon4,
        ];

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setIconHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setIconHashes')
                    ->with([]);

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['parseIcon', 'buildArrayKey'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('parseIcon')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['name' => 'abc', 'type' => 'fluid']))],
                   [$this->equalTo(new DataContainer(['name' => 'def', 'type' => 'item']))],
                   [$this->equalTo(new DataContainer(['name' => 'ghi', 'type' => 'recipe']))],
                   [$this->equalTo(new DataContainer(['name' => 'jkl', 'type' => 'foo']))]
               )
               ->willReturnOnConsecutiveCalls(
                   $icon1,
                   $icon2,
                   $icon3,
                   $icon4
               );
        $parser->expects($this->exactly(5))
               ->method('buildArrayKey')
               ->withConsecutive(
                   ['fluid', 'abc'],
                   ['item', 'def'],
                   ['recipe', 'ghi'],
                   ['item', 'jkl'],
                   ['machine', 'jkl']
               )
               ->willReturnOnConsecutiveCalls(
                   'fluid|abc',
                   'item|def',
                   'recipe|ghi',
                   'item|jkl',
                   'machine|jkl'
               );

        $parser->parse($combination, $dumpData);
        $this->assertEquals($expectedIcons, $this->extractProperty($parser, 'icons'));
    }

    /**
     * Tests the parseIcon method.
     * @throws ReflectionException
     * @covers ::parseIcon
     */
    public function testParseIcon(): void
    {
        $layer1 = new Layer();
        $layer1->setFileName('abc');
        $layer2 = new Layer();
        $layer2->setFileName('def');

        $iconData = new DataContainer([
            'icons' => [
                ['icon' => 'abc'],
                ['icon' => 'def'],
            ],
            'iconSize' => 42,
        ]);
        $expectedResult = new Icon();
        $expectedResult->setLayers([$layer1, $layer2])
                       ->setSize(42);

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['parseLayer'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('parseLayer')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['icon' => 'abc']))],
                   [$this->equalTo(new DataContainer(['icon' => 'def']))]
               )
               ->willReturnOnConsecutiveCalls(
                   $layer1,
                   $layer2
               );

        $result = $this->invokeMethod($parser, 'parseIcon', $iconData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the parseLayer method.
     * @throws ReflectionException
     * @covers ::parseLayer
     */
    public function testParseLayer(): void
    {
        $layerData = new DataContainer([
            'icon' => 'abc',
            'shift' => [
                '0' => 13,
                '1' => 37,
            ],
            'scale' => 4.2,
            'tint' => [
                'r' => 2.1,
                'g' => 3.2,
                'b' => 4.3,
                'a' => 5.4,
            ],
        ]);
        $expectedResult = new Layer();
        $expectedResult->setFileName('abc')
                       ->setOffsetX(13)
                       ->setOffsetY(37)
                       ->setScale(4.2);
        $expectedResult->getTintColor()->setRed(1.2)
                                       ->setGreen(2.3)
                                       ->setBlue(3.4)
                                       ->setAlpha(4.5);

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['convertColorValue'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('convertColorValue')
               ->withConsecutive(
                   [2.1],
                   [3.2],
                   [4.3],
                   [5.4]
               )
               ->willReturnOnConsecutiveCalls(
                   1.2,
                   2.3,
                   3.4,
                   4.5
               );

        $result = $this->invokeMethod($parser, 'parseLayer', $layerData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the convertColorValue test.
     * @return array
     */
    public function provideConvertColorValue(): array
    {
        return [
            [0., 0.],
            [0.25, 0.25],
            [1., 1.],
            [127., 127. / 255.],
            [255., 1.],
        ];
    }

    /**
     * Tests the convertColorValue method.
     * @param float $value
     * @param float $expectedResult
     * @throws ReflectionException
     * @covers ::convertColorValue
     * @dataProvider provideConvertColorValue
     */
    public function testConvertColorValue(float $value, float $expectedResult): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $result = $this->invokeMethod($parser, 'convertColorValue', $value);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getIconHashForEntity test.
     * @return array
     */
    public function provideGetIconHashForEntity(): array
    {
        return [
            [true, 'foo', 'foo'],
            [false, null, null],
        ];
    }

    /**
     * Tests the getIconHashForEntity method.
     * @param bool $withIcon
     * @param null|string $resultSet
     * @param null|string $expectedResult
     * @throws ReflectionException
     * @covers ::getIconHashForEntity
     * @dataProvider provideGetIconHashForEntity
     */
    public function testGetIconHashForEntity(bool $withIcon, ?string $resultSet, ?string $expectedResult): void
    {
        $type = 'abc';
        $name = 'def';
        $key = 'ghi';
        $icon = new Icon();

        $icons = $withIcon ? [$key => $icon] : [];

        /* @var EntityRegistry|MockObject $iconRegistry */
        $iconRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $iconRegistry->expects($resultSet === null ? $this->never() : $this->once())
                     ->method('set')
                     ->with($icon)
                     ->willReturn((string) $resultSet);

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['addIconHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($resultSet === null ? $this->never() : $this->once())
                    ->method('addIconHash')
                    ->with((string) $resultSet);

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['buildArrayKey'])
                       ->setConstructorArgs([$iconRegistry])
                       ->getMock();
        $parser->expects($this->once())
               ->method('buildArrayKey')
               ->with($type, $name)
               ->willReturn($key);
        $this->injectProperty($parser, 'icons', $icons);

        $result = $parser->getIconHashForEntity($combination, $type, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the buildArrayKey method.
     * @covers ::buildArrayKey
     * @throws ReflectionException
     */
    public function testBuildArrayKey(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = 'abc|def';

        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $result = $this->invokeMethod($parser, 'buildArrayKey', $type, $name);
        $this->assertSame($expectedResult, $result);
    }
}
