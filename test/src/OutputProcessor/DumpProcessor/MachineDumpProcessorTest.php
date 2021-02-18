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
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\MachineDumpProcessor
 */
class MachineDumpProcessorTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $exportSerializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exportSerializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::getType
     */
    public function testConstruct(): void
    {
        $instance = new MachineDumpProcessor($this->exportSerializer);

        $this->assertSame($this->exportSerializer, $this->extractProperty($instance, 'exportSerializer'));
        $this->assertSame('machine', $instance->getType());
    }

    /**
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);

        $dump = new Dump();
        $dump->machines = [$machine1];

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($serializedDump),
                                   $this->identicalTo(Machine::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($machine2);

        $instance = new MachineDumpProcessor($this->exportSerializer);
        $instance->process($serializedDump, $dump);

        $this->assertContains($machine1, $dump->machines);
        $this->assertContains($machine2, $dump->machines);
    }
}
