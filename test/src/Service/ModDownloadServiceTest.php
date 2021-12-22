<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Service;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\FactorioModPortalClient\Response\ModListResponse;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\MissingModsException;
use FactorioItemBrowser\Export\Exception\NoValidReleaseException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ModListOutput;
use FactorioItemBrowser\Export\Process\ModDownloadProcessManager;
use FactorioItemBrowser\Export\Service\ModDownloadService;
use FactorioItemBrowser\Export\Service\ModFileService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModDownloadService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Service\ModDownloadService
 */
class ModDownloadServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var ModDownloadProcessManager&MockObject */
    private ModDownloadProcessManager $modDownloadProcessManager;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;
    /** @var Facade&MockObject */
    private Facade $modPortalClientFacade;
    private Version $factorioVersion;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->modDownloadProcessManager = $this->createMock(ModDownloadProcessManager::class);
        $this->modFileService = $this->createMock(ModFileService::class);
        $this->modPortalClientFacade = $this->createMock(Facade::class);

        $this->factorioVersion = new Version('1.2.3');
    }

    /**
     * @param array<string> $mockedMethods
     * @return ModDownloadService&MockObject
     */
    private function createInstance(array $mockedMethods = [], bool $mockBaseInfo = true): ModDownloadService
    {
        if ($mockBaseInfo) {
            $info = new InfoJson();
            $info->version = $this->factorioVersion;

            $this->modFileService->expects($this->once())
                                 ->method('getInfo')
                                 ->with($this->identicalTo('base'))
                                 ->willReturn($info);
        }

        return $this->getMockBuilder(ModDownloadService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->modDownloadProcessManager,
                        $this->modFileService,
                        $this->modPortalClientFacade,
                    ])
                    ->getMock();
    }

    /**
     * @throws ExportException
     */
    public function testDownload(): void
    {
        $modNames = ['abc', 'def', 'foo'];
        $filteredModNames = ['abc', 'def'];
        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $mods = [
            'abc' => $mod1,
            'def' => $mod2,
        ];
        $currentVersions = [
            'abc' => new Version('2.3.4'),
            'def' => new Version('3.4.5'),
        ];
        $release1 = $this->createMock(Release::class);
        $release2 = $this->createMock(Release::class);
        $releases = [
            'abc' => $release1,
            'def' => $release2,
        ];

        $this->modDownloadProcessManager->expects($this->exactly(2))
                                        ->method('add')
                                        ->withConsecutive(
                                            [$this->identicalTo($mod1), $this->identicalTo($release1)],
                                            [$this->identicalTo($mod2), $this->identicalTo($release2)],
                                        );
        $this->modDownloadProcessManager->expects($this->once())
                                        ->method('wait');

        $instance = $this->createInstance([
            'filterModNames',
            'getCurrentVersions',
            'fetchMetaData',
            'getReleases',
            'printModList',
        ]);
        $instance->expects($this->once())
                 ->method('filterModNames')
                 ->with($this->identicalTo($modNames))
                 ->willReturn($filteredModNames);
        $instance->expects($this->once())
                 ->method('getCurrentVersions')
                 ->with($this->identicalTo($filteredModNames))
                 ->willReturn($currentVersions);
        $instance->expects($this->once())
                 ->method('fetchMetaData')
                 ->with($this->identicalTo($filteredModNames))
                 ->willReturn($mods);
        $instance->expects($this->once())
                 ->method('getReleases')
                 ->with($this->identicalTo($mods), $this->identicalTo($currentVersions))
                 ->willReturn($releases);
        $instance->expects($this->once())
                 ->method('printModList')
                 ->with($this->identicalTo($mods), $this->identicalTo($currentVersions), $this->identicalTo($releases));

        $instance->download($modNames);
    }

    /**
     * @throws ExportException
     */
    public function testDownloadWithoutMods(): void
    {
        $modNames = ['foo'];
        $filteredModNames = [];

        $this->modDownloadProcessManager->expects($this->never())
                                        ->method('add');
        $this->modDownloadProcessManager->expects($this->never())
                                        ->method('wait');

        $instance = $this->createInstance([
            'filterModNames',
            'getCurrentVersions',
            'fetchMetaData',
            'getReleases',
            'printModList',
        ]);
        $instance->expects($this->once())
                 ->method('filterModNames')
                 ->with($this->identicalTo($modNames))
                 ->willReturn($filteredModNames);
        $instance->expects($this->never())
                 ->method('getCurrentVersions');
        $instance->expects($this->never())
                 ->method('fetchMetaData');
        $instance->expects($this->never())
                 ->method('getReleases');
        $instance->expects($this->never())
                 ->method('printModList');

        $instance->download($modNames);
    }

    /**
     * @throws ReflectionException
     */
    public function testFilterModNames(): void
    {
        $modNames = ['abc', 'foo', 'def'];
        $expectedResult = ['abc', 'def'];

        $this->modFileService->expects($this->exactly(3))
            ->method('isVanillaMod')
            ->withConsecutive(
                [$this->identicalTo('abc')],
                [$this->identicalTo('foo')],
                [$this->identicalTo('def')],
            )
            ->willReturnOnConsecutiveCalls(
                false,
                true,
                false,
            );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'filterModNames', $modNames);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetCurrentVersions(): void
    {
        $modNames = ['abc', 'def', 'ghi'];
        $expectedResult = [
            'abc' => new Version('2.3.4'),
            'def' => null,
            'ghi' => new Version('3.4.5'),
        ];

        $infoBase = new InfoJson();
        $infoBase->version = $this->factorioVersion;
        $info1 = new InfoJson();
        $info1->version = new Version('2.3.4');
        $info2 = new InfoJson();
        $info2->version = new Version('3.4.5');

        $this->modFileService->expects($this->exactly(4))
                             ->method('getInfo')
                             ->withConsecutive(
                                 [$this->identicalTo('base')],
                                 [$this->identicalTo('abc')],
                                 [$this->identicalTo('def')],
                                 [$this->identicalTo('ghi')],
                             )
                             ->willReturnOnConsecutiveCalls(
                                 $infoBase,
                                 $info1,
                                 $this->throwException(new FileNotFoundInModException('foo', 'bar')),
                                 $info2,
                             );

        $instance = $this->createInstance([], false);
        $result = $this->invokeMethod($instance, 'getCurrentVersions', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchMetaData(): void
    {
        $modNames = ['base', 'abc', 'def'];

        $mod1 = new Mod();
        $mod1->setName('abc');
        $mod2 = new Mod();
        $mod2->setName('def');

        $modPortalResponse = new ModListResponse();
        $modPortalResponse->setResults([$mod1, $mod2]);

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList($modNames)
                        ->setPageSize(3);
        $expectedResult = [
            'abc' => $mod1,
            'def' => $mod2,
        ];

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getModList')
                                    ->with($this->equalTo($expectedRequest))
                                    ->willReturn($modPortalResponse);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchMetaData', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchMetaDataWithMissingMod(): void
    {
        $modNames = ['base', 'abc', 'def'];

        $mod1 = new Mod();
        $mod1->setName('abc');

        $modPortalResponse = new ModListResponse();
        $modPortalResponse->setResults([$mod1]);

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList($modNames)
                        ->setPageSize(3);

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getModList')
                                    ->with($this->equalTo($expectedRequest))
                                    ->willReturn($modPortalResponse);

        $this->expectException(MissingModsException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchMetaData', $modNames);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchMetaDataWithClientException(): void
    {
        $modNames = ['base', 'abc', 'def'];

        $mod1 = new Mod();
        $mod1->setName('abc');

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList($modNames)
                        ->setPageSize(3);

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getModList')
                                    ->with($this->equalTo($expectedRequest))
                                    ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(InternalException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchMetaData', $modNames);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetReleases(): void
    {
        $version1 = new Version('2.3.4');
        $release1 = new Release();
        $release1->setVersion(new Version('3.4.5'));
        $release1->getInfoJson()->setFactorioVersion($this->factorioVersion);
        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setReleases([$release1]);

        $version2 = new Version('4.5.6');
        $release2 = new Release();
        $release2->setVersion(new Version('4.5.6'));
        $release2->getInfoJson()->setFactorioVersion($this->factorioVersion);
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setReleases([$release2]);

        $release3 = new Release();
        $release3->setVersion(new Version('5.6.7'));
        $release3->getInfoJson()->setFactorioVersion($this->factorioVersion);
        $mod3 = new Mod();
        $mod3->setName('ghi')
             ->setReleases([$release3]);

        $mods = [
            'abc' => $mod1,
            'def' => $mod2,
            'ghi' => $mod3,
        ];
        $currentVersions = [
            'abc' => $version1,
            'def' => $version2,
        ];
        $expectedResult = [
            'abc' => $release1,
            'def' => null,
            'ghi' => $release3,
        ];

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getReleases', $mods, $currentVersions);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetReleasesWithMissingRelease(): void
    {
        $version1 = new Version('2.3.4');
        $release1 = new Release();
        $release1->setVersion(new Version('3.4.5'));
        $release1->getInfoJson()->setFactorioVersion($this->factorioVersion);
        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setReleases([$release1]);

        $mod2 = new Mod();
        $mod2->setName('def');

        $mods = [
            'abc' => $mod1,
            'def' => $mod2,
        ];
        $currentVersions = [
            'abc' => $version1,
        ];

        $this->expectException(NoValidReleaseException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'getReleases', $mods, $currentVersions);
    }

    /**
     * @throws ReflectionException
     */
    public function testPrintModList(): void
    {
        $mod1 = new Mod();
        $mod1->setName('abc');
        $release1 = new Release();
        $release1->setVersion(new Version('2.3.4'));

        $mod2 = new Mod();
        $mod2->setName('def');

        $mods = [
            'abc' => $mod1,
            'def' => $mod2,
        ];
        $currentVersions = [
            'abc' => new Version('3.4.5'),
        ];
        $releases = [
            'abc' => $release1,
        ];

        $modListOutput = $this->createMock(ModListOutput::class);
        $modListOutput->expects($this->exactly(2))
                      ->method('add')
                      ->withConsecutive(
                          [
                              $this->identicalTo('abc'),
                              $this->equalTo(new Version('3.4.5')),
                              $this->equalTo(new Version('2.3.4')),
                          ],
                          [
                              $this->identicalTo('def'),
                              $this->isNull(),
                              $this->isNull(),
                          ],
                      );
        $modListOutput->expects($this->once())
                      ->method('render');

        $this->console->expects($this->once())
                      ->method('createModListOutput')
                      ->willReturn($modListOutput);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'printModList', $mods, $currentVersions, $releases);
    }
}
