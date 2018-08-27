<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Cache;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Cache\AbstractCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractCache class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Cache\AbstractCache
 */
class AbstractCacheTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $cacheDirectory = 'abc';

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setConstructorArgs([$cacheDirectory])
                      ->getMockForAbstractClass();
        $this->assertSame($cacheDirectory, $this->extractProperty($cache, 'cacheDirectory'));
    }


    /**
     * Provides the data for the ensureDirectory test.
     * @return array
     */
    public function provideEnsureDirectory(): array
    {
        return [
            [0775, 0775, false, true],
            [0775, null, false, true],
            [0775, 0000, true, false],
            [0000, null, true, false],
        ];
    }

    /**
     * Tests the ensureDirectory method.
     * @param int $parentDirectoryPermission
     * @param int|null $directoryPermission
     * @param bool $expectException
     * @param bool $expectDirectory
     * @throws ReflectionException
     * @covers ::ensureDirectory
     * @dataProvider provideEnsureDirectory
     */
    public function testEnsureDirectory(
        int $parentDirectoryPermission,
        ?int $directoryPermission,
        bool $expectException,
        bool $expectDirectory
    ): void {
        $vfs = vfsStream::setup('root', $parentDirectoryPermission);
        $directory = vfsStream::url('root/abc');
        if ($directoryPermission !== null) {
            $vfs->addChild(vfsStream::newDirectory('abc', $directoryPermission));
        }

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $this->invokeMethod($cache, 'ensureDirectory', $directory);
        $this->assertSame($expectDirectory, $vfs->hasChild('abc'));
    }

    /**
     * Tests the getFullFilePath method.
     * @throws ReflectionException
     * @covers ::getFullFilePath
     */
    public function testGetFullFilePath(): void
    {
        $cacheDirectory = 'abc';
        $modName = 'def';
        $fileName = 'ghi';
        $expectedResult = 'abc/def/ghi';

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setConstructorArgs([$cacheDirectory])
                      ->getMockForAbstractClass();

        $result = $this->invokeMethod($cache, 'getFullFilePath', $modName, $fileName);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the writeFile test.
     * @return array
     */
    public function provideWriteFile(): array
    {
        return [
            [0775, false],
            [0000, true],
        ];
    }

    /**
     * Tests the writeFile method.
     * @param int $filePermission
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::writeFile
     * @dataProvider provideWriteFile
     */
    public function testWriteFile(int $filePermission, bool $expectException): void
    {
        $directory = vfsStream::setup('root');
        $directory->addChild(vfsStream::newFile('abc', $filePermission));

        $filePath = vfsStream::url('root/abc');
        $expectedDirectory = vfsStream::url('root');
        $content = 'def';

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setMethods(['ensureDirectory'])
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $cache->expects($this->once())
              ->method('ensureDirectory')
              ->with($expectedDirectory);

        $this->invokeMethod($cache, 'writeFile', $filePath, $content);
        if (!$expectException) {
            $this->assertSame($content, file_get_contents($filePath));
        }
    }

    /**
     * Provides the data for the readFile test.
     * @return array
     */
    public function provideReadFile(): array
    {
        return [
            ['bar', 0775, 'bar'],
            ['bar', 0000, null],
            [null, 0775, null],
        ];
    }

    /**
     * Tests the readFile method.
     * @param string|null $content
     * @param int $filePermission
     * @param string|null $expectedResult
     * @throws ReflectionException
     * @covers ::readFile
     * @dataProvider provideReadFile
     */
    public function testReadFile(?string $content, int $filePermission, ?string $expectedResult): void
    {
        $directory = vfsStream::setup('root', $filePermission);
        if ($content !== null) {
            $file = vfsStream::newFile('foo', 0775);
            $directory->addChild($file);
            file_put_contents($file->url(), $content);
            $file->chmod($filePermission);
        }

        $filePath = vfsStream::url('root/foo');

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();

        $result = $this->invokeMethod($cache, 'readFile', $filePath);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the clear method.
     * @covers ::clear
     */
    public function testClear(): void
    {
        $cacheDirectory = 'abc';

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setMethods(['clearDirectory'])
                      ->setConstructorArgs([$cacheDirectory])
                      ->getMockForAbstractClass();
        $cache->expects($this->once())
              ->method('clearDirectory')
              ->with($cacheDirectory);

        $cache->clear();
    }

    /**
     * Tests the clearMod method.
     * @covers ::clearMod
     */
    public function testClearMod(): void
    {
        $cacheDirectory = 'abc';
        $modName = 'def';
        $expectedDirectory = 'abc/def';

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setMethods(['clearDirectory'])
                      ->setConstructorArgs([$cacheDirectory])
                      ->getMockForAbstractClass();
        $cache->expects($this->once())
              ->method('clearDirectory')
              ->with($expectedDirectory);

        $cache->clearMod($modName);
    }

    /**
     * Tests the clearDirectory method.
     * @throws ReflectionException
     * @covers ::clearDirectory
     */
    public function testClearDirectory(): void
    {
        $directory = vfsStream::setup('root', null, [
            'abc' => [
                'def' => 'ghi',
                'jkl' => [
                    'mno' => 'pqr'
                ]
            ],
            'stu' => 'vwx'
        ]);
        $directoriesToRemove = [
            'abc/def',
            'abc/jkl/mno',
            'stu',
        ];
        foreach ($directoriesToRemove as $path) {
            $this->assertTrue($directory->hasChild($path));
        }

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $this->invokeMethod($cache, 'clearDirectory', vfsStream::url('root'));
        foreach ($directoriesToRemove as $path) {
            $this->assertFalse($directory->hasChild($path));
        }
    }
}
