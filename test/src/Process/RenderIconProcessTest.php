<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\ExportData\Entity\Icon;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RenderIconProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\RenderIconProcess
 */
class RenderIconProcessTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getIcon
     */
    public function testConstruct(): void
    {
        $commandLine = ['abc', 'def'];
        $env = ['ghi' => 'jkl'];
        $expectedCommandLine = "'abc' 'def'";

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        $process = new RenderIconProcess($icon, $commandLine, $env);

        $this->assertSame($icon, $process->getIcon());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
        $this->assertSame($env, $process->getEnv());
        $this->assertNull($process->getTimeout());
    }
}
