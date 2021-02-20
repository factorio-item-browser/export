<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RenderIconProcessFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Process\RenderIconProcessFactory
 */
class RenderIconProcessFactoryTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;
    private string $factorioDirectory = 'data/factorio';
    private string $modsDirectory = 'data/mods';
    private string $renderIconBinary = 'bin/render-icon';

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RenderIconProcessFactory&MockObject
     */
    private function createInstance(array $mockedMethods = []): RenderIconProcessFactory
    {
        return $this->getMockBuilder(RenderIconProcessFactory::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->serializer,
                        $this->factorioDirectory,
                        $this->modsDirectory,
                        $this->renderIconBinary
                    ])
                    ->getMock();
    }

    public function testCreate(): void
    {
        $serializedIcon = 'abc';
        $icon = $this->createMock(Icon::class);
        $expectedCommandLine = sprintf("'%s' 'abc'", realpath('bin/render-icon'));
        $expectedEnv = [
            'FACTORIO_DATA_DIRECTORY' => realpath('data/factorio') . '/data',
            'FACTORIO_MODS_DIRECTORY' => realpath('data/mods'),
        ];

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->identicalTo($icon), $this->identicalTo('json'))
                         ->willReturn($serializedIcon);

        $instance = $this->createInstance();
        $result = $instance->create($icon);

        $this->assertSame($expectedCommandLine, $result->getCommandLine());
        $this->assertSame($expectedEnv, $result->getEnv());
        $this->assertSame($icon, $result->getIcon());
    }
}
