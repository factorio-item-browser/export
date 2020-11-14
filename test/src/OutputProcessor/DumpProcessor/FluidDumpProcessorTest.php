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
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\FluidDumpProcessor
 */
class FluidDumpProcessorTest extends TestCase
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
        $instance = new FluidDumpProcessor($this->exportSerializer);

        $this->assertSame($this->exportSerializer, $this->extractProperty($instance, 'exportSerializer'));
        $this->assertSame('fluid', $instance->getType());
    }

    /**
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $fluid1 = $this->createMock(Fluid::class);
        $fluid2 = $this->createMock(Fluid::class);

        $dump = new Dump();
        $dump->fluids = [$fluid1];

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($serializedDump),
                                   $this->identicalTo(Fluid::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($fluid2);

        $instance = new FluidDumpProcessor($this->exportSerializer);
        $instance->process($serializedDump, $dump);

        $this->assertContains($fluid1, $dump->fluids);
        $this->assertContains($fluid2, $dump->fluids);
    }
}
