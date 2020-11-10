<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use Exception;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DownloadFailedException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\DownloadFailedException
 */
class DownloadFailedExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $message = 'abc';
        $modName = 'def';
        $releaseVersion = new Version('1.2.3');
        $expectedMessage = 'Download of mod def (1.2.3) failed: abc';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getName')
            ->willReturn($modName);

        /* @var Release&MockObject $release */
        $release = $this->createMock(Release::class);
        $release->expects($this->once())
                ->method('getVersion')
                ->willReturn($releaseVersion);

        $exception = new DownloadFailedException($mod, $release, $message, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
