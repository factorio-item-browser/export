<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use FactorioItemBrowser\Export\Process\DownloadProcess;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DownloadProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\DownloadProcess
 */
class DownloadProcessTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getDownloadUrl
     * @covers ::getDestinationFile
     */
    public function testConstruct(): void
    {
        $downloadUrl = 'abc';
        $destinationFile = 'def';
        $expectedCommandLine = "'wget' '-o' '/dev/null' '-O' 'def' 'abc'";

        $process = new DownloadProcess($downloadUrl, $destinationFile);

        $this->assertSame($downloadUrl, $process->getDownloadUrl());
        $this->assertSame($destinationFile, $process->getDestinationFile());
        $this->assertNull($process->getTimeout());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
    }
}
