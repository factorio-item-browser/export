<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the AbstractCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\AbstractCommand
 */
class AbstractCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            [null, null, null, 0],
            [new CommandException('abc', 42), 'abc', ColorInterface::YELLOW, 42],
            [new ExportException('abc', 42), 'abc', ColorInterface::RED, 500],
        ];
    }

    /**
     * Tests the invoking.
     * @param Exception|null $thrownException
     * @param string|null $expectedBannerMessage
     * @param int|null $expectedBannerColor
     * @param int $expectedResult
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(
        ?Exception $thrownException,
        ?string $expectedBannerMessage,
        ?int $expectedBannerColor,
        int $expectedResult
    ): void {
        /* @var Route $route */
        $route = $this->createMock(Route::class);
        /* @var AdapterInterface $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeBanner'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($expectedBannerMessage === null ? $this->never() : $this->once())
                ->method('writeBanner')
                ->with($expectedBannerMessage, $expectedBannerColor)
                ->willReturnSelf();

        /* @var AbstractCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractCommand::class)
                        ->setMethods(['createConsole', 'execute'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $command->expects($this->once())
                ->method('createConsole')
                ->with($consoleAdapter)
                ->willReturn($console);
        if ($thrownException !== null) {
            $command->expects($this->once())
                    ->method('execute')
                    ->with($route)
                    ->willThrowException($thrownException);
        } else {
            $command->expects($this->once())
                    ->method('execute')
                    ->with($route);
        }

        $result = $command($route, $consoleAdapter);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createConsole method.
     * @throws ReflectionException
     * @covers ::createConsole
     */
    public function testCreateConsole(): void
    {
        /* @var AdapterInterface $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);

        /* @var AbstractCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractCommand::class)
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($command, 'createConsole', $consoleAdapter);
        $this->assertInstanceOf(Console::class, $result);
    }
}
