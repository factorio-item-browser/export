<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Output;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ModListOutput;
use FactorioItemBrowser\Export\Output\ProcessOutput;
use FactorioItemBrowser\Export\Output\ProgressBar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * The PHPUnit test of the Console class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Output\Console
 */
class ConsoleTest extends TestCase
{
    use ReflectionTrait;

    /** @var ConsoleOutputInterface&MockObject */
    private ConsoleOutputInterface $output;
    private bool $isDebug = false;

    protected function setUp(): void
    {
        $this->output = $this->createMock(ConsoleOutputInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return Console&MockObject
     */
    private function createInstance(array $mockedMethods = []): Console
    {
        return $this->getMockBuilder(Console::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([$this->output, $this->isDebug])
                    ->getMock();
    }

    public function testWriteHeadline(): void
    {
        $message = 'abc';

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->stringContains($message));

        $instance = $this->createInstance();
        $result = $instance->writeHeadline($message);

        $this->assertSame($instance, $result);
    }

    public function testWriteStep(): void
    {
        $step = 'abc';

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->stringContains($step));

        $instance = $this->createInstance();
        $result = $instance->writeStep($step);

        $this->assertSame($instance, $result);
    }

    public function testWriteAction(): void
    {
        $action = 'abc';

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->stringContains($action));

        $instance = $this->createInstance();
        $result = $instance->writeAction($action);

        $this->assertSame($instance, $result);
    }

    public function testWriteMessage(): void
    {
        $message = 'abc';

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->stringContains($message));

        $instance = $this->createInstance();
        $result = $instance->writeMessage($message);

        $this->assertSame($instance, $result);
    }

    public function testWriteException(): void
    {
        $exception = new ExportException('abc');

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->logicalAnd(
                         $this->stringContains('abc'),
                         $this->stringContains('ExportException'),
                     ));

        $this->isDebug = false;

        $instance = $this->createInstance();
        $result = $instance->writeException($exception);

        $this->assertSame($instance, $result);
    }

    public function testWriteExceptionWithDebug(): void
    {
        $exception = new ExportException('abc');

        $this->output->expects($this->exactly(2))
                     ->method('writeln')
                     ->withConsecutive(
                         [$this->logicalAnd(
                             $this->stringContains('abc'),
                             $this->stringContains('ExportException'),
                         )],
                         [$this->stringContains($exception->getTraceAsString())],
                     );

        $this->isDebug = true;

        $instance = $this->createInstance();
        $result = $instance->writeException($exception);

        $this->assertSame($instance, $result);
    }

    public function testCreateModListOutput(): void
    {
        $section = $this->createMock(ConsoleSectionOutput::class);

        $this->output->expects($this->once())
                     ->method('section')
                     ->willReturn($section);

        $expectedResult = new ModListOutput($section);

        $instance = $this->createInstance();
        $result = $instance->createModListOutput();

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateProcessOutput(): void
    {
        $section = $this->createMock(ConsoleSectionOutput::class);

        $this->output->expects($this->once())
                     ->method('section')
                     ->willReturn($section);

        $expectedResult = new ProcessOutput($section);

        $instance = $this->createInstance();
        $result = $instance->createProcessOutput();

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateProgressBar(): void
    {
        $label = 'abc';
        $expectedResult = new ProgressBar($this->output, $label);

        $instance = $this->createInstance();
        $result = $instance->createProgressBar($label);

        $this->assertEquals($expectedResult, $result);
    }

    public function testIterateWithProgressbar(): void
    {
        $label = 'foo';
        $values = [
            'abc' => 'def',
            'ghi' => 'jkl',
            'mno' => 'pqr',
        ];

        $progressBar = $this->createMock(ProgressBar::class);
        $progressBar->expects($this->once())
                    ->method('setNumberOfSteps')
                    ->with($this->identicalTo(3));
        $progressBar->expects($this->exactly(3))
                    ->method('finish')
                    ->withConsecutive(
                        [$this->identicalTo('abc')],
                        [$this->identicalTo('ghi')],
                        [$this->identicalTo('mno')],
                    );

        $instance = $this->createInstance(['createProgressBar']);
        $instance->expects($this->once())
                 ->method('createProgressBar')
                 ->with($this->identicalTo($label))
                 ->willReturn($progressBar);

        $result = iterator_to_array($instance->iterateWithProgressbar($label, $values));
        $this->assertEquals($values, $result);
    }

    public function testCreateSection(): void
    {
        $section = $this->createMock(ConsoleSectionOutput::class);

        $this->output->expects($this->once())
                     ->method('section')
                     ->willReturn($section);

        $instance = $this->createInstance();
        $result = $instance->createSection();

        $this->assertEquals($section, $result);
    }
}
