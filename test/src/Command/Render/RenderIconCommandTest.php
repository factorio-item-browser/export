<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Render\RenderIconCommand;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Registry\ContentRegistry;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
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
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            [
                'abc',
                new Icon(),
                true,
                null,
                'def',
                ['Rendering icon #abc...'],
                [null],
                0,
            ],
            [
                'abc',
                new Icon(),
                true,
                new ExportException('def'),
                null,
                ['Rendering icon #abc...', 'Failed to render icon #abc: def'],
                [null, ColorInterface::RED],
                500,
            ],
            [
                'abc',
                null,
                false,
                null,
                null,
                ['Cannot find icon #abc.'],
                [ColorInterface::RED],
                404,
            ]
        ];
    }


    /**
     * Tests the invoking.
     * @param string $hash
     * @param Icon|null $icon
     * @param bool $expectRender
     * @param ExportException|null $thrownException
     * @param null|string $renderedIcon
     * @param array $expectedConsoleOutputs
     * @param array $expectedConsoleOutputColors
     * @param int $expectedResult
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(
        string $hash,
        ?Icon $icon,
        bool $expectRender,
        ?ExportException $thrownException,
        ?string $renderedIcon,
        array $expectedConsoleOutputs,
        array $expectedConsoleOutputColors,
        int $expectedResult
    ): void {
        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with('hash', '')
              ->willReturn($hash);

        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeLine'])
                        ->getMockForAbstractClass();
        foreach ($expectedConsoleOutputs as $index => $expectedConsoleOutput) {
            $console->expects($this->at($index))
                    ->method('writeLine')
                    ->with($expectedConsoleOutput, $expectedConsoleOutputColors[$index] ?? null);
        }

        /* @var EntityRegistry|MockObject $iconRegistry */
        $iconRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['get'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $iconRegistry->expects($this->once())
                     ->method('get')
                     ->with($hash)
                     ->willReturn($icon);

        /* @var IconRenderer|MockObject $iconRenderer */
        $iconRenderer = $this->getMockBuilder(IconRenderer::class)
                             ->setMethods(['render'])
                             ->disableOriginalConstructor()
                             ->getMock();

        if ($expectRender) {
            if ($thrownException === null) {
                $iconRenderer->expects($this->once())
                             ->method('render')
                             ->with($icon, 32)
                             ->willReturn($renderedIcon);
            } else {
                $iconRenderer->expects($this->once())
                             ->method('render')
                             ->with($icon, 32)
                             ->willThrowException($thrownException);
            }
        } else {
            $iconRenderer->expects($this->never())
                         ->method('render');
        }

        /* @var ContentRegistry|MockObject $renderedIconRegistry */
        $renderedIconRegistry = $this->getMockBuilder(ContentRegistry::class)
                                     ->setMethods(['set'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $renderedIconRegistry->expects($renderedIcon === null ? $this->never() : $this->once())
                             ->method('set')
                             ->with($hash, $renderedIcon);

        $command = new RenderIconCommand($iconRegistry, $renderedIconRegistry, $iconRenderer);
        $result = $command($route, $console);
        $this->assertSame($expectedResult, $result);
    }
}
