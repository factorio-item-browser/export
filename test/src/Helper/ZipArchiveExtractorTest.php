<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Helper;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InvalidZipArchiveException;
use FactorioItemBrowser\Export\Exception\ZipExtractException;
use FactorioItemBrowser\Export\Helper\ZipArchiveExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The PHPUnit test of the ZipArchiveExtractor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Helper\ZipArchiveExtractor
 */
class ZipArchiveExtractorTest extends TestCase
{
    /** @var Filesystem&MockObject */
    private Filesystem $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = $this->createMock(Filesystem::class);
    }

    private function createInstance(): ZipArchiveExtractor
    {
        return new ZipArchiveExtractor($this->fileSystem);
    }

    /**
     * @throws ExportException
     * @covers ::__construct
     * @covers ::extract
     */
    public function testExtract(): void
    {
        $zipArchiveFile = __DIR__ . '/../../asset/mod/valid.zip';
        $targetDirectory = 'foo';

        $expectedFiles = [
            'foo/abc' => 'abc',
            'foo/def' => 'def',
            'foo/bar/abc' => 'abc',
            'foo/bar/ghi' => 'ghi',
            'foo/empty' => '',
        ];

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo($targetDirectory));
        $this->fileSystem->expects($this->once())
                         ->method('mkdir')
                         ->with($this->identicalTo($targetDirectory));
        $this->fileSystem->expects($this->exactly(5))
                         ->method('dumpFile')
                         ->willReturnCallback(function (string $fileName, $contents) use ($expectedFiles): void {
                             $streamContents = (string) stream_get_contents($contents);
                             $this->assertArrayHasKey($fileName, $expectedFiles);
                             $this->assertSame($expectedFiles[$fileName], $streamContents);
                         });

        $instance = $this->createInstance();
        $instance->extract($zipArchiveFile, $targetDirectory);
    }

    /**
     * @throws ExportException
     * @covers ::__construct
     * @covers ::extract
     */
    public function testExtractWithInvalidArchive(): void
    {
        $zipArchiveFile = __DIR__ . '/../../asset/mod/invalid.zip';
        $targetDirectory = 'foo';

        $this->expectException(InvalidZipArchiveException::class);

        $instance = $this->createInstance();
        $instance->extract($zipArchiveFile, $targetDirectory);
    }

    /**
     * @throws ExportException
     * @covers ::__construct
     * @covers ::extract
     */
    public function testExtractWithIOException(): void
    {
        $zipArchiveFile = __DIR__ . '/../../asset/mod/valid.zip';
        $targetDirectory = 'foo';

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo('foo'))
                         ->willThrowException($this->createMock(IOException::class));

        $this->expectException(ZipExtractException::class);

        $instance = $this->createInstance();
        $instance->extract($zipArchiveFile, $targetDirectory);
    }
}
