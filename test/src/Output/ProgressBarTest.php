<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Output;

use FactorioItemBrowser\Export\Output\ProgressBar;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * The PHPUnit test of the ProgressBar class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Output\ProgressBar
 */
class ProgressBarTest extends TestCase
{
    public function test(): void
    {
        $label = 'abc';

        $array = [];
        $realOutput = new ConsoleOutput();
        $progressSection = $this->getMockBuilder(ConsoleSectionOutput::class)
                                ->onlyMethods(['write'])
                                ->setConstructorArgs([
                                    $realOutput->getStream(),
                                    &$array,
                                    $realOutput->getVerbosity(),
                                    false,
                                    $realOutput->getFormatter(),
                                ])
                                ->getMock();

        $statusSection = $this->createMock(ConsoleSectionOutput::class);
        $statusSection->expects($this->exactly(7))
                      ->method('overwrite')
                      ->withConsecutive(
                          [$this->identicalTo([' foo'])],
                          [$this->identicalTo([' foo', ' bar'])],
                          [$this->identicalTo([' bar'])],
                          [$this->identicalTo([' bar', ' oof'])],
                          [$this->identicalTo([' baz', ' oof'])],
                          [$this->identicalTo([' oof'])],
                          [$this->identicalTo([])],
                      );

        $output = $this->createMock(ConsoleOutput::class);
        $output->expects($this->exactly(2))
               ->method('section')
               ->willReturnOnConsecutiveCalls(
                   $progressSection,
                   $statusSection,
               );

        $instance = new ProgressBar($output, $label);
        $result = $instance->setNumberOfSteps(3);
        $this->assertSame($instance, $result);
        $this->assertSame(3, $instance->getNumberOfSteps());
        $result = $instance->start('def', 'foo');
        $this->assertSame($instance, $result);
        $instance->start('ghi', 'bar');
        $result = $instance->finish('def');
        $this->assertSame($instance, $result);
        $instance->start('jkl', 'oof');
        $result = $instance->update('ghi', 'baz');
        $this->assertSame($instance, $result);
        $instance->finish('ghi');
        $instance->finish('jkl');
    }
}
