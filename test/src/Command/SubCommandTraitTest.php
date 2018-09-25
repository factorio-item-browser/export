<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Process\CommandProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Process\Process;
use Zend\Console\Adapter\AdapterInterface;

/**
 * The PHPUnit test of the SubCommandTrait class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\SubCommandTrait
 */
class SubCommandTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the runCommand method.
     * @throws ReflectionException
     * @covers ::runCommand
     */
    public function testRunCommand(): void
    {
        $commandName = 'abc';
        $parameters = ['def'];
        $exitCode = 42;

        /* @var AdapterInterface $console */
        $console = $this->createMock(AdapterInterface::class);

        /* @var Process|MockObject $process */
        $process = $this->getMockBuilder(Process::class)
                        ->setMethods(['run', 'getExitCode'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->expects($this->once())
                ->method('run');
        $process->expects($this->once())
                ->method('getExitCode')
                ->willReturn($exitCode);

        /* @var SubCommandTrait|MockObject $trait */
        $trait = $this->getMockBuilder(SubCommandTrait::class)
                      ->setMethods(['createCommandProcess'])
                      ->getMockForTrait();
        $trait->expects($this->once())
              ->method('createCommandProcess')
              ->with($commandName, $parameters, $console)
              ->willReturn($process);

        $result = $this->invokeMethod($trait, 'runCommand', $commandName, $parameters, $console);
        $this->assertSame($exitCode, $result);
    }

    /**
     * Tests the createCommandProcess method.
     * @throws ReflectionException
     * @covers ::createCommandProcess
     */
    public function testCreateCommandProcess(): void
    {
        $commandName = 'abc';
        $parameters = ['def'];

        /* @var AdapterInterface $console */
        $console = $this->createMock(AdapterInterface::class);

        /* @var SubCommandTrait|MockObject $trait */
        $trait = $this->getMockBuilder(SubCommandTrait::class)
                      ->getMockForTrait();

        $result = $this->invokeMethod($trait, 'createCommandProcess', $commandName, $parameters, $console);
        $this->assertInstanceOf(CommandProcess::class, $result);
    }
}
