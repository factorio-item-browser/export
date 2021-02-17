<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Output;

use FactorioItemBrowser\Export\Output\ProcessOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * The PHPUnit test of the ProcessOutput class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Output\ProcessOutput
 */
class ProcessOutputTest extends TestCase
{
    public function test(): void
    {
        $output = $this->createMock(ConsoleSectionOutput::class);
        $output->expects($this->any())
               ->method('overwrite')
               ->withConsecutive(
                   [$this->containsEqual('abc')],
                   [$this->logicalAnd(
                       $this->containsEqual('abc'),
                       $this->containsEqual('def'),
                   )],
               );


        $instance = new ProcessOutput($output);

        $result = $instance->addLine('abc');
        $this->assertSame($instance, $result);
        $instance->addLine('def');
    }
}
