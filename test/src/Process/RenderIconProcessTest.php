<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RenderIconProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\RenderIconProcess
 */
class RenderIconProcessTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getIcon
     */
    public function testConstruct(): void
    {
        $serializedIcon = 'abc';
        $expectedCommandLine = "'php' '{$_SERVER['SCRIPT_FILENAME']}' 'render-icon' 'abc'";

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var SerializerInterface&MockObject $serializer */
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
                   ->method('serialize')
                   ->with($this->identicalTo($icon), $this->identicalTo('json'))
                   ->willReturn($serializedIcon);


        $process = new RenderIconProcess($serializer, $icon);

        $this->assertSame($icon, $process->getIcon());
        $this->assertSame(['SUBCMD' => 1], $process->getEnv());
        $this->assertNull($process->getTimeout());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
    }
}
