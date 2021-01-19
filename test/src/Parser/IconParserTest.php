<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
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
 * @covers \FactorioItemBrowser\Export\Parser\IconParser
 */
class IconParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $methods
     * @return IconParser&MockObject
     */
    private function createInstance(array $methods = []): IconParser
    {
        return $this->getMockBuilder(IconParser::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($methods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->hashCalculator,
                        $this->mapperManager,
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
        $instance->parse($dump, $exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
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

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo([$dumpIcon1, $dumpIcon2]))
                      ->willReturnCallback(fn () => yield from [$dumpIcon1, $dumpIcon2]);

        $instance = $this->createInstance(['isIconValid', 'createIcon', 'addParsedIcon']);
        $instance->expects($this->exactly(2))
                 ->method('isIconValid')
                 ->withConsecutive(
                     [$this->identicalTo($dumpIcon1)],
                     [$this->identicalTo($dumpIcon2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     true,
                     false
                 );
        $instance->expects($this->once())
                 ->method('createIcon')
                 ->with($this->identicalTo($dumpIcon1))
                 ->willReturn($mappedIcon);
        $instance->expects($this->once())
                 ->method('addParsedIcon')
                 ->with($this->identicalTo('abc'), $this->identicalTo('def'), $this->identicalTo($mappedIcon));

        $instance->prepare($dump);
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
     * @dataProvider provideIsIconValid
     */
    public function testIsIconValid(string $type, bool $expectedResult): void
    {
        $dumpIcon = new DumpIcon();
        $dumpIcon->type = $type;

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'isIconValid', $dumpIcon);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createIcon', $dumpIcon);

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
     * @dataProvider provideAddParsedIcon
     */
    public function testAddParsedIcon(
        array $parsedIcons,
        string $type,
        string $name,
        ExportIcon $icon,
        array $expectedParsedIcons
    ): void {
        $instance = $this->createInstance();
        $this->injectProperty($instance, 'parsedIcons', $parsedIcons);

        $this->invokeMethod($instance, 'addParsedIcon', $type, $name, $icon);

        $this->assertSame($expectedParsedIcons, $this->extractProperty($instance, 'parsedIcons'));
    }

    /**
     * @throws ExportException
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'usedIcons', $usedIcons);

        $instance->validate($exportData);
    }

    /**
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'parsedIcons', $parsedIcons);
        $this->injectProperty($instance, 'usedIcons', $usedIcons);

        $result = $instance->getIconId($type, $name);

        $this->assertSame($iconId, $result);
        $this->assertSame($expectedUsedIcons, $this->extractProperty($instance, 'usedIcons'));
    }

    /**
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'parsedIcons', $parsedIcons);
        $this->injectProperty($instance, 'usedIcons', $usedIcons);

        $result = $instance->getIconId($type, $name);

        $this->assertSame('', $result);
        $this->assertSame($usedIcons, $this->extractProperty($instance, 'usedIcons'));
    }
}
