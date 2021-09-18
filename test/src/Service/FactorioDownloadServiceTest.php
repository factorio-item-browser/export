<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use FactorioItemBrowser\Export\Service\FactorioDownloadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the FactorioDownloadService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Service\FactorioDownloadService
 */
class FactorioDownloadServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var Filesystem&MockObject */
    private Filesystem $fileSystem;
    private string $factorioApiUsername = 'foo';
    private string $factorioApiToken = 'bar';
    private string $tempDirectory = '/tmp';

    protected function setUp(): void
    {
        $this->fileSystem = $this->createMock(Filesystem::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return FactorioDownloadService&MockObject
     */
    private function createInstance(array $mockedMethods = []): FactorioDownloadService
    {
        return $this->getMockBuilder(FactorioDownloadService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->fileSystem,
                        $this->factorioApiUsername,
                        $this->factorioApiToken,
                        $this->tempDirectory,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideCreateFactorioDownloadProcess(): array
    {
        return [
            [
                FactorioDownloadService::VARIANT_FULL,
                '1.2.3',
                'https://www.factorio.com/get-download/1.2.3/alpha/linux64',
            ],
            [
                FactorioDownloadService::VARIANT_HEADLESS,
                '1.2.3',
                'https://www.factorio.com/get-download/1.2.3/headless/linux64',
            ],
        ];
    }

    /**
     * @dataProvider provideCreateFactorioDownloadProcess
     */
    public function testCreateFactorioDownloadProcess(string $variant, string $version, string $expectedUrl): void
    {
        $builtUrl = 'abc';
        $destinationFile = 'def';
        $expectedResult = new DownloadProcess($builtUrl, $destinationFile);

        $instance = $this->createInstance(['buildUrl']);
        $instance->expects($this->once())
                 ->method('buildUrl')
                 ->with($this->identicalTo($expectedUrl))
                 ->willReturn($builtUrl);

        $result = $instance->createFactorioDownloadProcess($variant, $version, $destinationFile);

        $this->assertEquals($expectedResult, $result);
    }

    public function testExtractFactorio(): void
    {
        $destinationDirectory = 'abc';
        $archiveFile = 'def';
        $expectedTempDirectory = '/tmp/factorio_temp';

        $process = $this->createMock(Process::class);
        $process->expects($this->once())
                ->method('run');

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo($destinationDirectory));
        $this->fileSystem->expects($this->once())
                         ->method('rename')
                         ->with($this->identicalTo($expectedTempDirectory), $this->identicalTo($destinationDirectory));

        $instance = $this->createInstance(['createExtractArchiveProcess']);
        $instance->expects($this->once())
                 ->method('createExtractArchiveProcess')
                 ->with($this->identicalTo($archiveFile), $this->identicalTo($expectedTempDirectory))
                 ->willReturn($process);

        $instance->extractFactorio($archiveFile, $destinationDirectory);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateExtractArchiveProcess(): void
    {
        $archiveFile = 'abc';
        $destinationDirectory = 'def';

        $expectedResult = new Process(explode(' ', 'tar -xf abc -C def --strip 1'), null, null, null, null);

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo($destinationDirectory));
        $this->fileSystem->expects($this->once())
                         ->method('mkdir')
                         ->with($this->identicalTo($destinationDirectory));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createExtractArchiveProcess', $archiveFile, $destinationDirectory);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testBuildUrl(): void
    {
        $url = 'abc';
        $parameters = ['def' => 'ghi'];
        $expectedResult = 'abc?def=ghi&username=foo&token=bar';

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'buildUrl', $url, $parameters);

        $this->assertSame($expectedResult, $result);
    }
}
