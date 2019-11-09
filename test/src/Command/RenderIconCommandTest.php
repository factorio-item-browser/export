<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Command\RenderIconCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\IconRenderException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the RenderIconCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\RenderIconCommand
 */
class RenderIconCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked icon renderer.
     * @var IconRenderer&MockObject
     */
    protected $iconRenderer;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->iconRenderer = $this->createMock(IconRenderer::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new RenderIconCommand($this->console, $this->iconRenderer, $this->serializer);

        $this->assertSame($this->console, $this->extractProperty($command, 'console'));
        $this->assertSame($this->iconRenderer, $this->extractProperty($command, 'iconRenderer'));
        $this->assertSame($this->serializer, $this->extractProperty($command, 'serializer'));
    }

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $renderedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);
        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        /* @var AdapterInterface&MockObject $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);

        $this->console->expects($this->once())
                      ->method('writeData')
                      ->with($this->identicalTo($renderedIcon))
                      ->willReturnSelf();
        $this->console->expects($this->never())
                      ->method('writeException');

        /* @var RenderIconCommand&MockObject $command */
        $command = $this->getMockBuilder(RenderIconCommand::class)
                        ->onlyMethods(['getIconFromRoute', 'renderIcon'])
                        ->setConstructorArgs([$this->console, $this->iconRenderer, $this->serializer])
                        ->getMock();
        $command->expects($this->once())
                ->method('getIconFromRoute')
                ->with($this->identicalTo($route))
                ->willReturn($icon);
        $command->expects($this->once())
                ->method('renderIcon')
                ->with($this->identicalTo($icon))
                ->willReturn($renderedIcon);

        $result = $command($route, $consoleAdapter);

        $this->assertSame(0, $result);
    }

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvokeWithException(): void
    {
        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        /* @var AdapterInterface&MockObject $consoleAdapter */
        $consoleAdapter = $this->createMock(AdapterInterface::class);
        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->console->expects($this->never())
                      ->method('writeData');
        $this->console->expects($this->once())
                      ->method('writeException')
                      ->with($this->identicalTo($exception))
                      ->willReturnSelf();

        /* @var RenderIconCommand&MockObject $command */
        $command = $this->getMockBuilder(RenderIconCommand::class)
                        ->onlyMethods(['getIconFromRoute', 'renderIcon'])
                        ->setConstructorArgs([$this->console, $this->iconRenderer, $this->serializer])
                        ->getMock();
        $command->expects($this->once())
                ->method('getIconFromRoute')
                ->with($this->identicalTo($route))
                ->willReturn($icon);
        $command->expects($this->once())
                ->method('renderIcon')
                ->with($this->identicalTo($icon))
                ->willThrowException($exception);

        $result = $command($route, $consoleAdapter);

        $this->assertSame(1, $result);
    }

    /**
     * Tests the getIconFromRoute method.
     * @throws ReflectionException
     * @covers ::getIconFromRoute
     */
    public function testGetIconFromRoute(): void
    {
        $serializedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with($this->identicalTo('icon'))
              ->willReturn($serializedIcon);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedIcon),
                             $this->identicalTo(Icon::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($icon);

        $command = new RenderIconCommand($this->console, $this->iconRenderer, $this->serializer);
        $result = $this->invokeMethod($command, 'getIconFromRoute', $route);

        $this->assertSame($icon, $result);
    }

    /**
     * Tests the getIconFromRoute method.
     * @throws ReflectionException
     * @covers ::getIconFromRoute
     */
    public function testGetIconFromRouteWithException(): void
    {
        $serializedIcon = 'abc';

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with($this->identicalTo('icon'))
              ->willReturn($serializedIcon);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedIcon),
                             $this->identicalTo(Icon::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InternalException::class);

        $command = new RenderIconCommand($this->console, $this->iconRenderer, $this->serializer);
        $this->invokeMethod($command, 'getIconFromRoute', $route);
    }

    /**
     * Tests the renderIcon method.
     * @throws ReflectionException
     * @covers ::renderIcon
     */
    public function testRenderIcon(): void
    {
        $renderedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        $this->iconRenderer->expects($this->once())
                           ->method('render')
                           ->with($this->identicalTo($icon))
                           ->willReturn($renderedIcon);

        $command = new RenderIconCommand($this->console, $this->iconRenderer, $this->serializer);
        $result = $this->invokeMethod($command, 'renderIcon', $icon);

        $this->assertSame($renderedIcon, $result);
    }

    /**
     * Tests the renderIcon method.
     * @throws ReflectionException
     * @covers ::renderIcon
     */
    public function testRenderIconWithException(): void
    {
        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        $this->iconRenderer->expects($this->once())
                           ->method('render')
                           ->with($this->identicalTo($icon))
                           ->willThrowException($this->createMock(Exception::class));

        $this->expectException(IconRenderException::class);

        $command = new RenderIconCommand($this->console, $this->iconRenderer, $this->serializer);
        $this->invokeMethod($command, 'renderIcon', $icon);
    }
}
