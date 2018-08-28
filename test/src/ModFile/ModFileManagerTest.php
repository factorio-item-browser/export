<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\ModFile;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModFileManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\ModFile\ModFileManager
 */
class ModFileManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ModFileCache $cache */
        $cache = $this->createMock(ModFileCache::class);
        $directory = 'abc';

        $manager = new ModFileManager($cache, $directory);
        $this->assertSame($cache, $this->extractProperty($manager, 'cache'));
        $this->assertSame($directory, $this->extractProperty($manager, 'directory'));
    }

    /**
     * Provides the data for the getFile test.
     * @return array
     */
    public function provideGetFile(): array
    {
        return [
            [
                'abc',
                false,
                null,
                false,
                false,
                'abc',
            ],
            [
                null,
                true,
                'abc',
                true,
                false,
                'abc',
            ],
            [
                null,
                true,
                null,
                false,
                true,
                ''
            ],
        ];
    }

    /**
     * Tests the getFile method.
     * @param null|string $resultRead
     * @param bool $expectReadFile
     * @param null|string $resultReadFile
     * @param bool $expectCacheWrite
     * @param bool $expectException
     * @param string $expectedResult
     * @throws ExportException
     * @covers ::getFile
     * @dataProvider provideGetFile
     */
    public function testGetFile(
        ?string $resultRead,
        bool $expectReadFile,
        ?string $resultReadFile,
        bool $expectCacheWrite,
        bool $expectException,
        string $expectedResult
    ): void {
        $directory = 'abc';
        $modName = 'def';
        $fileName = 'ghi';

        $mod = new Mod();
        $mod->setName($modName);

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        /* @var ModFileCache|MockObject $cache */
        $cache = $this->getMockBuilder(ModFileCache::class)
                      ->setMethods(['read', 'write'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache->expects($this->once())
              ->method('read')
              ->with($modName, $fileName)
              ->willReturn($resultRead);
        $cache->expects($expectCacheWrite ? $this->once() : $this->never())
              ->method('write')
              ->with($modName, $fileName, $resultReadFile);

        /* @var ModFileManager|MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->setMethods(['readFile'])
                        ->setConstructorArgs([$cache, $directory])
                        ->getMock();
        $manager->expects($expectReadFile ? $this->once() : $this->never())
                ->method('readFile')
                ->with($mod, $fileName)
                ->willReturn($resultReadFile);

        $result = $manager->getFile($mod, $fileName);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the readFile test.
     * @return array
     */
    public function provideReadFile(): array
    {
        return [
            ['abc', 'abc'],
            [null, null],
        ];
    }

    /**
     * Tests the readFile method.
     * @param null|string $fileContent
     * @param null|string $expectedResult
     * @covers ::readFile
     * @dataProvider provideReadFile
     */
    public function testReadFile(?string $fileContent, ?string $expectedResult): void
    {
        $mod = new Mod();
        $fileName = 'abc';
        $filePath = vfsStream::url('root/def');

        $directory = vfsStream::setup('root');
        if ($fileContent !== null) {
            $directory->addChild(vfsStream::newFile('def'));
            file_put_contents($filePath, $fileContent);
        }

        /* @var ModFileManager|MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->setMethods(['getFullFilePath'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $manager->expects($this->once())
                ->method('getFullFilePath')
                ->with($mod, $fileName)
                ->willReturn($filePath);

        $result = $manager->readFile($mod, $fileName);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getFullFilePath method.
     * @covers ::getFullFilePath
     * @throws ReflectionException
     */
    public function testGetFullFilePath(): void
    {
        $directory = 'abc';
        $mod = new Mod();
        $mod->setFileName('def')
            ->setDirectoryName('ghi');
        $fileName = 'jkl';
        $expectedResult = 'zip://abc/def#ghi/jkl';

        /* @var ModFileCache $cache */
        $cache = $this->createMock(ModFileCache::class);

        $manager = new ModFileManager($cache, $directory);

        $result = $this->invokeMethod($manager, 'getFullFilePath', $mod, $fileName);
        $this->assertSame($expectedResult, $result);
    }
}
