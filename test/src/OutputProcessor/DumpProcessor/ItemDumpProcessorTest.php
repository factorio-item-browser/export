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
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ItemDumpProcessor
 */
class ItemDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createInstance(): ItemDumpProcessor
    {
        return new ItemDumpProcessor(
            $this->serializer,
        );
    }

    public function testGetType(): void
    {
        $expectedResult = 'item';

        $instance = $this->createInstance();
        $result = $instance->getType();

        $this->assertSame($expectedResult, $result);
    }

    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);

        $dump = new Dump();
        $dump->items = [$item1];

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedDump),
                             $this->identicalTo(Item::class),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($item2);

        $instance = $this->createInstance();
        $instance->process($serializedDump, $dump);

        $this->assertContains($item1, $dump->items);
        $this->assertContains($item2, $dump->items);
    }
}
