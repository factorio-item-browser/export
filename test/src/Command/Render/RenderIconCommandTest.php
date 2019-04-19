<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Render\RenderIconCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\Config;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Registry\ContentRegistry;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the RenderIconCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Render\RenderIconCommand
 */
class RenderIconCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked icon registry.
     * @var EntityRegistry&MockObject
     */
    protected $iconRegistry;

    /**
     * The mocked rendered icon registry.
     * @var ContentRegistry&MockObject
     */
    protected $renderedIconRegistry;

    /**
     * The mocked icon renderer.
     * @var IconRenderer&MockObject
     */
    protected $iconRenderer;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iconRegistry = $this->createMock(EntityRegistry::class);
        $this->renderedIconRegistry = $this->createMock(ContentRegistry::class);
        $this->iconRenderer = $this->createMock(IconRenderer::class);
    }

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $command = new RenderIconCommand($this->iconRegistry, $this->renderedIconRegistry, $this->iconRenderer);

        $this->assertSame($this->iconRegistry, $this->extractProperty($command, 'iconRegistry'));
        $this->assertSame($this->renderedIconRegistry, $this->extractProperty($command, 'renderedIconRegistry'));
        $this->assertSame($this->iconRenderer, $this->extractProperty($command, 'iconRenderer'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     */
    public function testExecute(): void
    {
        $iconHash = 'abc';
        $size = 64;
        $renderedIcon = 'def';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->exactly(2))
              ->method('getMatchedParam')
              ->withConsecutive(
                  [$this->identicalTo(ParameterName::ICON_HASH), $this->identicalTo('')],
                  [$this->identicalTo(ParameterName::SIZE), $this->identicalTo(Config::ICON_SIZE)]
              )
              ->willReturnOnConsecutiveCalls(
                  $iconHash,
                  $size
              );

        $this->iconRegistry->expects($this->once())
                           ->method('get')
                           ->with($this->identicalTo($iconHash))
                           ->willReturn($icon);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeAction')
                ->with($this->identicalTo('Rendering icon #abc'));

        $this->iconRenderer->expects($this->once())
                           ->method('render')
                           ->with($this->identicalTo($icon))
                           ->willReturn($renderedIcon);

        $this->renderedIconRegistry->expects($this->once())
                                   ->method('set')
                                   ->with($this->identicalTo($iconHash), $this->identicalTo($renderedIcon));

        $command = new RenderIconCommand($this->iconRegistry, $this->renderedIconRegistry, $this->iconRenderer);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the execute method without an actual icon.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecuteWithoutIcon(): void
    {
        $iconHash = 'abc';
        $size = 64;
        $icon = null;

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->exactly(2))
              ->method('getMatchedParam')
              ->withConsecutive(
                  [$this->identicalTo(ParameterName::ICON_HASH), $this->identicalTo('')],
                  [$this->identicalTo(ParameterName::SIZE), $this->identicalTo(Config::ICON_SIZE)]
              )
              ->willReturnOnConsecutiveCalls(
                  $iconHash,
                  $size
              );

        $this->iconRegistry->expects($this->once())
                           ->method('get')
                           ->with($this->identicalTo($iconHash))
                           ->willReturn($icon);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->never())
                ->method('writeAction');

        $this->iconRenderer->expects($this->never())
                           ->method('render');

        $this->renderedIconRegistry->expects($this->never())
                                   ->method('set');

        $this->expectException(CommandException::class);
        $this->expectExceptionCode(404);

        $command = new RenderIconCommand($this->iconRegistry, $this->renderedIconRegistry, $this->iconRenderer);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'execute', $route);
    }
}
