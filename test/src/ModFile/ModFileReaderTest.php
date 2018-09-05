<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\ModFile;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\Export\ModFile\ModFileReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModFileReader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\ModFile\ModFileReader
 */
class ModFileReaderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new ModFileReader($modFileManager);
        $this->assertSame($modFileManager, $this->extractProperty($reader, 'modFileManager'));
    }

    /**
     * Provides the data for the calculateChecksum test.
     * @return array
     */
    public function provideCalculateChecksum(): array
    {
        return [
            [true, '4ed9407630eb1000c0f6b63842defa7d'],
            [false, ''],
        ];
    }

    /**
     * Tests the calculateChecksum method.
     * @param bool $withFile
     * @param string $expectedResult
     * @covers ::calculateChecksum
     * @dataProvider provideCalculateChecksum
     */
    public function testCalculateChecksum(bool $withFile, string $expectedResult): void
    {
        $fileName = vfsStream::url('root/abc');

        $directory = vfsStream::setup('root');
        if ($withFile) {
            $directory->addChild(vfsStream::newFile($fileName));
            file_put_contents($fileName, 'def');
        }

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new ModFileReader($modFileManager);
        $result = $reader->calculateChecksum($fileName);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the read method.
     * @covers ::read
     * @throws ExportException
     */
    public function testRead(): void
    {
        $fileName = 'abc';
        $checksum = 'def';
        $directoryName = 'ghi';

        /* @var Mod|MockObject $mod */
        $mod = $this->getMockBuilder(Mod::class)
                    ->setMethods(['setDirectoryName'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $mod->expects($this->once())
            ->method('setDirectoryName')
            ->with($directoryName)
            ->willReturnSelf();


        /* @var ModFileReader|MockObject $reader */
        $reader = $this->getMockBuilder(ModFileReader::class)
                       ->setMethods(['createEntity', 'detectDirectoryName', 'parseInfoJson'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $reader->expects($this->once())
               ->method('createEntity')
               ->with($fileName, $checksum)
               ->willReturn($mod);
        $reader->expects($this->once())
               ->method('detectDirectoryName')
               ->with($fileName)
               ->willReturn($directoryName);
        $reader->expects($this->once())
               ->method('parseInfoJson')
               ->with($mod);

        $result = $reader->read($fileName, $checksum);
        $this->assertSame($mod, $result);
    }

    /**
     * Tests the createEntity method.
     * @covers ::createEntity
     * @throws ReflectionException
     */
    public function testCreateEntity(): void
    {
        $fileName = 'abc/def/ghi.zip';
        $checksum = 'jkl';
        $expectedResult = new Mod();
        $expectedResult->setFileName('ghi.zip')
                       ->setChecksum('jkl');

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new ModFileReader($modFileManager);
        $result = $this->invokeMethod($reader, 'createEntity', $fileName, $checksum);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the parseInfoJson method.
     * @covers ::parseInfoJson
     * @throws ReflectionException
     */
    public function testParseInfoJson(): void
    {
        $infoJson = new DataContainer([
            'name' => 'abc',
            'author' => 'def',
            'version' => '1.2',
            'title' => 'ghi',
            'description' => 'jkl',
        ]);

        $mod = new Mod();

        $expectedMod = new Mod();
        $expectedMod->setName('abc')
                    ->setAuthor('def')
                    ->setVersion('1.2.0');
        $expectedMod->getTitles()->setTranslation('en', 'ghi');
        $expectedMod->getDescriptions()->setTranslation('en', 'jkl');

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['getInfoJson'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($this->once())
                       ->method('getInfoJson')
                       ->with($mod)
                       ->willReturn($infoJson);

        $reader = new ModFileReader($modFileManager);

        $this->invokeMethod($reader, 'parseInfoJson', $mod);
        $this->assertEquals($expectedMod, $mod);
    }
}
