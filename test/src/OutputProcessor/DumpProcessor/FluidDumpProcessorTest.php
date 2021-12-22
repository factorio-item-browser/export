<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\FluidDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the FluidDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\FluidDumpProcessor
 */
class FluidDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createInstance(): FluidDumpProcessor
    {
        return new FluidDumpProcessor(
            $this->serializer,
        );
    }

    public function testGetType(): void
    {
        $expectedResult = 'fluid';

        $instance = $this->createInstance();
        $result = $instance->getType();

        $this->assertSame($expectedResult, $result);
    }

    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $fluid1 = $this->createMock(Fluid::class);
        $fluid2 = $this->createMock(Fluid::class);

        $dump = new Dump();
        $dump->fluids = [$fluid1];

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedDump),
                             $this->identicalTo(Fluid::class),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($fluid2);

        $instance = $this->createInstance();
        $instance->process($serializedDump, $dump);

        $this->assertContains($fluid1, $dump->fluids);
        $this->assertContains($fluid2, $dump->fluids);
    }
}
