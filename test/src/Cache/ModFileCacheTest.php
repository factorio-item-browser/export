<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Cache;

use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModFileCache class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Cache\ModFileCache
 */
class ModFileCacheTest extends TestCase
{
    /**
     * Tests the read method.
     * @covers ::read
     */
    public function testRead(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $filePath = 'ghi';
        $content = 'jkl';

        /* @var ModFileCache|MockObject $cache */
        $cache = $this->getMockBuilder(ModFileCache::class)
                      ->setMethods(['getFullFilePath', 'readFile'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache->expects($this->once())
              ->method('getFullFilePath')
              ->with($modName, $fileName)
              ->willReturn($filePath);
        $cache->expects($this->once())
              ->method('readFile')
              ->with($filePath)
              ->willReturn($content);

        $result = $cache->read($modName, $fileName);
        $this->assertSame($content, $result);
    }

    /**
     * Tests the write method.
     * @throws ExportException
     * @covers ::write
     */
    public function testWrite(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $filePath = 'ghi';
        $content = 'jkl';

        /* @var ModFileCache|MockObject $cache */
        $cache = $this->getMockBuilder(ModFileCache::class)
                      ->setMethods(['getFullFilePath', 'writeFile'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache->expects($this->once())
              ->method('getFullFilePath')
              ->with($modName, $fileName)
              ->willReturn($filePath);
        $cache->expects($this->once())
              ->method('writeFile')
              ->with($filePath, $content);

        $cache->write($modName, $fileName, $content);
    }
}
