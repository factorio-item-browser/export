<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Output;

use FactorioItemBrowser\Export\Output\ConsoleOutputFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * The PHPUnit test of the ConsoleOutputFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Output\ConsoleOutputFactory
 */
class ConsoleOutputFactoryTest extends TestCase
{
    public function test(): void
    {
        $instance = new ConsoleOutputFactory();
        $result = $instance($this->createMock(ContainerInterface::class), ConsoleOutput::class);

        $this->assertSame(ConsoleOutput::VERBOSITY_NORMAL, $result->getVerbosity());
    }
}
