<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Update;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Update\UpdateListCommand;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the UpdateListCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Update\UpdateListCommand
 */
class UpdateListCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);
        $this->assertSame($modFileManager, $this->extractProperty($command, 'modFileManager'));
        $this->assertSame($modReader, $this->extractProperty($command, 'modReader'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the getModsFromRegistry method.
     * @throws ReflectionException
     * @covers ::getModsFromRegistry
     */
    public function testGetModsFromRegistry(): void
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');
        $modNames = ['abc', 'def'];
        $expectedResult = [
            'abc' => $mod1,
            'def' => $mod2,
        ];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames', 'get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($modNames);
        $modRegistry->expects($this->exactly(2))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        $mod2
                    );

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);
        $result = $this->invokeMethod($command, 'getModsFromRegistry', $modRegistry);
        $this->assertEquals($expectedResult, $result);
    }
}
