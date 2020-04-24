<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RenderIconProcessFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\RenderIconProcessFactory
 */
class RenderIconProcessFactoryTest extends TestCase
{
    use ReflectionTrait;

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

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factorioDirectory = 'abc';
        $modsDirectory = 'def';
        $renderIconBinary = 'ghi';

        $factory = new RenderIconProcessFactory(
            $this->serializer,
            $factorioDirectory,
            $modsDirectory,
            $renderIconBinary
        );

        $this->assertSame($this->serializer, $this->extractProperty($factory, 'serializer'));
        $this->assertSame($factorioDirectory, $this->extractProperty($factory, 'factorioDirectory'));
        $this->assertSame($modsDirectory, $this->extractProperty($factory, 'modsDirectory'));
        $this->assertSame($renderIconBinary, $this->extractProperty($factory, 'renderIconBinary'));
    }

    /**
     * Tests the create method.
     * @covers ::create
     */
    public function testCreate(): void
    {
        $serializedIcon = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        $factorioDirectory = '.';
        $modsDirectory = 'test';
        $renderIconBinary = 'bin/render-icon';

        $expectedCommandLine = sprintf("'%s' 'abc'", realpath($renderIconBinary));
        $expectedEnv = [
            'FACTORIO_DATA_DIRECTORY' => realpath('data'),
            'FACTORIO_MODS_DIRECTORY' => realpath('test'),
        ];

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->identicalTo($icon), $this->identicalTo('json'))
                         ->willReturn($serializedIcon);

        $factory = new RenderIconProcessFactory(
            $this->serializer,
            $factorioDirectory,
            $modsDirectory,
            $renderIconBinary
        );

        $result = $factory->create($icon);
        $this->assertSame($expectedCommandLine, $result->getCommandLine());
        $this->assertSame($expectedEnv, $result->getEnv());
        $this->assertSame($icon, $result->getIcon());
    }
}
