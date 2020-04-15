<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Serializer\Handler;

use FactorioItemBrowser\Export\Serializer\Handler\RawHandler;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RawHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Serializer\Handler\RawHandler
 */
class RawHandlerTest extends TestCase
{
    /**
     * Tests the getSubscribingMethods method.
     * @covers ::getSubscribingMethods
     */
    public function testGetSubscribingMethods(): void
    {
        $expectedResult = [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'raw',
                'method' => 'deserializeRaw',
            ],
        ];

        $result = RawHandler::getSubscribingMethods();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the deserializeRaw method.
     * @covers ::deserializeRaw
     */
    public function testDeserializeRaw(): void
    {
        $value = ['abc', 'def'];
        $type = ['def' => 'ghi'];

        /* @var DeserializationVisitorInterface&MockObject $visitor */
        $visitor = $this->createMock(DeserializationVisitorInterface::class);

        $handler = new RawHandler();
        $result = $handler->deserializeRaw($visitor, $value, $type);

        $this->assertSame($value, $result);
    }
}
