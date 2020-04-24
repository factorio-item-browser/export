<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export;

use FactorioItemBrowser\Export\Serializer\SerializerFactory;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The test case for the serializing and deserializing of objects.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class SerializerTestCase extends TestCase
{
    /**
     * Creates and returns the serializer.
     * @return SerializerInterface
     */
    protected function createSerializer(): SerializerInterface
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $serializerFactory = new SerializerFactory();
        return $serializerFactory($container, SerializerInterface::class);
    }

    /**
     * Tests the deserializing.
     */
    public function testDeserialize(): void
    {
        $data = $this->getData();
        $expectedObject = $this->getObject();

        $serializer = $this->createSerializer();
        $result = $serializer->deserialize((string) json_encode($data), get_class($expectedObject), 'json');

        $this->assertEquals($expectedObject, $result);
    }

    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    abstract protected function getObject(): object;

    /**
     * Returns the serialized data.
     * @return array<mixed>
     */
    abstract protected function getData(): array;
}
