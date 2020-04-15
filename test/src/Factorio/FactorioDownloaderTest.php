<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Factorio\FactorioDownloader;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the FactorioDownloader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\FactorioDownloader
 */
class FactorioDownloaderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked file system.
     * @var Filesystem&MockObject
     */
    protected $fileSystem;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->fileSystem = $this->createMock(Filesystem::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factorioDirectory = 'abc';
        $factorioDownloadUsername = 'def';
        $factorioDownloadToken = 'ghi';
        $tempDirectory = 'jkl';

        $downloader = new FactorioDownloader(
            $this->console,
            $this->fileSystem,
            $factorioDirectory,
            $factorioDownloadUsername,
            $factorioDownloadToken,
            $tempDirectory
        );

        $this->assertSame($this->console, $this->extractProperty($downloader, 'console'));
        $this->assertSame($this->fileSystem, $this->extractProperty($downloader, 'fileSystem'));
        $this->assertSame($factorioDirectory, $this->extractProperty($downloader, 'factorioDirectory'));
        $this->assertSame($factorioDownloadUsername, $this->extractProperty($downloader, 'factorioDownloadUsername'));
        $this->assertSame($factorioDownloadToken, $this->extractProperty($downloader, 'factorioDownloadToken'));
        $this->assertSame($tempDirectory, $this->extractProperty($downloader, 'tempDirectory'));
    }

    /**
     * Tests the download method.
     * @covers ::download
     */
    public function testDownload(): void
    {
        $tempDirectory = 'abc';
        $version = '1.2.3';

        $expectedHeadlessArchive = 'abc/headless.tar.xz';
        $expectedHeadlessDirectory = 'abc/headless';
        $expectedFullArchive = 'abc/full.tar.xz';
        $expectedFullDirectory = 'abc/full';

        /* @var DownloadProcess&MockObject $headlessProcess */
        $headlessProcess = $this->createMock(DownloadProcess::class);
        $headlessProcess->expects($this->once())
                        ->method('start');
        $headlessProcess->expects($this->once())
                        ->method('wait');

        /* @var DownloadProcess&MockObject $fullProcess */
        $fullProcess = $this->createMock(DownloadProcess::class);
        $fullProcess->expects($this->once())
                    ->method('start');
        $fullProcess->expects($this->once())
                    ->method('wait');

        /* @var Process&MockObject $extractProcess1 */
        $extractProcess1 = $this->createMock(Process::class);
        $extractProcess1->expects($this->once())
                        ->method('run');

        /* @var Process&MockObject $extractProcess2 */
        $extractProcess2 = $this->createMock(Process::class);
        $extractProcess2->expects($this->once())
                        ->method('run');

        $this->console->expects($this->once())
                      ->method('writeHeadline')
                      ->with($this->identicalTo('Downloading and installing Factorio version 1.2.3'));
        $this->console->expects($this->exactly(9))
                      ->method('writeAction')
                      ->with($this->isType('string'));

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo([
                             $expectedHeadlessDirectory,
                             $expectedFullDirectory,
                             $expectedHeadlessArchive,
                             $expectedFullArchive,
                         ]));

        /* @var FactorioDownloader&MockObject $downloader */
        $downloader = $this->getMockBuilder(FactorioDownloader::class)
                           ->onlyMethods([
                               'createDownloadProcess',
                               'createExtractArchiveProcess',
                               'patchHeadless',
                               'replaceOldVersion',
                           ])
                           ->setConstructorArgs([
                               $this->console,
                               $this->fileSystem,
                               'foo',
                               'bar',
                               'baz',
                               $tempDirectory,
                           ])
                           ->getMock();
        $downloader->expects($this->exactly(2))
                   ->method('createDownloadProcess')
                   ->withConsecutive(
                       [
                           $this->identicalTo('headless'),
                           $this->identicalTo($version),
                           $this->identicalTo($expectedHeadlessArchive),
                       ],
                       [
                           $this->identicalTo('alpha'),
                           $this->identicalTo($version),
                           $this->identicalTo($expectedFullArchive),
                       ]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $headlessProcess,
                       $fullProcess
                   );
        $downloader->expects($this->exactly(2))
                   ->method('createExtractArchiveProcess')
                   ->withConsecutive(
                       [$this->identicalTo($expectedHeadlessArchive), $this->identicalTo($expectedHeadlessDirectory)],
                       [$this->identicalTo($expectedFullArchive), $this->identicalTo($expectedFullDirectory)]
                   )
                   ->willReturn(
                       $extractProcess1,
                       $extractProcess2
                   );
        $downloader->expects($this->once())
                   ->method('patchHeadless')
                   ->with($this->identicalTo($expectedHeadlessDirectory), $this->identicalTo($expectedFullDirectory));
        $downloader->expects($this->once())
                   ->method('replaceOldVersion')
                   ->with($this->identicalTo($expectedHeadlessDirectory));

        $downloader->download($version);
    }

    /**
     * Tests the createDownloadProcess method.
     * @throws ReflectionException
     * @covers ::createDownloadProcess
     */
    public function testCreateDownloadProcess(): void
    {
        $variant = 'abc';
        $version = '1.2.3';
        $destinationFile = 'def';
        $factorioDownloadUsername = 'ghi';
        $factorioDownloadToken = 'jkl';

        $expectedDownloadUrl = 'https://www.factorio.com/get-download/1.2.3/abc/linux64?username=ghi&token=jkl';
        $expectedResult = new DownloadProcess($expectedDownloadUrl, 'def');

        $downloader = new FactorioDownloader(
            $this->console,
            $this->fileSystem,
            'foo',
            $factorioDownloadUsername,
            $factorioDownloadToken,
            'bar'
        );
        $result = $this->invokeMethod($downloader, 'createDownloadProcess', $variant, $version, $destinationFile);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createExtractArchiveProcess method.
     * @throws ReflectionException
     * @covers ::createExtractArchiveProcess
     */
    public function testCreateExtractArchiveProcess(): void
    {
        $archiveFile = 'abc';
        $directory = 'def';

        $expectedResult = new Process(['tar', '-xf', 'abc', '-C', 'def']);

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo($directory));
        $this->fileSystem->expects($this->once())
                         ->method('mkdir')
                         ->with($this->identicalTo($directory));

        $downloader = new FactorioDownloader($this->console, $this->fileSystem, 'foo', 'bar', 'baz', 'oof');
        $result = $this->invokeMethod($downloader, 'createExtractArchiveProcess', $archiveFile, $directory);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the patchHeadless method.
     * @throws ReflectionException
     * @covers ::patchHeadless
     */
    public function testPatchHeadless(): void
    {
        $headlessDirectory = 'abc';
        $fullDirectory = 'def';

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo(['abc/factorio/data/base', 'abc/factorio/data/core']));
        $this->fileSystem->expects($this->exactly(2))
                         ->method('rename')
                         ->withConsecutive(
                             [
                                 $this->identicalTo('def/factorio/data/base'),
                                 $this->identicalTo('abc/factorio/data/base'),
                             ],
                             [
                                 $this->identicalTo('def/factorio/data/core'),
                                 $this->identicalTo('abc/factorio/data/core'),
                             ]
                         );

        $downloader = new FactorioDownloader($this->console, $this->fileSystem, 'foo', 'bar', 'baz', 'oof');
        $this->invokeMethod($downloader, 'patchHeadless', $headlessDirectory, $fullDirectory);
    }

    /**
     * Tests the replaceOldVersion method.
     * @throws ReflectionException
     * @covers ::replaceOldVersion
     */
    public function testReplaceOldVersion(): void
    {
        $headlessDirectory = 'abc';
        $factorioDirectory = 'def';

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo($factorioDirectory));
        $this->fileSystem->expects($this->once())
                         ->method('rename')
                         ->with($this->identicalTo('abc/factorio'), $this->identicalTo($factorioDirectory));

        $downloader = new FactorioDownloader(
            $this->console,
            $this->fileSystem,
            $factorioDirectory,
            'foo',
            'bar',
            'baz'
        );
        $this->invokeMethod($downloader, 'replaceOldVersion', $headlessDirectory);
    }
}
