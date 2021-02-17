<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Serializer\Handler;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\Export\Serializer\Handler\ConstructorHandler;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ConstructorHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Serializer\Handler\ConstructorHandler
 */
class ConstructorHandlerTest extends TestCase
{
    public function testGetSubscribingMethods(): void
    {
        $result = ConstructorHandler::getSubscribingMethods();
        $this->assertCount(2, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideSerializeAndDeserialize(): array
    {
        return [
            [new Version('1.2.3'), '1.2.3', ['params' => [['name' => Version::class]]]],
            [new Dependency('base > 1.2.3'), 'base > 1.2.3', ['params' => [['name' => Dependency::class]]]],
        ];
    }

    /**
     * @param object $value
     * @param string $serializedValue
     * @param array<mixed> $type
     * @dataProvider provideSerializeAndDeserialize
     */
    public function testSerializeAndDeserialize(object $value, string $serializedValue, array $type): void
    {
        $instance = new ConstructorHandler();

        $this->assertSame(
            $serializedValue,
            $instance->serialize($this->createMock(SerializationVisitorInterface::class), $value),
        );
        $this->assertEquals(
            $value,
            $instance->deserialize($this->createMock(DeserializationVisitorInterface::class), $serializedValue, $type),
        );
    }
}
