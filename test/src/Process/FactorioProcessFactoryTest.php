<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\OutputProcessor\OutputProcessorInterface;
use FactorioItemBrowser\Export\Process\FactorioProcess;
use FactorioItemBrowser\Export\Process\FactorioProcessFactory;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the FactorioProcessFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\FactorioProcessFactory
 */
class FactorioProcessFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $outputProcessors = [
            $this->createMock(OutputProcessorInterface::class),
            $this->createMock(OutputProcessorInterface::class),
        ];

        $factory = new FactorioProcessFactory($outputProcessors);

        $this->assertSame($outputProcessors, $this->extractProperty($factory, 'outputProcessors'));
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $outputProcessors = [
            $this->createMock(OutputProcessorInterface::class),
            $this->createMock(OutputProcessorInterface::class),
        ];
        $instanceDirectory = 'abc';
        $exportData = $this->createMock(ExportData::class);

        $expectedResult = new FactorioProcess($outputProcessors, $exportData, $instanceDirectory);

        $factory = new FactorioProcessFactory($outputProcessors);
        $result = $factory->create($exportData, $instanceDirectory);

        $this->assertEquals($expectedResult, $result);
    }
}
