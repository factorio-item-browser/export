<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Item;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ItemDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ItemDumpProcessor
 */
class ItemDumpProcessorTest extends TestCase
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
        $instance = new ItemDumpProcessor($this->exportSerializer);

        $this->assertSame($this->exportSerializer, $this->extractProperty($instance, 'exportSerializer'));
        $this->assertSame('item', $instance->getType());
    }

    /**
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);

        $dump = new Dump();
        $dump->items = [$item1];

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($serializedDump),
                                   $this->identicalTo(Item::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($item2);

        $instance = new ItemDumpProcessor($this->exportSerializer);
        $instance->process($serializedDump, $dump);

        $this->assertContains($item1, $dump->items);
        $this->assertContains($item2, $dump->items);
    }
}
