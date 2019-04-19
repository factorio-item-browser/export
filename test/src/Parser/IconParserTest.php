<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Constant\Config;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Utils\EntityUtils;
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
     * @covers ::check
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $this->assertSame($iconRegistry, $this->extractProperty($parser, 'iconRegistry'));

        $parser->check(); // Empty method
    }

    /**
     * Tests the reset method.
     * @throws ReflectionException
     * @covers ::reset
     */
    public function testReset(): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $this->injectProperty($parser, 'parsedIcons', ['fail' => new Icon()]);
        $this->injectProperty($parser, 'usedIcons', ['fail' => new Icon()]);

        $parser->reset();

        $this->assertSame([], $this->extractProperty($parser, 'parsedIcons'));
        $this->assertSame([], $this->extractProperty($parser, 'usedIcons'));
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        $icon1 = (new Icon())->setSize(12);
        $icon2 = (new Icon())->setSize(23);
        $icon3 = (new Icon())->setSize(34);
        $icon4 = (new Icon())->setSize(45);
        $icon5 = (new Icon())->setSize(56);
        $icon6 = (new Icon())->setSize(67);

        $dumpData = new DataContainer([
            'icons' => [
                ['name' => 'abc', 'type' => 'fluid'],
                ['name' => 'def', 'type' => 'item'],
                ['name' => 'ghi', 'type' => 'recipe'],
                ['name' => 'jkl', 'type' => 'technology'],
                ['name' => 'mno', 'type' => 'tutorial'],
                ['name' => 'pqr', 'type' => 'foo'],
            ]
        ]);

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['parseIcon', 'addParsedIcon'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(6))
               ->method('parseIcon')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['name' => 'abc', 'type' => 'fluid']))],
                   [$this->equalTo(new DataContainer(['name' => 'def', 'type' => 'item']))],
                   [$this->equalTo(new DataContainer(['name' => 'ghi', 'type' => 'recipe']))],
                   [$this->equalTo(new DataContainer(['name' => 'jkl', 'type' => 'technology']))],
                   [$this->equalTo(new DataContainer(['name' => 'mno', 'type' => 'tutorial']))],
                   [$this->equalTo(new DataContainer(['name' => 'pqr', 'type' => 'foo']))]
               )
               ->willReturnOnConsecutiveCalls(
                   $icon1,
                   $icon2,
                   $icon3,
                   $icon4,
                   $icon5,
                   $icon6
               );
        $parser->expects($this->exactly(5))
               ->method('addParsedIcon')
               ->withConsecutive(
                   ['fluid', 'abc', $icon1, true],
                   ['item', 'def', $icon2, true],
                   ['recipe', 'ghi', $icon3, true],
                   ['item', 'pqr', $icon6, false],
                   ['machine', 'pqr', $icon6, false]
               );

        $parser->parse($dumpData);
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
                       ->setSize(42)
                       ->setRenderedSize(Config::ICON_SIZE);

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
     * Provides the data for the addParsedIcon test.
     * @return array
     */
    public function provideAddParsedIcon(): array
    {
        $icon1 = (new Icon())->setSize(12);
        $icon2 = (new Icon())->setSize(23);
        $icon3 = (new Icon())->setSize(34);

        return [
            [
                ['abc' => $icon1, 'def' => $icon2],
                'ghi',
                $icon3,
                false,
                ['abc' => $icon1, 'def' => $icon2, 'ghi' => $icon3],
            ],
            [
                ['abc' => $icon1, 'def' => $icon2],
                'ghi',
                $icon3,
                true,
                ['abc' => $icon1, 'def' => $icon2, 'ghi' => $icon3],
            ],
            [
                ['abc' => $icon1, 'def' => $icon2],
                'abc',
                $icon3,
                false,
                ['abc' => $icon1, 'def' => $icon2],
            ],
            [
                ['abc' => $icon1, 'def' => $icon2],
                'abc',
                $icon3,
                true,
                ['abc' => $icon3, 'def' => $icon2],
            ],
        ];
    }

    /**
     * Tests the addParsedIcon method.
     * @param array|Icon[] $parsedIcons
     * @param string $key
     * @param Icon $icon
     * @param bool $overwriteExistingIcon
     * @param array|Icon[] $expectedParsedIcons
     * @throws ReflectionException
     * @covers ::addParsedIcon
     * @dataProvider provideAddParsedIcon
     */
    public function testAddParsedIcon(
        array $parsedIcons,
        string $key,
        Icon $icon,
        bool $overwriteExistingIcon,
        array $expectedParsedIcons
    ): void {
        $type = 'foo';
        $name = 'bar';

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['buildArrayKey'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('buildArrayKey')
               ->with($type, $name)
               ->willReturn($key);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);

        $this->invokeMethod($parser, 'addParsedIcon', $type, $name, $icon, $overwriteExistingIcon);
        $this->assertEquals($expectedParsedIcons, $this->extractProperty($parser, 'parsedIcons'));
    }


    /**
     * Tests the persist method.
     * @throws ReflectionException
     * @covers ::persist
     */
    public function testPersist(): void
    {
        $icon1 = (new Icon())->setSize(42);
        $icon2 = (new Icon())->setSize(21);
        $usedIcons = [
            'abc' => $icon1,
            'def' => $icon2,
        ];

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setIconHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setIconHashes')
                    ->with(['abc', 'def']);

        /* @var EntityRegistry|MockObject $iconRegistry */
        $iconRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $iconRegistry->expects($this->exactly(2))
                     ->method('set')
                     ->withConsecutive(
                         [$icon1],
                         [$icon2]
                     )
                     ->willReturnOnConsecutiveCalls(
                         'abc',
                         'def'
                     );

        $parser = new IconParser($iconRegistry);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $parser->persist($combination);
    }

    /**
     * Provides the data for the getIconHashForEntity test.
     * @return array
     */
    public function provideGetIconHashForEntity(): array
    {
        /* @var Icon|MockObject $icon1 */
        $icon1 = $this->getMockBuilder(Icon::class)
                      ->setMethods(['calculateHash'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $icon1->expects($this->once())
              ->method('calculateHash')
              ->willReturn('abc');

        $icon2 = (new Icon())->setSize(42);
        $icon3 = (new Icon())->setSize(21);


        return [
            [
                ['def' => $icon1],
                ['ghi' => $icon2],
                'def',
                'abc',
                ['ghi' => $icon2, 'abc' => $icon1],
            ],
            [
                ['def' => $icon2],
                ['ghi' => $icon3],
                'jkl',
                null,
                ['ghi' => $icon3],
            ],
        ];
    }

    /**
     * Tests the getIconHashForEntity method.
     * @param array|Icon[] $parsedIcons
     * @param array|Icon[] $usedIcons
     * @param string $arrayKey
     * @param null|string $expectedResult
     * @param array|Icon[] $expectedUsedIcons
     * @throws ReflectionException
     * @covers ::getIconHashForEntity
     * @dataProvider provideGetIconHashForEntity
     */
    public function testGetIconHashForEntity(
        array $parsedIcons,
        array $usedIcons,
        string $arrayKey,
        ?string $expectedResult,
        array $expectedUsedIcons
    ): void {
        $type = 'foo';
        $name = 'bar';

        /* @var IconParser|MockObject $parser */
        $parser = $this->getMockBuilder(IconParser::class)
                       ->setMethods(['buildArrayKey'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('buildArrayKey')
               ->with($type, $name)
               ->willReturn($arrayKey);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $result = $this->invokeMethod($parser, 'getIconHashForEntity', $type, $name);
        $this->assertSame($expectedResult, $result);
        $this->assertEquals($expectedUsedIcons, $this->extractProperty($parser, 'usedIcons'));
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
        $expectedResult = EntityUtils::buildIdentifier(['abc', 'def']);

        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        $parser = new IconParser($iconRegistry);
        $result = $this->invokeMethod($parser, 'buildArrayKey', $type, $name);
        $this->assertSame($expectedResult, $result);
    }
}
