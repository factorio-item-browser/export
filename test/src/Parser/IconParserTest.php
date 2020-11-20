<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\ExportData;
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
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new IconParser($this->hashCalculator, $this->mapperManager);

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
        $this->assertSame($this->mapperManager, $this->extractProperty($parser, 'mapperManager'));
    }

    /**
     * @throws ExportException
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
                       ->onlyMethods(['isIconValid', 'createIcon', 'addParsedIcon'])
                       ->setConstructorArgs([$this->hashCalculator, $this->mapperManager])
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
               ->method('createIcon')
               ->with($this->identicalTo($dumpIcon1))
               ->willReturn($mappedIcon);
        $parser->expects($this->once())
               ->method('addParsedIcon')
               ->with($this->identicalTo('abc'), $this->identicalTo('def'), $this->identicalTo($mappedIcon));

        $parser->prepare($dump);
    }

    /**
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

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $result = $this->invokeMethod($parser, 'isIconValid', $dumpIcon);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::createIcon
     */
    public function testCreateIcon(): void
    {
        $iconId = 'abc';

        $dumpIcon = new DumpIcon();
        $dumpIcon->name = 'def';

        $exportIcon = new ExportIcon();
        $exportIcon->size = 42;

        $expectedResult = new ExportIcon();
        $expectedResult->size = 42;
        $expectedResult->id = $iconId;

        $this->hashCalculator->expects($this->once())
                             ->method('hashIcon')
                             ->with($this->identicalTo($exportIcon))
                             ->willReturn($iconId);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($dumpIcon), $this->isInstanceOf(ExportIcon::class))
                            ->willReturn($exportIcon);

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $result = $this->invokeMethod($parser, 'createIcon', $dumpIcon);

        $this->assertEquals($expectedResult, $result);
    }

    /**
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
     * @param array<string, array<string, ExportIcon>> $parsedIcons
     * @param string $type
     * @param string $name
     * @param ExportIcon $icon
     * @param array<string, array<string, ExportIcon>> $expectedParsedIcons
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
        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);

        $this->invokeMethod($parser, 'addParsedIcon', $type, $name, $icon);

        $this->assertSame($expectedParsedIcons, $this->extractProperty($parser, 'parsedIcons'));
    }

    /**
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        $dump = $this->createMock(Dump::class);
        $exportData = $this->createMock(ExportData::class);

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $parser->parse($dump, $exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $icon1 = $this->createMock(ExportIcon::class);
        $icon2 = $this->createMock(ExportIcon::class);

        $usedIcons = [$icon1, $icon2];

        $icons = $this->createMock(ChunkedCollection::class);
        $icons->expects($this->exactly(2))
              ->method('add')
              ->withConsecutive(
                  [$this->identicalTo($icon1)],
                  [$this->identicalTo($icon2)],
              );

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getIcons')
                   ->willReturn($icons);

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $parser->validate($exportData);
    }

    /**
     * @throws ReflectionException
     * @covers ::getIconId
     */
    public function testGetIconId(): void
    {
        $type = 'abc';
        $name = 'def';
        $iconId = 'ghi';

        $icon1 = new ExportIcon();
        $icon1->id = $iconId;

        $icon2 = new ExportIcon();
        $icon2->id = 'jkl';

        $parsedIcons = [
            'abc' => ['def' => $icon1],
        ];
        $usedIcons = ['foo' => $icon2];
        $expectedUsedIcons = ['foo' => $icon2, $iconId => $icon1];

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $result = $parser->getIconId($type, $name);

        $this->assertSame($iconId, $result);
        $this->assertSame($expectedUsedIcons, $this->extractProperty($parser, 'usedIcons'));
    }

    /**
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

        $parser = new IconParser($this->hashCalculator, $this->mapperManager);
        $this->injectProperty($parser, 'parsedIcons', $parsedIcons);
        $this->injectProperty($parser, 'usedIcons', $usedIcons);

        $result = $parser->getIconId($type, $name);

        $this->assertSame('', $result);
        $this->assertSame($usedIcons, $this->extractProperty($parser, 'usedIcons'));
    }
}
