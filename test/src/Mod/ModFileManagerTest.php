<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InvalidInfoJsonFileException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use JMS\Serializer\SerializerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModFileManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\ModFileManager
 */
class ModFileManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked serializer interface.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $modsDirectory = 'abc';
        $manager = new ModFileManager($this->serializer, $modsDirectory);

        $this->assertSame($this->serializer, $this->extractProperty($manager, 'serializer'));
        $this->assertSame($modsDirectory, $this->extractProperty($manager, 'modsDirectory'));
    }

    /**
     * Tests the getInfo method.
     * @throws ExportException
     * @covers ::getInfo
     */
    public function testGetInfo(): void
    {
        $modName = 'abc';
        $contents = 'def';

        /* @var InfoJson&MockObject $infoJson */
        $infoJson = $this->createMock(InfoJson::class);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($contents),
                             $this->identicalTo(InfoJson::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($infoJson);

        /* @var ModFileManager&MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->onlyMethods(['readFile'])
                        ->setConstructorArgs([$this->serializer, 'foo'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('readFile')
                ->with($this->identicalTo($modName), $this->identicalTo('info.json'))
                ->willReturn($contents);

        $result = $manager->getInfo($modName);

        $this->assertSame($infoJson, $result);
    }

    /**
     * Tests the getInfo method.
     * @throws ExportException
     * @covers ::getInfo
     */
    public function testGetInfoWithException(): void
    {
        $modName = 'abc';
        $contents = 'def';

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($contents),
                             $this->identicalTo(InfoJson::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidInfoJsonFileException::class);

        /* @var ModFileManager&MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->onlyMethods(['readFile'])
                        ->setConstructorArgs([$this->serializer, 'foo'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('readFile')
                ->with($this->identicalTo($modName), $this->identicalTo('info.json'))
                ->willReturn($contents);

        $manager->getInfo($modName);
    }

    /**
     * Tests the readFile method.
     * @throws ExportException
     * @covers ::readFile
     */
    public function testReadFile(): void
    {
        $modName = 'abc';
        $fileName = 'def';
        $contents = 'ghi';

        vfsStream::setup('root');
        file_put_contents(vfsStream::url('root/def'), $contents);

        /* @var ModFileManager&MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->onlyMethods(['getLocalDirectory'])
                        ->setConstructorArgs([$this->serializer, 'foo'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('getLocalDirectory')
                ->with($this->identicalTo($modName))
                ->willReturn(vfsStream::url('root'));

        $result = $manager->readFile($modName, $fileName);

        $this->assertSame($contents, $result);
    }

    /**
     * Tests the readFile method.
     * @throws ExportException
     * @covers ::readFile
     */
    public function testReadFileWithException(): void
    {
        $modName = 'abc';
        $fileName = 'def';

        vfsStream::setup('root');

        $this->expectException(FileNotFoundInModException::class);

        /* @var ModFileManager&MockObject $manager */
        $manager = $this->getMockBuilder(ModFileManager::class)
                        ->onlyMethods(['getLocalDirectory'])
                        ->setConstructorArgs([$this->serializer, 'foo'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('getLocalDirectory')
                ->with($this->identicalTo($modName))
                ->willReturn(vfsStream::url('root'));

        $manager->readFile($modName, $fileName);
    }

    /**
     * Tests the getLocalDirectory method.
     * @covers ::getLocalDirectory
     */
    public function testGetLocalDirectory(): void
    {
        $modsDirectory = 'abc';
        $modName = 'def';
        $expectedResult = 'abc/def';

        $manager = new ModFileManager($this->serializer, $modsDirectory);
        $result = $manager->getLocalDirectory($modName);

        $this->assertSame($expectedResult, $result);
    }
}
