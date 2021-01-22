<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use FactorioItemBrowser\Export\Process\ModDownloadProcess;
use FactorioItemBrowser\Export\Process\ModDownloadProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModDownloadProcessFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Process\ModDownloadProcessFactory
 */
class ModDownloadProcessFactoryTest extends TestCase
{
    public function test(): void
    {
        $tempDirectory = 'foo';

        $mod = new Mod();
        $mod->setName('abc');
        $release = new Release();
        $release->setDownloadUrl('def')
                ->setFileName('ghi');

        $modPortalClientFacade = $this->createMock(Facade::class);
        $modPortalClientFacade->expects($this->once())
                              ->method('getDownloadUrl')
                              ->with($this->identicalTo('def'))
                              ->willReturn('jkl');

        $expectedResult = new ModDownloadProcess($mod, $release, 'jkl', 'foo/ghi');

        $instance = new ModDownloadProcessFactory($modPortalClientFacade, $tempDirectory);
        $result = $instance->create($mod, $release);

        $this->assertEquals($expectedResult, $result);
    }
}
