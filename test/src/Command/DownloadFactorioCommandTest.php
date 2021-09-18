<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\DownloadFactorioCommand;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Factorio\FactorioDownloader;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use FactorioItemBrowser\Export\Service\FactorioDownloadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The PHPUnit test of the DownloadFactorioCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\DownloadFactorioCommand
 */
class DownloadFactorioCommandTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var FactorioDownloadService&MockObject */
    private FactorioDownloadService $factorioDownloadService;
    /** @var Filesystem&MockObject */
    private Filesystem $fileSystem;
    private string $fullFactorioDirectory = 'foo';
    private string $headlessFactorioDirectory = 'bar';
    private string $tempDirectory = 'tmp';

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->factorioDownloadService = $this->createMock(FactorioDownloadService::class);
        $this->fileSystem = $this->createMock(Filesystem::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DownloadFactorioCommand&MockObject
     */
    private function createInstance(array $mockedMethods = []): DownloadFactorioCommand
    {
        $instance = $this->getMockBuilder(DownloadFactorioCommand::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->console,
                             $this->factorioDownloadService,
                             $this->fileSystem,
                             'src',
                             'test',
                             '/tmp',
                         ])
                         ->getMock();

        $this->assertSame(realpath('src'), $this->extractProperty($instance, 'fullFactorioDirectory'));
        $this->assertSame(realpath('test'), $this->extractProperty($instance, 'headlessFactorioDirectory'));
        $this->assertSame(realpath('/tmp'), $this->extractProperty($instance, 'tempDirectory'));
        $this->injectProperty($instance, 'fullFactorioDirectory', $this->fullFactorioDirectory);
        $this->injectProperty($instance, 'headlessFactorioDirectory', $this->headlessFactorioDirectory);
        $this->injectProperty($instance, 'tempDirectory', $this->tempDirectory);

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    public function testConfigure(): void
    {
        $instance = $this->createInstance(['setName', 'setDescription', 'addArgument']);
        $instance->expects($this->once())
                 ->method('setName')
                 ->with($this->identicalTo(CommandName::DOWNLOAD_FACTORIO));
        $instance->expects($this->once())
                 ->method('setDescription')
                 ->with($this->isType('string'));
        $instance->expects($this->once())
                 ->method('addArgument')
                 ->with(
                     $this->identicalTo('version'),
                     $this->identicalTo(InputArgument::REQUIRED),
                     $this->isType('string')
                 );

        $this->invokeMethod($instance, 'configure');
    }

    /**
     * @throws ReflectionException
     */
    public function testExecute(): void
    {
        $version = '1.2.3';
        $output = $this->createMock(OutputInterface::class);

        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())
              ->method('getArgument')
              ->with($this->identicalTo('version'))
              ->willReturn($version);

        $expectedArchiveFileFull = 'tmp/factorio_1.2.3_full.tar.xz';
        $expectedArchiveFileHeadless = 'tmp/factorio_1.2.3_headless.tar.xz';

        $fullProcess = $this->createMock(DownloadProcess::class);
        $fullProcess->expects($this->once())
                    ->method('start');
        $fullProcess->expects($this->once())
                    ->method('wait');
        $headlessProcess = $this->createMock(DownloadProcess::class);
        $headlessProcess->expects($this->once())
                        ->method('run');

        $this->factorioDownloadService->expects($this->exactly(2))
                                      ->method('createFactorioDownloadProcess')
                                      ->withConsecutive(
                                          [
                                              $this->identicalTo(FactorioDownloadService::VARIANT_FULL),
                                              $this->identicalTo($version),
                                              $this->identicalTo($expectedArchiveFileFull),
                                          ],
                                          [
                                              $this->identicalTo(FactorioDownloadService::VARIANT_HEADLESS),
                                              $this->identicalTo($version),
                                              $this->identicalTo($expectedArchiveFileHeadless),
                                          ],
                                      )
                                      ->willReturnOnConsecutiveCalls(
                                          $fullProcess,
                                          $headlessProcess,
                                      );
        $this->factorioDownloadService->expects($this->exactly(2))
                                      ->method('extractFactorio')
                                      ->withConsecutive(
                                          [$this->identicalTo($expectedArchiveFileHeadless), $this->identicalTo('bar')],
                                          [$this->identicalTo($expectedArchiveFileFull), $this->identicalTo('foo')],
                                      );

        $this->fileSystem->expects($this->exactly(2))
                         ->method('remove')
                         ->withConsecutive(
                             [$this->identicalTo($expectedArchiveFileHeadless)],
                             [$this->identicalTo($expectedArchiveFileFull)],
                         );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }
}
