<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\DownloadFactorioCommand;
use FactorioItemBrowser\Export\Constant\CommandName;
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
use Symfony\Component\Process\Process;

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
        return $this->getMockBuilder(DownloadFactorioCommand::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->factorioDownloadService,
                        $this->fileSystem,
                        $this->fullFactorioDirectory,
                        $this->headlessFactorioDirectory,
                        $this->tempDirectory,
                    ])
                    ->getMock();
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
        $expectedTempDirectoryFull = 'tmp/factorio_1.2.3_full';
        $expectedTempDirectoryHeadless = 'tmp/factorio_1.2.3_headless';

        $fullDownloadProcess = $this->createMock(DownloadProcess::class);
        $fullDownloadProcess->expects($this->once())
                            ->method('start');
        $fullDownloadProcess->expects($this->once())
                            ->method('wait');
        $fullDownloadProcess->expects($this->once())
                            ->method('getExitCode')
                            ->willReturn(0);

        $headlessDownloadProcess = $this->createMock(DownloadProcess::class);
        $headlessDownloadProcess->expects($this->once())
                                ->method('run');
        $headlessDownloadProcess->expects($this->once())
                                ->method('getExitCode')
                                ->willReturn(0);

        $headlessExtractProcess = $this->createMock(Process::class);
        $headlessExtractProcess->expects($this->once())
                               ->method('run');
        $headlessExtractProcess->expects($this->once())
                               ->method('getExitCode')
                               ->willReturn(0);

        $fullExtractProcess = $this->createMock(Process::class);
        $fullExtractProcess->expects($this->once())
                           ->method('run');
        $fullExtractProcess->expects($this->once())
                           ->method('getExitCode')
                           ->willReturn(0);

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
                                          $fullDownloadProcess,
                                          $headlessDownloadProcess,
                                      );
        $this->factorioDownloadService->expects($this->exactly(2))
                                      ->method('createFactorioExtractProcess')
                                      ->withConsecutive(
                                          [
                                              $this->identicalTo($expectedArchiveFileHeadless),
                                              $this->identicalTo($expectedTempDirectoryHeadless),
                                          ],
                                          [
                                              $this->identicalTo($expectedArchiveFileFull),
                                              $this->identicalTo($expectedTempDirectoryFull),
                                          ],
                                      )
                                      ->willReturnOnConsecutiveCalls(
                                          $headlessExtractProcess,
                                          $fullExtractProcess,
                                      );

        $this->fileSystem->expects($this->exactly(2))
                         ->method('rename')
                         ->withConsecutive(
                             [$this->identicalTo($expectedTempDirectoryHeadless), $this->identicalTo('bar')],
                             [$this->identicalTo($expectedTempDirectoryFull), $this->identicalTo('foo')],
                         );
        $this->fileSystem->expects($this->exactly(4))
                         ->method('remove')
                         ->withConsecutive(
                             [$this->identicalTo('bar')],
                             [$this->identicalTo('foo')],
                             [$this->identicalTo($expectedArchiveFileHeadless)],
                             [$this->identicalTo($expectedArchiveFileFull)],
                         );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithFailedDownloadHeadless(): void
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
        $expectedTempDirectoryHeadless = 'tmp/factorio_1.2.3_headless';

        $fullDownloadProcess = $this->createMock(DownloadProcess::class);
        $fullDownloadProcess->expects($this->once())
                            ->method('start');
        $fullDownloadProcess->expects($this->never())
                            ->method('wait');

        $headlessDownloadProcess = $this->createMock(DownloadProcess::class);
        $headlessDownloadProcess->expects($this->once())
                                ->method('run');
        $headlessDownloadProcess->expects($this->once())
                                ->method('getExitCode')
                                ->willReturn(1);

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
                                          $fullDownloadProcess,
                                          $headlessDownloadProcess,
                                      );
        $this->factorioDownloadService->expects($this->never())
                                      ->method('createFactorioExtractProcess');

        $this->fileSystem->expects($this->never())
                         ->method('rename');
        $this->fileSystem->expects($this->never())
                         ->method('remove');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithFailedExtractHeadless(): void
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
        $expectedTempDirectoryHeadless = 'tmp/factorio_1.2.3_headless';

        $fullDownloadProcess = $this->createMock(DownloadProcess::class);
        $fullDownloadProcess->expects($this->once())
                            ->method('start');
        $fullDownloadProcess->expects($this->never())
                            ->method('wait');

        $headlessDownloadProcess = $this->createMock(DownloadProcess::class);
        $headlessDownloadProcess->expects($this->once())
                                ->method('run');
        $headlessDownloadProcess->expects($this->once())
                                ->method('getExitCode')
                                ->willReturn(0);

        $headlessExtractProcess = $this->createMock(Process::class);
        $headlessExtractProcess->expects($this->once())
                               ->method('run');
        $headlessExtractProcess->expects($this->once())
                               ->method('getExitCode')
                               ->willReturn(1);

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
                                          $fullDownloadProcess,
                                          $headlessDownloadProcess,
                                      );
        $this->factorioDownloadService->expects($this->once())
                                      ->method('createFactorioExtractProcess')
                                      ->with(
                                          $this->identicalTo($expectedArchiveFileHeadless),
                                          $this->identicalTo($expectedTempDirectoryHeadless),
                                      )
                                      ->willReturn($headlessExtractProcess);

        $this->fileSystem->expects($this->never())
                         ->method('rename');
        $this->fileSystem->expects($this->never())
                         ->method('remove');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithFailedDownloadFull(): void
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
        $expectedTempDirectoryHeadless = 'tmp/factorio_1.2.3_headless';

        $fullDownloadProcess = $this->createMock(DownloadProcess::class);
        $fullDownloadProcess->expects($this->once())
                            ->method('start');
        $fullDownloadProcess->expects($this->once())
                            ->method('wait');
        $fullDownloadProcess->expects($this->once())
                            ->method('getExitCode')
                            ->willReturn(1);

        $headlessDownloadProcess = $this->createMock(DownloadProcess::class);
        $headlessDownloadProcess->expects($this->once())
                                ->method('run');
        $headlessDownloadProcess->expects($this->once())
                                ->method('getExitCode')
                                ->willReturn(0);

        $headlessExtractProcess = $this->createMock(Process::class);
        $headlessExtractProcess->expects($this->once())
                               ->method('run');
        $headlessExtractProcess->expects($this->once())
                               ->method('getExitCode')
                               ->willReturn(0);

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
                                          $fullDownloadProcess,
                                          $headlessDownloadProcess,
                                      );
        $this->factorioDownloadService->expects($this->once())
                                      ->method('createFactorioExtractProcess')
                                      ->with(
                                          $this->identicalTo($expectedArchiveFileHeadless),
                                          $this->identicalTo($expectedTempDirectoryHeadless),
                                      )
                                      ->willReturn($headlessExtractProcess);

        $this->fileSystem->expects($this->never())
                         ->method('rename');
        $this->fileSystem->expects($this->never())
                         ->method('remove');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithFailedExtractFull(): void
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
        $expectedTempDirectoryFull = 'tmp/factorio_1.2.3_full';
        $expectedTempDirectoryHeadless = 'tmp/factorio_1.2.3_headless';

        $fullDownloadProcess = $this->createMock(DownloadProcess::class);
        $fullDownloadProcess->expects($this->once())
                            ->method('start');
        $fullDownloadProcess->expects($this->once())
                            ->method('wait');
        $fullDownloadProcess->expects($this->once())
                            ->method('getExitCode')
                            ->willReturn(0);

        $headlessDownloadProcess = $this->createMock(DownloadProcess::class);
        $headlessDownloadProcess->expects($this->once())
                                ->method('run');
        $headlessDownloadProcess->expects($this->once())
                                ->method('getExitCode')
                                ->willReturn(0);

        $headlessExtractProcess = $this->createMock(Process::class);
        $headlessExtractProcess->expects($this->once())
                               ->method('run');
        $headlessExtractProcess->expects($this->once())
                               ->method('getExitCode')
                               ->willReturn(0);

        $fullExtractProcess = $this->createMock(Process::class);
        $fullExtractProcess->expects($this->once())
                           ->method('run');
        $fullExtractProcess->expects($this->once())
                           ->method('getExitCode')
                           ->willReturn(1);

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
                                          $fullDownloadProcess,
                                          $headlessDownloadProcess,
                                      );
        $this->factorioDownloadService->expects($this->exactly(2))
                                      ->method('createFactorioExtractProcess')
                                      ->withConsecutive(
                                          [
                                              $this->identicalTo($expectedArchiveFileHeadless),
                                              $this->identicalTo($expectedTempDirectoryHeadless),
                                          ],
                                          [
                                              $this->identicalTo($expectedArchiveFileFull),
                                              $this->identicalTo($expectedTempDirectoryFull),
                                          ],
                                      )
                                      ->willReturnOnConsecutiveCalls(
                                          $headlessExtractProcess,
                                          $fullExtractProcess,
                                      );

        $this->fileSystem->expects($this->never())
                         ->method('rename');
        $this->fileSystem->expects($this->never())
                         ->method('remove');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }
}
