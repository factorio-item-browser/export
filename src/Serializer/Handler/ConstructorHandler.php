<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * The handler using the constructor of a class for deserializing, and the string conversion for serializing.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConstructorHandler implements SubscribingHandlerInterface
{
    /**
     * Returns the methods to subscribe to.
     * @return array<mixed>
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'constructor',
                'method' => 'serialize',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'constructor',
                'method' => 'deserialize',
            ],
        ];
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param mixed $value
     * @return string
     */
    public function serialize(SerializationVisitorInterface $visitor, $value): string
    {
        return (string) $value;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array<mixed> $type
     * @return mixed
     */
    public function deserialize(DeserializationVisitorInterface $visitor, $data, array $type)
    {
        $class = $type['params'][0]['name'] ?? '';
        return new $class((string) $data);
    }
}
