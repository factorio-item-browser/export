<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * The handler for the localised strings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RawHandler implements SubscribingHandlerInterface
{
    /**
     * Returns the methods to subscribe to.
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'raw',
                'method' => 'deserializeRaw',
            ],
        ];
    }

    /**
     * Deserializes the localised string.
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $value
     * @param array $type
     * @return mixed
     */
    public function deserializeRaw(DeserializationVisitorInterface $visitor, $value, array $type)
    {
        return $value;
    }
}
