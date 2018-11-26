<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Console;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Console\Console;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * The PHPUnit test of the Console class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Console\Console
 */
class ConsoleTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var AdapterInterface $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);

        $console = new Console($consoleAdapter);

        $this->assertSame($consoleAdapter, $this->extractProperty($console, 'consoleAdapter'));
    }

    /**
     * Tests the write method.
     * @covers ::write
     */
    public function testWrite(): void
    {
        $message = 'abc';
        $color = 42;

        /* @var AdapterInterface|MockObject $consoleAdapter */
        $consoleAdapter = $this->getMockBuilder(AdapterInterface::class)
                               ->setMethods(['write'])
                               ->getMockForAbstractClass();
        $consoleAdapter->expects($this->once())
                       ->method('write')
                       ->with($message, $color);

        $console = new Console($consoleAdapter);
        $result = $console->write($message, $color);

        $this->assertSame($console, $result);
    }

    /**
     * Tests the writeLine method.
     * @covers ::writeLine
     */
    public function testWriteLine(): void
    {
        $message = 'abc';
        $color = 42;

        /* @var AdapterInterface|MockObject $consoleAdapter */
        $consoleAdapter = $this->getMockBuilder(AdapterInterface::class)
                               ->setMethods(['writeLine'])
                               ->getMockForAbstractClass();
        $consoleAdapter->expects($this->once())
                       ->method('writeLine')
                       ->with($message, $color);

        $console = new Console($consoleAdapter);
        $result = $console->writeLine($message, $color);

        $this->assertSame($console, $result);
    }

    /**
     * Tests the writeCommand method.
     * @covers ::writeCommand
     */
    public function testWriteCommand(): void
    {
        $command = 'abc';
        $expectedMessage = '$ abc';

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeLine'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $console->expects($this->once())
                ->method('writeLine')
                ->with($expectedMessage, ColorInterface::GRAY)
                ->willReturnSelf();

        $result = $console->writeCommand($command);
        $this->assertSame($console, $result);
    }

    /**
     * Tests the writeBanner method.
     * @covers ::writeBanner
     */
    public function testWriteBanner(): void
    {
        $message = 'abc';
        $color = 42;
        $expectedMessage = ' abc';

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeHorizontalLine', 'writeLine'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $console->expects($this->exactly(2))
                ->method('writeHorizontalLine')
                ->with('-', $color)
                ->willReturnSelf();
        $console->expects($this->once())
                ->method('writeLine')
                ->with($expectedMessage, $color)
                ->willReturnSelf();

        $result = $console->writeBanner($message, $color);
        $this->assertSame($console, $result);
    }

    /**
     * Tests the writeHorizontalLine method.
     * @covers ::writeHorizontalLine
     */
    public function testWriteHorizontalLine(): void
    {
        $character = '#';
        $color = 42;
        $width = 10;
        $expectedMessage = '##########';

        /* @var AdapterInterface|MockObject $consoleAdapter */
        $consoleAdapter = $this->getMockBuilder(AdapterInterface::class)
                               ->setMethods(['getWidth'])
                               ->getMockForAbstractClass();
        $consoleAdapter->expects($this->once())
                       ->method('getWidth')
                       ->willReturn($width);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeLine'])
                        ->setConstructorArgs([$consoleAdapter])
                        ->getMock();
        $console->expects($this->once())
                ->method('writeLine')
                ->with($expectedMessage, $color)
                ->willReturnSelf();

        $result = $console->writeHorizontalLine($character, $color);
        $this->assertSame($console, $result);
    }

    /**
     * Provides the data for the formatModName test.
     * @return array
     */
    public function provideFormatModName(): array
    {
        return [

            ['abc', '', '                                                             abc'],
            ['abc-def-ghi-jkl', '', '                                                 abc-def-ghi-jkl'],
            ['abc', 'def', '                                                             abcdef'],
        ];
    }

    /**
     * Tests the formatModName method.
     * @param string $modName
     * @param string $suffix
     * @param string $expectedResult
     * @covers ::formatModName
     * @dataProvider provideFormatModName
     */
    public function testFormatModName(string $modName, string $suffix, string $expectedResult): void
    {
        /* @var AdapterInterface $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);
        $console = new Console($consoleAdapter);

        $result = $console->formatModName($modName, $suffix);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the formatVersion test.
     * @return array
     */
    public function provideFormatVersion(): array
    {
        return [
            ['1.2.3', false, '1.2.3     '],
            ['1.2.3', true, '     1.2.3'],
            ['1', false, '1.0.0     '],
            ['', false, '          ']
        ];
    }
    /**
     * Tests the formatVersion method.
     * @param string $version
     * @param bool $padLeft
     * @param string $expectedResult
     * @covers ::formatVersion
     * @dataProvider provideFormatVersion
     */
    public function testFormatVersion(string $version, bool $padLeft, string $expectedResult): void
    {
        /* @var AdapterInterface $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);
        $console = new Console($consoleAdapter);

        $result = $console->formatVersion($version, $padLeft);
        $this->assertSame($expectedResult, $result);
    }
}
