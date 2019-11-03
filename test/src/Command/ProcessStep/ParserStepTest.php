<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\ParserStep;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserInterface;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ParserStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\ParserStep
 */
class ParserStepTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parsers = [
            $this->createMock(ParserInterface::class),
            $this->createMock(ParserInterface::class),
        ];

        $step = new ParserStep($this->console, $parsers);

        $this->assertSame($this->console, $this->extractProperty($step, 'console'));
        $this->assertSame($parsers, $this->extractProperty($step, 'parsers'));
    }


    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Parsing the dumped data';
        $step = new ParserStep($this->console, []);

        $result = $step->getLabel();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getExportJobStatus method.
     * @covers ::getExportJobStatus
     */
    public function testGetExportJobStatus(): void
    {
        $expectedResult = JobStatus::PROCESSING;
        $step = new ParserStep($this->console, []);

        $result = $step->getExportJobStatus();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the run method.
     * @throws ExportException
     * @covers ::run
     */
    public function testRun(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getCombination')
                   ->willReturn($combination);

        $data = new ProcessStepData();
        $data->setDump($dump)
             ->setExportData($exportData);

        /* @var ParserInterface&MockObject $parser1 */
        $parser1 = $this->createMock(ParserInterface::class);
        $parser1->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser1->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($combination));
        $parser1->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($combination));

        /* @var ParserInterface&MockObject $parser2 */
        $parser2 = $this->createMock(ParserInterface::class);
        $parser2->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser2->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($combination));
        $parser2->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($combination));

        $parsers = [$parser1, $parser2];

        $this->console->expects($this->exactly(3))
                      ->method('writeAction')
                      ->withConsecutive(
                          [$this->identicalTo('Preparing')],
                          [$this->identicalTo('Parsing')],
                          [$this->identicalTo('Validating')]
                      );

        $step = new ParserStep($this->console, $parsers);
        $step->run($data);
    }
}
