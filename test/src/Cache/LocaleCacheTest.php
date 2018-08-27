<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Cache;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the LocaleCache class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Cache\LocaleCache
 */
class LocaleCacheTest extends TestCase
{
    /**
     * The contents of a cached translation file.
     */
    protected const TEST_TRANSLATION_CONTENT = <<<EOT
<?php return array (
  'foo' => 'bar',
  'hello' => 'world',
);
EOT;

    /**
     * Provides the data for the read test.
     * @return array
     */
    public function provideRead(): array
    {
        return [
            [0775, self::TEST_TRANSLATION_CONTENT, ['foo' => 'bar', 'hello' => 'world']],
            [0775, '<?php return 42;', null],
            [0000, self::TEST_TRANSLATION_CONTENT, null],
        ];
    }

    /**
     * Tests the read method.
     * @param int $filePermission
     * @param string $fileContent
     * @param array|null $expectedResult
     * @covers ::read
     * @dataProvider provideRead
     */
    public function testRead(int $filePermission, string $fileContent, ?array $expectedResult): void
    {
        $directory = vfsStream::setup('root');
        $file = vfsStream::newFile('abc', 0755);
        $directory->addChild($file);
        file_put_contents($file->url(), $fileContent);
        $file->chmod($filePermission);

        $modName = 'def';
        $filePath = vfsStream::url('root/abc');

        /* @var LocaleCache|MockObject $cache */
        $cache = $this->getMockBuilder(LocaleCache::class)
                      ->setMethods(['getFullFilePath'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache->expects($this->once())
              ->method('getFullFilePath')
              ->with($modName, 'translation.php')
              ->willReturn($filePath);

        $result = $cache->read($modName);
        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Tests the write method.
     * @throws ExportException
     * @covers ::write
     */
    public function testWrite(): void
    {
        $modName = 'abc';
        $filePath = 'def';
        $translations = ['foo' => 'bar', 'hello' => 'world'];
        $expectedContent = self::TEST_TRANSLATION_CONTENT;

        /* @var LocaleCache|MockObject $cache */
        $cache = $this->getMockBuilder(LocaleCache::class)
                      ->setMethods(['getFullFilePath', 'writeFile'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache->expects($this->once())
              ->method('getFullFilePath')
              ->with($modName, 'translation.php')
              ->willReturn($filePath);
        $cache->expects($this->once())
              ->method('writeFile')
              ->with($filePath, $expectedContent);

        $cache->write($modName, $translations);
    }
}
