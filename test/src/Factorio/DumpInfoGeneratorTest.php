<?php

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\DumpInfoGenerator;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DumpInfoGenerator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\DumpInfoGenerator
 */
class DumpInfoGeneratorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        $modsDirectory = 'abc';

        $generator = new DumpInfoGenerator($modRegistry, $modsDirectory);

        $this->assertSame($modRegistry, $this->extractProperty($generator, 'modRegistry'));
        $this->assertSame($modsDirectory, $this->extractProperty($generator, 'modsDirectory'));
    }

    /**
     * Tests the generate method.
     * @throws ExportException
     * @covers ::generate
     */
    public function testGenerate(): void
    {
        $baseMod = (new Mod())->setName('base');
        $json = ['abc' => 'def'];

        /* @var DumpInfoGenerator|MockObject $generator */
        $generator = $this->getMockBuilder(DumpInfoGenerator::class)
                          ->setMethods(['fetchBaseMod', 'generateInfoJson', 'writeInfoJson'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $generator->expects($this->once())
                  ->method('fetchBaseMod')
                  ->willReturn($baseMod);
        $generator->expects($this->once())
                  ->method('generateInfoJson')
                  ->with($baseMod)
                  ->willReturn($json);
        $generator->expects($this->once())
                  ->method('writeInfoJson')
                  ->with($json);

        $generator->generate();
    }

    /**
     * Provides the data for the fetchBaseMod test.
     * @return array
     */
    public function provideFetchBaseMod(): array
    {
        return [
            [(new Mod())->setName('base'), false],
            [null, true],
        ];
    }

    /**
     * Tests the fetchBaseMod method.
     * @param Mod|null $baseMod
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::fetchBaseMod
     * @dataProvider provideFetchBaseMod
     */
    public function testFetchBaseMod(?Mod $baseMod, bool $expectException): void
    {
        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('get')
                    ->with('base')
                    ->willReturn($baseMod);

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        $generator = new DumpInfoGenerator($modRegistry, 'foo');

        $result = $this->invokeMethod($generator, 'fetchBaseMod');
        $this->assertSame($baseMod, $result);
    }


    /**
     * Tests the generateInfoJson method.
     * @throws ReflectionException
     * @covers ::generateInfoJson
     */
    public function testGenerateInfoJson(): void
    {
        $baseMod = (new Mod())->setVersion('1.2.3');
        $dependencies = ['?abc', '?def'];
        $expectedResult = [
            'name' => 'Dump',
            'version' => '1.0.0',
            'factorio_version' => '1.2.3',
            'title' => 'BluePsyduck\'s Dump',
            'author' => 'BluePsyduck',
            'dependencies' => ['?abc', '?def'],
        ];

        /* @var DumpInfoGenerator|MockObject $generator */
        $generator = $this->getMockBuilder(DumpInfoGenerator::class)
                          ->setMethods(['createDependencies'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $generator->expects($this->once())
                  ->method('createDependencies')
                  ->willReturn($dependencies);

        $result = $this->invokeMethod($generator, 'generateInfoJson', $baseMod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createDependencies method.
     * @throws ReflectionException
     * @covers ::createDependencies
     */
    public function testCreateDependencies(): void
    {
        $modNames = ['abc', 'def'];
        $expectedResult = ['?abc', '?def'];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($modNames);

        $generator = new DumpInfoGenerator($modRegistry, 'foo');

        $result = $this->invokeMethod($generator, 'createDependencies');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the writeInfoJson test.
     * @return array
     */
    public function provideWriteInfoJson(): array
    {
        return [
            [0777, false],
            [0000, true],
        ];
    }

    /**
     * Tests the writeInfoJson method.
     * @param int $directoryPermissions
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::writeInfoJson
     * @dataProvider provideWriteInfoJson
     */
    public function testWriteInfoJson(int $directoryPermissions, bool $expectException): void
    {
        $root = vfsStream::setup('root');
        $directory = vfsStream::newDirectory('Dump_1.0.0', $directoryPermissions);
        $root->addChild($directory);

        $modsDirectory = vfsStream::url('root');

        $json = ['abc' => 'def', 'ghi' => 'jkl'];
        $expectedContent = '{"abc":"def","ghi":"jkl"}';

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $generator = new DumpInfoGenerator($modRegistry, $modsDirectory);

        $this->invokeMethod($generator, 'writeInfoJson', $json);

        if (!$expectException) {
            $this->assertSame($expectedContent, file_get_contents($modsDirectory . '/Dump_1.0.0/info.json'));
        }
    }
}
