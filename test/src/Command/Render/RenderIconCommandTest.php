<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Render\RenderIconCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Registry\ContentRegistry;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
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
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Render\RenderIconCommand
 */
class RenderIconCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);
        /* @var ContentRegistry $renderedIconRegistry */
        $renderedIconRegistry = $this->createMock(ContentRegistry::class);
        /* @var IconRenderer $iconRenderer */
        $iconRenderer = $this->createMock(IconRenderer::class);

        $command = new RenderIconCommand($iconRegistry, $renderedIconRegistry, $iconRenderer);
        $this->assertSame($iconRegistry, $this->extractProperty($command, 'iconRegistry'));
        $this->assertSame($renderedIconRegistry, $this->extractProperty($command, 'renderedIconRegistry'));
        $this->assertSame($iconRenderer, $this->extractProperty($command, 'iconRenderer'));
    }

    /**
     * Provides the data for the execute test.
     * @return array
     */
    public function provideExecute(): array
    {
        return [
            [new Icon(), true, false],
            [null, false, true],
        ];
    }

    /**
     * Tests the execute method.
     * @param Icon|null $icon
     * @param bool $expectRender
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::execute
     * @dataProvider provideExecute
     */
    public function testExecute(?Icon $icon, bool $expectRender, bool $expectException): void
    {
        $hash = 'abc';
        $renderedIcon = 'def';

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with('hash', '')
              ->willReturn($hash);

        /* @var EntityRegistry|MockObject $iconRegistry */
        $iconRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['get'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $iconRegistry->expects($this->once())
                     ->method('get')
                     ->with($hash)
                     ->willReturn($icon);

        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeLine'])
                        ->getMockForAbstractClass();
        $console->expects($expectRender ? $this->once() : $this->never())
                ->method('writeLine')
                ->with('Rendering icon #abc...');

        /* @var IconRenderer|MockObject $iconRenderer */
        $iconRenderer = $this->getMockBuilder(IconRenderer::class)
                             ->setMethods(['render'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $iconRenderer->expects($expectRender ? $this->once() : $this->never())
                     ->method('render')
                     ->with($icon)
                     ->willReturn($renderedIcon);

        /* @var ContentRegistry|MockObject $renderedIconRegistry */
        $renderedIconRegistry = $this->getMockBuilder(ContentRegistry::class)
                                     ->setMethods(['set'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $renderedIconRegistry->expects($expectRender ? $this->once() : $this->never())
                             ->method('set')
                             ->with($hash, $renderedIcon);

        if ($expectException) {
            $this->expectException(CommandException::class);
        }

        $command = new RenderIconCommand($iconRegistry, $renderedIconRegistry, $iconRenderer);
        $this->invokeMethod($command, 'execute', $route, $console);
    }
}
