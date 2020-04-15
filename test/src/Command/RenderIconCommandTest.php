<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Command\RenderIconCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\IconRenderException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var RenderIconCommand&MockObject $command */
        $command = $this->getMockBuilder(RenderIconCommand::class)
                        ->onlyMethods(['setName', 'setDescription', 'addArgument'])
                        ->setConstructorArgs([$this->console, $this->iconRenderer, $this->serializer])
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::RENDER_ICON));
        $command->expects($this->once())
                ->method('setDescription')
                ->with($this->isType('string'));
        $command->expects($this->once())
                ->method('addArgument')
                ->with(
                    $this->identicalTo('icon'),
                    $this->identicalTo(InputArgument::REQUIRED),
                    $this->isType('string')
                );

        $this->invokeMethod($command, 'configure');
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $renderedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        $this->console->expects($this->once())
                      ->method('writeData')
                      ->with($this->identicalTo($renderedIcon))
                      ->willReturnSelf();
        $this->console->expects($this->never())
                      ->method('writeException');

        /* @var RenderIconCommand&MockObject $command */
        $command = $this->getMockBuilder(RenderIconCommand::class)
                        ->onlyMethods(['getIconFromInput', 'renderIcon'])
                        ->setConstructorArgs([$this->console, $this->iconRenderer, $this->serializer])
                        ->getMock();
        $command->expects($this->once())
                ->method('getIconFromInput')
                ->with($this->identicalTo($input))
                ->willReturn($icon);
        $command->expects($this->once())
                ->method('renderIcon')
                ->with($this->identicalTo($icon))
                ->willReturn($renderedIcon);

        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecuteWithException(): void
    {
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);
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
                        ->onlyMethods(['getIconFromInput', 'renderIcon'])
                        ->setConstructorArgs([$this->console, $this->iconRenderer, $this->serializer])
                        ->getMock();
        $command->expects($this->once())
                ->method('getIconFromInput')
                ->with($this->identicalTo($input))
                ->willReturn($icon);
        $command->expects($this->once())
                ->method('renderIcon')
                ->with($this->identicalTo($icon))
                ->willThrowException($exception);

        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * Tests the getIconFromInput method.
     * @throws ReflectionException
     * @covers ::getIconFromInput
     */
    public function testGetIconFromInput(): void
    {
        $serializedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())
              ->method('getArgument')
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
        $result = $this->invokeMethod($command, 'getIconFromInput', $input);

        $this->assertSame($icon, $result);
    }

    /**
     * Tests the getIconFromInput method.
     * @throws ReflectionException
     * @covers ::getIconFromInput
     */
    public function testGetIconFromInputWithException(): void
    {
        $serializedIcon = 'abc';

        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())
              ->method('getArgument')
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
        $this->invokeMethod($command, 'getIconFromInput', $input);
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
