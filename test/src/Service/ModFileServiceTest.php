<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Service;

use Exception;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\InvalidInfoJsonFileException;
use FactorioItemBrowser\Export\Helper\ZipArchiveExtractor;
use FactorioItemBrowser\Export\Service\ModFileService;
use JMS\Serializer\SerializerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModFileService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Service\ModFileService
 */
class ModFileServiceTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $exportSerializer;
    /** @var ZipArchiveExtractor&MockObject */
    private ZipArchiveExtractor $zipArchiveExtractor;
    private string $fullFactorioDirectory = 'data/factorio-full';
    private string $modsDirectory = 'data/mods';

    protected function setUp(): void
    {
        $this->exportSerializer = $this->createMock(SerializerInterface::class);
        $this->zipArchiveExtractor = $this->createMock(ZipArchiveExtractor::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ModFileService&MockObject
     */
    private function createInstance(array $mockedMethods = []): ModFileService
    {
        return $this->getMockBuilder(ModFileService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->exportSerializer,
                        $this->zipArchiveExtractor,
                        $this->fullFactorioDirectory,
                        $this->modsDirectory,
                    ])
                    ->getMock();
    }

    /**
     * @throws ExportException
     */
    public function testAddModArchive(): void
    {
        $modName = 'abc';
        $archiveFilePath = 'def';

        $this->zipArchiveExtractor->expects($this->once())
                                  ->method('extract')
                                  ->with($this->identicalTo('def'), $this->identicalTo('bar/abc'));

        $instance = $this->createInstance(['getLocalDirectory']);
        $instance->expects($this->once())
                 ->method('getLocalDirectory')
                 ->with($this->identicalTo($modName))
                 ->willReturn('bar/abc');

        $instance->addModArchive($modName, $archiveFilePath);
    }

    /**
     * @throws ExportException
     */
    public function testAddModArchiveWithException(): void
    {
        $modName = 'base';
        $archiveFilePath = 'def';

        $this->zipArchiveExtractor->expects($this->never())
                                  ->method('extract');

        $instance = $this->createInstance(['getLocalDirectory']);
        $instance->expects($this->never())
                 ->method('getLocalDirectory');

        $this->expectException(InternalException::class);

        $instance->addModArchive($modName, $archiveFilePath);
    }

    /**
     * @throws ExportException
     */
    public function testGetInfo(): void
    {
        $modName = 'abc';
        $contents = 'def';
        $infoJson = $this->createMock(InfoJson::class);

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($contents),
                                   $this->identicalTo(InfoJson::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($infoJson);

        $instance = $this->createInstance(['readFile']);
        $instance->expects($this->once())
                 ->method('readFile')
                 ->with($this->identicalTo($modName), $this->identicalTo('info.json'))
                 ->willReturn($contents);

        $result = $instance->getInfo($modName);

        $this->assertSame($infoJson, $result);
    }

    /**
     * @throws ExportException
     */
    public function testGetInfoWithException(): void
    {
        $modName = 'abc';
        $contents = 'def';

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($contents),
                                   $this->identicalTo(InfoJson::class),
                                   $this->identicalTo('json'),
                               )
                               ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidInfoJsonFileException::class);

        $instance = $this->createInstance(['readFile']);
        $instance->expects($this->once())
                 ->method('readFile')
                 ->with($this->identicalTo($modName), $this->identicalTo('info.json'))
                 ->willReturn($contents);

        $instance->getInfo($modName);
    }

    /**
     * @throws ExportException
     */
    public function testReadFile(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $contents = 'ghi';

        vfsStream::setup('root');
        file_put_contents(vfsStream::url('root/def'), $contents);

        $instance = $this->createInstance(['getLocalDirectory']);
        $instance->expects($this->once())
                 ->method('getLocalDirectory')
                 ->with($this->identicalTo($modName))
                 ->willReturn(vfsStream::url('root'));

        $result = $instance->readFile($modName, $fileName);

        $this->assertSame($contents, $result);
    }

    /**
     * @throws ExportException
     */
    public function testReadFileWithException(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $contents = 'ghi';

        vfsStream::setup('root');

        $this->expectException(FileNotFoundInModException::class);

        $instance = $this->createInstance(['getLocalDirectory']);
        $instance->expects($this->once())
                 ->method('getLocalDirectory')
                 ->with($this->identicalTo($modName))
                 ->willReturn(vfsStream::url('root'));

        $result = $instance->readFile($modName, $fileName);

        $this->assertSame($contents, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideGetLocalDirectory(): array
    {
        return [
            ['base', realpath(__DIR__ . '/../../../data/factorio-full') . '/data/base'],
            ['core', realpath(__DIR__ . '/../../../data/factorio-full') . '/data/core'],
            ['abc', realpath(__DIR__ . '/../../../data/mods') . '/abc'],
        ];
    }

    /**
     * @param string $modName
     * @param string $expectedResult
     * @dataProvider provideGetLocalDirectory
     */
    public function testGetLocalDirectory(string $modName, string $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $instance->getLocalDirectory($modName);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideIsVanillaMod(): array
    {
        return [
            ['base', true],
            ['core', true],
            ['foo', false],
        ];
    }

    /**
     * @dataProvider provideIsVanillaMod
     */
    public function testIsVanillaMod(string $modName, bool $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $instance->isVanillaMod($modName);

        $this->assertSame($expectedResult, $result);
    }
}
