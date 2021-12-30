<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\DumpOutputProcessor;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\ExportData;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the DumpOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpOutputProcessor
 */
class DumpOutputProcessorTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DumpOutputProcessor&MockObject
     */
    private function createInstance(array $mockedMethods = []): DumpOutputProcessor
    {
        return $this->getMockBuilder(DumpOutputProcessor::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->serializer,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideProcessLine(): array
    {
        return [
            ['>DUMP>icon>foo<', 'foo', Icon::class, 'getIcons'],
            ['>DUMP>item>foo<', 'foo', Item::class, 'getItems'],
            ['>DUMP>machine>foo<', 'foo', Machine::class, 'getMachines'],
            ['>DUMP>recipe>foo<', 'foo', Recipe::class, 'getRecipes'],
        ];
    }

    /**
     * @throws ExportException
     * @dataProvider provideProcessLine
     */
    public function testProcessLine(
        string $outputLine,
        string $expectedData,
        string $expectedClass,
        string $getterMethod,
    ): void {
        $object = $this->createMock(stdClass::class);

        $items = $this->createMock(ChunkedCollection::class);
        $items->expects($this->once())
              ->method('add')
              ->with($this->identicalTo($object));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method($getterMethod)
                   ->willReturn($items);

        $instance = $this->createInstance(['createObject']);
        $instance->expects($this->once())
                 ->method('createObject')
                 ->with($this->identicalTo($expectedData), $this->identicalTo($expectedClass))
                 ->willReturn($object);

        $instance->processLine($outputLine, $exportData);
    }

    /**
     * @return array<mixed>
     */
    public function provideProcessLineWithMismatch(): array
    {
        return [
            ['>DUMP>unknown>foo<'],
            ['Not a dump'],
        ];
    }

    /**
     * @throws ExportException
     * @dataProvider provideProcessLineWithMismatch
     */
    public function testProcessLineWithMismatch(string $outputLine): void
    {
        $exportData = $this->createMock(ExportData::class);

        $instance = $this->createInstance(['createObject']);
        $instance->expects($this->never())
                 ->method('createObject');

        $instance->processLine($outputLine, $exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateObject(): void
    {
        $data = 'abc';
        $class = 'def';
        $object = new stdClass();

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with($this->identicalTo($data), $this->identicalTo($class), $this->identicalTo('json'))
                         ->willReturn($object);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createObject', $data, $class);

        $this->assertSame($object, $result);
    }

    /**
     * @throws ExportException
     */
    public function testProcessExitCode(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $instance = $this->createInstance();
        $instance->processExitCode(0, $exportData);

        $this->addToAssertionCount(1);
    }
}
