<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\MachineDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\MachineDumpProcessor
 */
class MachineDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createInstance(): MachineDumpProcessor
    {
        return new MachineDumpProcessor(
            $this->serializer,
        );
    }

    public function testGetType(): void
    {
        $expectedResult = 'machine';

        $instance = $this->createInstance();
        $result = $instance->getType();

        $this->assertSame($expectedResult, $result);
    }

    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);

        $dump = new Dump();
        $dump->machines = [$machine1];

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedDump),
                             $this->identicalTo(Machine::class),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($machine2);

        $instance = $this->createInstance();
        $instance->process($serializedDump, $dump);

        $this->assertContains($machine1, $dump->machines);
        $this->assertContains($machine2, $dump->machines);
    }
}
