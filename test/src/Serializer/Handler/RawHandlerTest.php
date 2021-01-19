<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Serializer\Handler;

use FactorioItemBrowser\Export\Serializer\Handler\RawHandler;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RawHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Serializer\Handler\RawHandler
 */
class RawHandlerTest extends TestCase
{
    public function testGetSubscribingMethods(): void
    {
        $result = RawHandler::getSubscribingMethods();
        $this->assertCount(1, $result);
    }

    public function testDeserializeRaw(): void
    {
        $value = ['abc', 'def'];
        $type = ['def' => 'ghi'];

        $visitor = $this->createMock(DeserializationVisitorInterface::class);

        $handler = new RawHandler();
        $result = $handler->deserializeRaw($visitor, $value, $type);

        $this->assertSame($value, $result);
    }
}
