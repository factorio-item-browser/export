<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;
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

    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hashCalculator = $this->createMock(HashCalculator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new IconParser($this->hashCalculator);

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
    }

    /**
     * Tests the prepare method.
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dumpIcon1 = new DumpIcon();
        $dumpIcon1->type = 'abc';
        $dumpIcon1->name = 'def';

        $dumpIcon2 = new DumpIcon();
        $dumpIcon2->type = 'ghi';
        $dumpIcon2->name = 'jkl';

        $mappedIcon = $this->createMock(ExportIcon::class);

        $dump = new Dump();
        $dump->icons = [$dumpIcon1, $dumpIcon2];

        $parser = $this->getMockBuilder(IconParser::class)
                       ->onlyMethods(['isIconValid', 'mapIcon', 'addParsedIcon'])
                       ->setConstructorArgs([$this->hashCalculator])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('isIconValid')
               ->withConsecutive(
                   [$this->identicalTo($dumpIcon1)],
                   [$this->identicalTo($dumpIcon2)]
               )
               ->willReturnOnConsecutiveCalls(
                   true,
                   false
               );
        $parser->expects($this->once())
               ->method('mapIcon')
               ->with($this->identicalTo($dumpIcon1))
               ->willReturn($mappedIcon);
        $parser->expects($this->once())
               ->method('addParsedIcon')
               ->with($this->identicalTo('abc'), $this->identicalTo('def'), $this->identicalTo($mappedIcon));

        $parser->prepare($dump);
    }

    /**
     * Provides the data for the isIconValid test.
     * @return array<mixed>
     */
    public function provideIsIconValid(): array
    {
        return [
            ['item', true],
            ['recipe', true],
            ['capsule', true],
            ['technology', false],
            ['tutorial', false],
        ];
    }

    /**
     * Tests the isIconValid method.
     * @param string $type
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isIconValid
     * @dataProvider provideIsIconValid
     */
    public function testIsIconValid(string $type, bool $expectedResult): void
    {
        $dumpIcon = new DumpIcon();
        $dumpIcon->type = $type;

        $parser = new IconParser($this->hashCalculator);
        $result = $this->invokeMethod($parser, 'isIconValid', $dumpIcon);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the mapIcon method.
     * @throws ReflectionException
     * @covers ::createIcon
     */
    public function testMapIcon(): void
    {
        $iconId = 'abc';

        $dumpLayer1 = $this->createMock(DumpLayer::class);
        $dumpLayer2 = $this->createMock(DumpLayer::class);
        $exportLayer1 = $this->createMock(ExportLayer::class);
        $exportLayer2 = $this->createMock(ExportLayer::class);

        $dumpIcon = new DumpIcon();
        $dumpIcon->layers = [$dumpLayer1, $dumpLayer2];

        $expectedIcon = new ExportIcon();
        $expectedIcon->setSize(64)
                     ->setLayers([$exportLayer1, $exportLayer2]);

        $expectedResult = new ExportIcon();
        $expectedResult->setSize(64)
                       ->setLayers([$exportLayer1, $exportLayer2])
                       ->setId($iconId);

        $this->hashCalculator->expects($this->once())
                             ->method('hashIcon')
                             ->with($this->equalTo($expectedIcon))
                             ->willReturn($iconId);

        $parser = $this->getMockBuilder(IconParser::class)
                       ->onlyMethods(['mapLayer'])
                       ->setConstructorArgs([$this->hashCalculator])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('mapLayer')
               ->withConsecutive(
                   [$this->identicalTo($dumpLayer1)],
                   [$this->identicalTo($dumpLayer2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportLayer1,
                   $exportLayer2
               );

        $result = $this->invokeMethod($parser, 'mapIcon', $dumpIcon);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapLayer method.
     * @throws ReflectionException
     * @covers ::mapLayer
     */
    public function testMapLayer(): void
    {
        $dumpLayer = new DumpLayer();
        $dumpLayer->file = 'abc';
        $dumpLayer->size = 1337;
        $dumpLayer->shiftX = 42;
        $dumpLayer->shiftY = 21;
        $dumpLayer->scale = 12.34;
        $dumpLayer->tintRed = 23.45;
        $dumpLayer->tintGreen = 34.56;
        $dumpLayer->tintBlue = 45.67;
        $dumpLayer->tintAlpha = 56.78;

        $expectedResult = new ExportLayer();
        $expectedResult->setFileName('abc')
                       ->setScale(12.34)
                       ->setSize(1337);
        $expectedResult->getOffset()->setX(42)
                                    ->setY(21);
        $expectedResult->getTint()->setRed(54.32)
                                  ->setGreen(65.43)
                                  ->setBlue(76.54)
                                  ->setAlpha(87.65);

        $parser = $this->getMockBuilder(IconParser::class)
                       ->onlyMethods(['convertColorValue'])
                       ->setConstructorArgs([$this->hashCalculator])
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('convertColorValue')
               ->withConsecutive(
                   [$this->identicalTo(23.45)],
                   [$this->identicalTo(34.56)],
                   [$this->identicalTo(45.67)],
                   [$this->identicalTo(56.78)]
               )
               ->willReturnOnConsecutiveCalls(
                   54.32,
                   65.43,
                   76.54,
                   87.65
               );

        $result = $this->invokeMethod($parser, 'mapLayer', $dumpLayer);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the convertColorValue test.
     * @return array<mixed>
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
        $parser = new IconParser($this->hashCalculator);
        $result = $this->invokeMethod($parser, 'convertColorValue', $value);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the addParsedIcon test.
     * @return array<mixed>
     */
    public function provideAddParsedIcon(): array
    {
        $icon1 = $this->createMock(ExportIcon::class);
        $icon2 = $this->createMock(ExportIcon::class);
        $icon3 = $this->createMock(ExportIcon::class);

        return [
            [
                [],
                'fluid',
                'foo',
                $icon1,
                ['fluid' => ['foo' => $icon1]],
            ],
            [
                ['fluid' => ['foo' => $icon1]],
                'item',
                'bar',
                $icon2,
                [
                    'fluid' => ['foo' => $icon1],
                    'item' => ['bar' => $icon2],
                ],
            ],
            [
                ['recipe' => ['foo' => $icon1]],
                'recipe',
                'bar',
                $icon2,
                ['recipe' => ['foo' => $icon1, 'bar' => $icon2]],
            ],
            [
                [],
                'foo',
                'bar',
                $icon1,
                [
                    'item' => ['bar' => $icon1],
                    'machine' => ['bar' => $icon1],
                ],
            ],
            [
                [
                    'item' => ['bar' => $icon1],
                    'machine' => ['bar' => $icon2],
                ],
                'foo',
                'bar',
                $icon3,
                [
                    'item' => ['bar' => $icon1],
                    'machine' => ['bar' => $icon2],
                ],
            ],
        ];
    }

    /**
     * Tests the addParsedIcon method.
     * @param array|ExportIcon[][] $parsedIcons
     * @param string $type
     * @param string $name
     * @param ExportIcon $icon
     * @param array|ExportIcon[][] $expectedParsedIcons
     * @throws ReflectionException
     * @covers ::addParsedIcon
     * @dataProvider provideAddParsedIcon
     */
    public function testAddParsedIcon(
        array $parsedIcons,
        string $type,
        string $name,
        ExportIcon $icon,
        array $expectedParsedIcons
    ): void {
        $parser = new IconParser($this->hashCalculator);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);

        $this->invokeMethod($parser, 'addParsedIcon', $type, $name, $icon);

        $this->assertSame($expectedParsedIcons, $this->extractProperty($parser, 'parsedIcons'));
    }

    /**
     * Tests the parse method.
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        $dump = $this->createMock(Dump::class);
        $combination = $this->createMock(Combination::class);

        $parser = new IconParser($this->hashCalculator);
        $parser->parse($dump, $combination);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the validate method.
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $icon1 = $this->createMock(ExportIcon::class);
        $icon2 = $this->createMock(ExportIcon::class);
        $icon3 = $this->createMock(ExportIcon::class);
        $icon4 = $this->createMock(ExportIcon::class);

        $usedIcons = [$icon3, $icon4];

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getIcons')
                    ->willReturn([$icon1, $icon2]);
        $combination->expects($this->once())
                    ->method('setIcons')
                    ->with($this->equalTo([$icon1, $icon2, $icon3, $icon4]));

        $parser = new IconParser($this->hashCalculator);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $parser->validate($combination);
    }

    /**
     * Tests the getIconId method.
     * @throws ReflectionException
     * @covers ::getIconId
     */
    public function testGetIconId(): void
    {
        $type = 'abc';
        $name = 'def';
        $iconId = 'ghi';

        $icon1 = new ExportIcon();
        $icon1->setId($iconId);

        $icon2 = $this->createMock(ExportIcon::class);

        $parsedIcons = [
            'abc' => ['def' => $icon1],
        ];
        $usedIcons = ['foo' => $icon2];
        $expectedUsedIcons = ['foo' => $icon2, $iconId => $icon1];

        $parser = new IconParser($this->hashCalculator);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $result = $parser->getIconId($type, $name);

        $this->assertSame($iconId, $result);
        $this->assertSame($expectedUsedIcons, $this->extractProperty($parser, 'usedIcons'));
    }

    /**
     * Tests the getIconId method.
     * @throws ReflectionException
     * @covers ::getIconId
     */
    public function testGetIconIdWithoutMatch(): void
    {
        $type = 'abc';
        $name = 'def';

        $parsedIcons = [];
        $usedIcons = [
            $this->createMock(ExportIcon::class),
            $this->createMock(ExportIcon::class),
        ];

        $parser = new IconParser($this->hashCalculator);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $result = $parser->getIconId($type, $name);

        $this->assertSame('', $result);
        $this->assertSame($usedIcons, $this->extractProperty($parser, 'usedIcons'));
    }
}
