<?php

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Entity\Dump\ControlStage;
use FactorioItemBrowser\Export\Entity\Dump\DataStage;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\InvalidDumpException;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the DumpExtractor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\DumpExtractor
 */
class DumpExtractorTest extends TestCase
{
    use ReflectionTrait;
    
    /**
     * The mocked serializer.
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
        $dumpExtractor = new DumpExtractor($this->serializer);
        
        $this->assertSame($this->serializer, $this->extractProperty($dumpExtractor, 'serializer'));
    }

    /**
     * Tests the extract method.
     * @throws ExportException
     * @covers ::extract
     */
    public function testExtract(): void
    {
        $output = 'abc';
        $dataStageData = 'def';
        $controlStageData = 'ghi';
        $modNames = ['jkl', 'mno'];

        /* @var DataStage&MockObject $dataStage */
        $dataStage = $this->createMock(DataStage::class);
        /* @var ControlStage&MockObject $controlStage */
        $controlStage = $this->createMock(ControlStage::class);
        
        /* @var DumpExtractor&MockObject $dumpExtractor */
        $dumpExtractor = $this->getMockBuilder(DumpExtractor::class)
                              ->onlyMethods(['detectModOrder', 'extractRawDumpData', 'parseDump'])
                              ->setConstructorArgs([$this->serializer])
                              ->getMock();
        $dumpExtractor->expects($this->once())
                      ->method('detectModOrder')
                      ->with($output)
                      ->willReturn($modNames);
        $dumpExtractor->expects($this->exactly(2))
                      ->method('extractRawDumpData')
                      ->withConsecutive(
                          [$this->identicalTo($output), $this->identicalTo('data')],
                          [$this->identicalTo($output), $this->identicalTo('control')]
                      )
                      ->willReturnOnConsecutiveCalls(
                          $dataStageData,
                          $controlStageData
                      );
        $dumpExtractor->expects($this->exactly(2))
                      ->method('parseDump')
                      ->withConsecutive(
                          [
                              $this->identicalTo('data'),
                              $this->identicalTo($dataStageData),
                              $this->identicalTo(DataStage::class),
                          ],
                          [
                              $this->identicalTo('control'),
                              $this->identicalTo($controlStageData),
                              $this->identicalTo(ControlStage::class),
                          ]
                      )
                      ->willReturnOnConsecutiveCalls(
                          $dataStage,
                          $controlStage
                      );

        $result = $dumpExtractor->extract($output);
        $this->assertSame($dataStage, $result->getDataStage());
        $this->assertSame($controlStage, $result->getControlStage());
    }

    /**
     * Tests the extractRawDumpData method.
     * @throws ReflectionException
     * @covers ::extractRawDumpData
     */
    public function testExtractRawDumpData(): void
    {
        $output = 'abc>>>FOO>>>def<<<FOO<<<ghi';
        $stage = 'foo';
        $expectedResult = 'def';

        $dumpExtractor = new DumpExtractor($this->serializer);

        $result = $this->invokeMethod($dumpExtractor, 'extractRawDumpData', $output, $stage);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the extractRawDumpData test.
     * @return array
     */
    public function provideExtractRawDumpDataWithException(): array
    {
        return [
            ['abc>>>FOO>>>def', 'foo'], // Missing end placeholder
            ['abc<<<FOO<<<def', 'foo'], // Missing start placeholder
            ['abc<<<FOO<<<def>>>FOO>>>ghi', 'foo'], // Wrong order of placeholders
        ];
    }

    /**
     * Tests the extractRawDumpData method.
     * @param string $output
     * @param string $stage
     * @throws ReflectionException
     * @covers ::extractRawDumpData
     * @dataProvider provideExtractRawDumpDataWithException
     */
    public function testExtractRawDumpDataWithException(string $output, string $stage): void
    {
        $this->expectException(InvalidDumpException::class);

        $dumpExtractor = new DumpExtractor($this->serializer);

        $this->invokeMethod($dumpExtractor, 'extractRawDumpData', $output, $stage);
    }

    /**
     * Tests the parseDump method.
     * @throws ReflectionException
     * @covers ::parseDump
     */
    public function testParseDump(): void
    {
        $stage = 'abc';
        $dumpData = 'def';
        $className = 'ghi';

        /* @var stdClass&MockObject $object */
        $object = $this->createMock(stdClass::class);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($dumpData),
                             $this->identicalTo($className),
                             $this->identicalTo('json')
                         )
                         ->willReturn($object);

        $dumpExtractor = new DumpExtractor($this->serializer);

        $result = $this->invokeMethod($dumpExtractor, 'parseDump', $stage, $dumpData, $className);

        $this->assertSame($object, $result);
    }

    /**
     * Tests the parseDump method.
     * @throws ReflectionException
     * @covers ::parseDump
     */
    public function testParseDumpWithException(): void
    {
        $stage = 'abc';
        $dumpData = 'def';
        $className = 'ghi';

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($dumpData),
                             $this->identicalTo($className),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidDumpException::class);

        $dumpExtractor = new DumpExtractor($this->serializer);

        $this->invokeMethod($dumpExtractor, 'parseDump', $stage, $dumpData, $className);
    }

    /**
     * Tests the detectModOrder method.
     * @throws ReflectionException
     * @covers ::detectModOrder
     */
    public function testDetectModOrder(): void
    {
        $output = <<<EOT
   7.754 Checksum for core: 2087614386
   7.754 Checksum of base: 1061071205
   7.754 Checksum of foo: 1234567890
   7.754 Checksum of Dump: 9876543210
EOT;
        $expectedResult = ['base', 'foo'];

        $dumpExtractor = new DumpExtractor($this->serializer);
        $result = $this->invokeMethod($dumpExtractor, 'detectModOrder', $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the detectModOrder method.
     * @throws ReflectionException
     * @covers ::detectModOrder
     */
    public function testDetectModOrderWithMissingDumpMod(): void
    {
        $output = <<<EOT
   7.754 Checksum for core: 2087614386
   7.754 Checksum of base: 1061071205
   7.754 Checksum of foo: 1234567890
EOT;

        $this->expectException(InternalException::class);

        $dumpExtractor = new DumpExtractor($this->serializer);
        $this->invokeMethod($dumpExtractor, 'detectModOrder', $output);
    }

    /**
     * Tests the detectModOrder method.
     * @throws ReflectionException
     * @covers ::detectModOrder
     */
    public function testDetectModOrderWithInvalidOutput(): void
    {
        $output = 'invalid';

        $this->expectException(InternalException::class);

        $dumpExtractor = new DumpExtractor($this->serializer);
        $this->invokeMethod($dumpExtractor, 'detectModOrder', $output);
    }
}
