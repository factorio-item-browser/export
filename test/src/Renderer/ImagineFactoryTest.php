<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer;

use FactorioItemBrowser\Export\Renderer\ImagineFactory;
use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ImagineFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Renderer\ImagineFactory
 */
class ImagineFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $factory = new ImagineFactory();
        $result = $factory($container, ImagineInterface::class);

        $this->assertInstanceOf(Imagine::class, $result);
    }
}
