<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export;

use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Serializer\Handler\ConstructorHandler;
use FactorioItemBrowser\Export\Serializer\Handler\RawHandler;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
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
        $config = require(__DIR__ . '/../../config/autoload/export.global.php');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                      [ConstructorHandler::class, new ConstructorHandler()],
                      [RawHandler::class, new RawHandler()],
                  ]);

        $serializerFactory = new JmsSerializerFactory(ConfigKey::MAIN, ConfigKey::SERIALIZER);
        return $serializerFactory($container, SerializerInterface::class); // @phpstan-ignore-line
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
