<?php

namespace FactorioItemBrowserTest\Export\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the LocaleReader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\LocaleReader
 */
class LocaleReaderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var LocaleCache $localeCache */
        $localeCache = $this->createMock(LocaleCache::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new LocaleReader($localeCache, $modFileManager);

        $this->assertSame($localeCache, $this->extractProperty($reader, 'localeCache'));
        $this->assertSame($modFileManager, $this->extractProperty($reader, 'modFileManager'));
    }

    /**
     * Provides the data for the read test.
     * @return array
     */
    public function provideRead(): array
    {
        return [
            [['abc' => 'def'], false, null, ['abc' => 'def']],
            [[], false, null, []],
            [null, true, ['abc' => 'def'], ['abc' => 'def']],
        ];
    }

    /**
     * Tests the read method.
     * @param array|null $resultCache
     * @param bool $expectRead
     * @param array|null $resultRead
     * @param array $expectedResult
     * @throws ExportException
     * @covers ::read
     * @dataProvider provideRead
     */
    public function testRead(?array $resultCache, bool $expectRead, ?array $resultRead, array $expectedResult): void
    {
        $modName = 'abc';
        $mod = (new Mod())->setName($modName);

        /* @var LocaleCache|MockObject $localeCache */
        $localeCache = $this->getMockBuilder(LocaleCache::class)
                            ->setMethods(['read', 'write'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $localeCache->expects($this->once())
                    ->method('read')
                    ->with($modName)
                    ->willReturn($resultCache);
        $localeCache->expects($expectRead ? $this->once() : $this->never())
                    ->method('write')
                    ->with($modName, $resultRead);

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        /* @var LocaleReader|MockObject $reader */
        $reader = $this->getMockBuilder(LocaleReader::class)
                       ->setMethods(['readLocaleFiles'])
                       ->setConstructorArgs([$localeCache, $modFileManager])
                       ->getMock();
        $reader->expects($expectRead ? $this->once() : $this->never())
               ->method('readLocaleFiles')
               ->with($mod)
               ->willReturn($resultRead);

        $result = $reader->read($mod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the readLocaleFiles method.
     * @throws ReflectionException
     * @covers ::readLocaleFiles
     */
    public function testReadLocaleFiles(): void
    {
        $mod = (new Mod())->setName('abc');
        $localeFileNames = [
            'locale1' => [
                'file1',
                'file2',
            ],
            'locale2' => [
                'file3',
                'file4',
            ],
        ];
        $expectedResult = [
            'locale1' => [
                'def' => 'ghi',
                'jkl' => 'mno',
            ],
            'locale2' => [
                'pqr' => 'stu',
                'vwx' => 'yza',
            ],
        ];

        /* @var LocaleReader|MockObject $reader */
        $reader = $this->getMockBuilder(LocaleReader::class)
                       ->setMethods(['getLocaleFileNames', 'readLocaleFile'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $reader->expects($this->once())
               ->method('getLocaleFileNames')
               ->with($mod)
               ->willReturn($localeFileNames);
        $reader->expects($this->exactly(4))
               ->method('readLocaleFile')
               ->withConsecutive(
                   [$mod, 'file1'],
                   [$mod, 'file2'],
                   [$mod, 'file3'],
                   [$mod, 'file4']
               )
               ->willReturnOnConsecutiveCalls(
                   ['def' => 'ghi'],
                   ['jkl' => 'mno'],
                   ['pqr' => 'stu'],
                   ['vwx' => 'yza']
               );

        $result = $this->invokeMethod($reader, 'readLocaleFiles', $mod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getLocaleFileNames method.
     * @throws ReflectionException
     * @covers ::getLocaleFileNames
     */
    public function testGetLocaleFileNames(): void
    {
        $mod = (new Mod())->setName('foo');
        $fileNames = [
            'locale/en/abc.cfg',
            'locale/de/abc.cfg',
            'locale/en/def.cfg',
            'locale/de/ghi.cfg',
            'locale/fail.cfg',
            'locale/en/fail',
            'fail.cfg'
        ];
        $expectedResult = [
            'en' => [
                'locale/en/abc.cfg',
                'locale/en/def.cfg',
            ],
            'de' => [
                'locale/de/abc.cfg',
                'locale/de/ghi.cfg',
            ],
        ];

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['getAllFileNamesOfMod'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($this->once())
                       ->method('getAllFileNamesOfMod')
                       ->with($mod)
                       ->willReturn($fileNames);

        /* @var LocaleCache $localeCache */
        $localeCache = $this->createMock(LocaleCache::class);

        $reader = new LocaleReader($localeCache, $modFileManager);
        $result = $this->invokeMethod($reader, 'getLocaleFileNames', $mod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the readLocaleFile method.
     * @throws ReflectionException
     * @covers ::readLocaleFile
     */
    public function testReadLocaleFile(): void
    {
        $mod = (new Mod())->setName('abc');
        $fileName = 'def';
        $contents = 'ghi';
        $parsedFile = ['jkl' => 'mno'];

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['readFile'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($this->once())
                       ->method('readFile')
                       ->with($mod, $fileName)
                       ->willReturn($contents);

        /* @var LocaleCache $localeCache */
        $localeCache = $this->createMock(LocaleCache::class);

        /* @var LocaleReader|MockObject $reader */
        $reader = $this->getMockBuilder(LocaleReader::class)
                       ->setMethods(['parseLocaleFile'])
                       ->setConstructorArgs([$localeCache, $modFileManager])
                       ->getMock();
        $reader->expects($this->once())
               ->method('parseLocaleFile')
               ->with($contents)
               ->willReturn($parsedFile);

        $result = $this->invokeMethod($reader, 'readLocaleFile', $mod, $fileName);
        $this->assertSame($parsedFile, $result);
    }

    /**
     * Tests the parseLocaleFile method.
     * @throws ReflectionException
     * @covers ::parseLocaleFile
     */
    public function testParseLocaleFile(): void
    {
        $content = <<<'EOT'
abc=def
ghi = jkl
mno=pqr\nstu
[foo]
abc=def
vwx=yza
EOT;
        $expectedResult = [
            'abc' => 'def',
            'ghi' => 'jkl',
            'mno' => 'pqr' . PHP_EOL . 'stu',
            'foo.abc' => 'def',
            'foo.vwx' => 'yza',
        ];

        /* @var LocaleCache $localeCache */
        $localeCache = $this->createMock(LocaleCache::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new LocaleReader($localeCache, $modFileManager);

        $result = $this->invokeMethod($reader, 'parseLocaleFile', $content);
        $this->assertEquals($expectedResult, $result);
    }
}
