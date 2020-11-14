<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\IconDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\IconDumpProcessor
 */
class IconDumpProcessorTest extends TestCase
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
        $instance = new IconDumpProcessor($this->exportSerializer);

        $this->assertSame($this->exportSerializer, $this->extractProperty($instance, 'exportSerializer'));
        $this->assertSame('icon', $instance->getType());
    }

    /**
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $icon1 = $this->createMock(Icon::class);
        $icon2 = $this->createMock(Icon::class);

        $dump = new Dump();
        $dump->icons = [$icon1];

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($serializedDump),
                                   $this->identicalTo(Icon::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($icon2);

        $instance = new IconDumpProcessor($this->exportSerializer);
        $instance->process($serializedDump, $dump);

        $this->assertContains($icon1, $dump->icons);
        $this->assertContains($icon2, $dump->icons);
    }
}
