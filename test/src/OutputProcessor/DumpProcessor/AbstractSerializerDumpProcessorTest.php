<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\AbstractSerializerDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the AbstractSerializerDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\AbstractSerializerDumpProcessor
 */
class AbstractSerializerDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return AbstractSerializerDumpProcessor<stdClass>&MockObject
     */
    private function createInstance(array $mockedMethods = []): AbstractSerializerDumpProcessor
    {
        return $this->getMockBuilder(AbstractSerializerDumpProcessor::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->serializer,
                    ])
                    ->getMockForAbstractClass();
    }

    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $entityClass = 'stdClass';
        $entity = new stdClass();
        $dump = new Dump();

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedDump),
                             $this->identicalTo($entityClass),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($entity);

        $instance = $this->createInstance(['getEntityClass', 'addEntityToDump']);
        $instance->expects($this->once())
                 ->method('getEntityClass')
                 ->willReturn($entityClass);
        $instance->expects($this->once())
                 ->method('addEntityToDump')
                 ->with($this->identicalTo($entity), $this->identicalTo($dump));

        $instance->process($serializedDump, $dump);
    }
}
