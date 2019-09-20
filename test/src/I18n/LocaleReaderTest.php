<?php

namespace FactorioItemBrowserTest\Export\I18n;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\LocaleReader;
use FactorioItemBrowser\Export\Mod\NewModFileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the LocaleReader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\I18n\LocaleReader
 */
class LocaleReaderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod file manager.
     * @var NewModFileManager&MockObject
     */
    protected $modFileManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modFileManager = $this->createMock(NewModFileManager::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $reader = new LocaleReader($this->modFileManager);

        $this->assertSame($this->modFileManager, $this->extractProperty($reader, 'modFileManager'));
    }

    /**
     * Tests the read method.
     * @throws ReflectionException
     * @covers ::read
     */
    public function testRead(): void
    {
        $modName = 'abc';
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

        /* @var LocaleReader&MockObject $reader */
        $reader = $this->getMockBuilder(LocaleReader::class)
                       ->setMethods(['getLocaleFileNames', 'readLocaleFile'])
                       ->setConstructorArgs([$this->modFileManager])
                       ->getMock();
        $reader->expects($this->once())
               ->method('getLocaleFileNames')
               ->with($this->identicalTo($modName))
               ->willReturn($localeFileNames);
        $reader->expects($this->exactly(4))
               ->method('readLocaleFile')
               ->withConsecutive(
                   [$this->identicalTo($modName), $this->identicalTo('file1')],
                   [$this->identicalTo($modName), $this->identicalTo('file2')],
                   [$this->identicalTo($modName), $this->identicalTo('file3')],
                   [$this->identicalTo($modName), $this->identicalTo('file4')]
               )
               ->willReturnOnConsecutiveCalls(
                   ['def' => 'ghi'],
                   ['jkl' => 'mno'],
                   ['pqr' => 'stu'],
                   ['vwx' => 'yza']
               );

        $result = $this->invokeMethod($reader, 'read', $modName);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getLocaleFileNames method.
     * @throws ReflectionException
     * @covers ::getLocaleFileNames
     */
    public function testGetLocaleFileNames(): void
    {
        $modName = 'foo';
        $fileNames = [
            'locale/en/abc.cfg',
            'locale/de/abc.cfg',
            'locale/en/def.cfg',
            'locale/de/ghi.cfg',
            'locale/fail.cfg',
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

        $this->modFileManager->expects($this->once())
                             ->method('findFiles')
                             ->with($this->identicalTo($modName), $this->identicalTo('locale/**/*.cfg'))
                             ->willReturn($fileNames);

        $reader = new LocaleReader($this->modFileManager);
        $result = $this->invokeMethod($reader, 'getLocaleFileNames', $modName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the readLocaleFile method.
     * @throws ReflectionException
     * @covers ::readLocaleFile
     */
    public function testReadLocaleFile(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $contents = 'ghi';
        $parsedFile = ['jkl' => 'mno'];

        $this->modFileManager->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo($fileName))
                             ->willReturn($contents);

        /* @var LocaleReader&MockObject $reader */
        $reader = $this->getMockBuilder(LocaleReader::class)
                       ->setMethods(['parseLocaleFile'])
                       ->setConstructorArgs([$this->modFileManager])
                       ->getMock();
        $reader->expects($this->once())
               ->method('parseLocaleFile')
               ->with($this->identicalTo($contents))
               ->willReturn($parsedFile);

        $result = $this->invokeMethod($reader, 'readLocaleFile', $modName, $fileName);
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

        $reader = new LocaleReader($this->modFileManager);

        $result = $this->invokeMethod($reader, 'parseLocaleFile', $content);
        $this->assertEquals($expectedResult, $result);
    }
}
