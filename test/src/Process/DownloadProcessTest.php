<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @covers ::getMod
     * @covers ::getRelease
     * @covers ::getDownloadUrl
     * @covers ::getDestinationFile
     */
    public function testConstruct(): void
    {
        $downloadUrl = 'abc';
        $destinationFile = 'def';
        $expectedCommandLine = "'wget' '-o' '/dev/null' '-O' 'def' 'abc'";

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        /* @var Release&MockObject $release */
        $release = $this->createMock(Release::class);

        $process = new DownloadProcess($mod, $release, $downloadUrl, $destinationFile);

        $this->assertSame($mod, $process->getMod());
        $this->assertSame($release, $process->getRelease());
        $this->assertSame($downloadUrl, $process->getDownloadUrl());
        $this->assertSame($destinationFile, $process->getDestinationFile());
        $this->assertNull($process->getTimeout());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
    }
}
