<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\ModFile;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\ModFile\DependencyReader;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DependencyReader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\ModFile\DependencyReader
 */
class DependencyReaderTest extends TestCase
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

        $reader = new DependencyReader($modFileManager);
        $this->assertSame($modFileManager, $this->extractProperty($reader, 'modFileManager'));
    }

    /**
     * Tests the read method.
     * @covers ::read
     * @throws ExportException
     */
    public function testRead(): void
    {
        $mod = new Mod();
        $infoJson = new DataContainer([
            'dependencies' => [
                'abc',
                'def',
                'ghi',
            ]
        ]);
        $baseDependency = new Dependency();
        $dependencyString1 = 'abc';
        $dependencyString2 = 'def';
        $dependencyString3 = 'ghi';
        $dependency1 = new Dependency();
        $dependency1->setRequiredModName('abc');
        $dependency2 = new Dependency();
        $dependency2->setRequiredModName('def');
        $expectedResult = [
            'base' => $baseDependency,
            'abc' => $dependency1,
            'def' => $dependency2,
        ];

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['getInfoJson'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($this->once())
                       ->method('getInfoJson')
                       ->with($mod)
                       ->willReturn($infoJson);

        /* @var DependencyReader|MockObject $reader */
        $reader = $this->getMockBuilder(DependencyReader::class)
                       ->setMethods(['createDependency', 'parseDependency'])
                       ->setConstructorArgs([$modFileManager])
                       ->getMock();
        $reader->expects($this->once())
               ->method('createDependency')
               ->with('base', '', true)
               ->willReturn($baseDependency);
        $reader->expects($this->exactly(3))
               ->method('parseDependency')
               ->withConsecutive(
                   [$dependencyString1],
                   [$dependencyString2],
                   [$dependencyString3]
               )
               ->willReturnOnConsecutiveCalls(
                   $dependency1,
                   null,
                   $dependency2
               );

        $result = $reader->read($mod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the parseDependency test.
     * @return array
     */
    public function provideParseDependency(): array
    {
        $dependency = new Dependency();
        $dependency->setRequiredModName('test');

        return [
            ['test', false, 'test', '', true, $dependency, $dependency],
            ['test>=1.2.3', false, 'test', '1.2.3', true, $dependency, $dependency],
            ['test>1.2.3', false, 'test', '1.2.3', true, $dependency, $dependency],
            ['test >= 1.2.3', false, 'test', '1.2.3', true, $dependency, $dependency],
            ['?test', false, 'test', '', false, $dependency, $dependency],
            ['?test >= 1.2.3', false, 'test', '1.2.3', false, $dependency, $dependency],

            ['test < 1.2.3', false, 'test', '1.2.3', true, null, null],
            ['test < fail', true, null, null, null, null, null],
        ];
    }

    /**
     * Tests the parseDependency method.
     * @param string $dependencyString
     * @param bool $expectException
     * @param null|string $expectedModName
     * @param null|string $expectedVersion
     * @param bool|null $expectedIsMandatory
     * @param Dependency|null $resultCreateDependency
     * @param Dependency|null $expectedResult
     * @throws ReflectionException
     * @covers ::parseDependency
     * @dataProvider provideParseDependency
     */
    public function testParseDependency(
        string $dependencyString,
        bool $expectException,
        ?string $expectedModName,
        ?string $expectedVersion,
        ?bool $expectedIsMandatory,
        ?Dependency $resultCreateDependency,
        ?Dependency $expectedResult
    ): void {
        /* @var DependencyReader|MockObject $reader */
        $reader = $this->getMockBuilder(DependencyReader::class)
                       ->setMethods(['createDependency'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $reader->expects($resultCreateDependency === null ? $this->never() : $this->once())
               ->method('createDependency')
               ->with($expectedModName, $expectedVersion, $expectedIsMandatory)
               ->willReturn($resultCreateDependency);

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        $result = $this->invokeMethod($reader, 'parseDependency', $dependencyString);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createDependency method.
     * @throws ReflectionException
     * @covers ::createDependency
     */
    public function testCreateDependency(): void
    {
        $modName = 'abc';
        $version = '1.2';
        $isMandatory = true;
        $expectedResult = new Dependency();
        $expectedResult->setRequiredModName('abc')
                       ->setRequiredVersion('1.2.0')
                       ->setIsMandatory(true);

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        $reader = new DependencyReader($modFileManager);
        $result = $this->invokeMethod($reader, 'createDependency', $modName, $version, $isMandatory);
        $this->assertEquals($expectedResult, $result);
    }
}
