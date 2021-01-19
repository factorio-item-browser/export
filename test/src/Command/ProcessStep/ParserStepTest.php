<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\ParserStep;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserInterface;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ParserStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\ProcessStep\ParserStep
 */
class ParserStepTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @param array<ParserInterface> $parsers
     * @return ParserStep
     */
    private function createInstance(array $parsers): ParserStep
    {
        return new ParserStep($parsers);
    }

    public function testMeta(): void
    {
        $instance = $this->createInstance([]);

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::PROCESSING, $instance->getExportJobStatus());
    }

    /**
     * @throws ExportException
     */
    public function testRun(): void
    {
        $dump = $this->createMock(Dump::class);
        $exportData = $this->createMock(ExportData::class);

        $processStepData = new ProcessStepData();
        $processStepData->dump = $dump;
        $processStepData->exportData = $exportData;

        $parser1 = $this->createMock(ParserInterface::class);
        $parser1->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser1->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($exportData));
        $parser1->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($exportData));

        $parser2 = $this->createMock(ParserInterface::class);
        $parser2->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser2->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($exportData));
        $parser2->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($exportData));


        $instance = $this->createInstance([$parser1, $parser2]);
        $instance->run($processStepData);
    }
}
