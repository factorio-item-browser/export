<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Combination;
use FactorioItemBrowser\Export\Command\ProcessStep\DownloadStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Service\ModDownloadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DownloadStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\ProcessStep\DownloadStep
 */
class DownloadStepTest extends TestCase
{
    use ReflectionTrait;

    /** @var ModDownloadService&MockObject */
    private ModDownloadService $modDownloadService;

    protected function setUp(): void
    {
        $this->modDownloadService = $this->createMock(ModDownloadService::class);
    }

    private function createInstance(): DownloadStep
    {
        return new DownloadStep($this->modDownloadService);
    }

    public function testMeta(): void
    {
        $instance = $this->createInstance();

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::DOWNLOADING, $instance->getExportJobStatus());
    }

    /**
     * @throws ExportException
     */
    public function testRun(): void
    {
        $modNames = ['abc', 'def'];
        $combination = new Combination();
        $combination->modNames = $modNames;

        $processStepData = new ProcessStepData();
        $processStepData->combination = $combination;

        $this->modDownloadService->expects($this->once())
                                 ->method('download')
                                 ->with($this->identicalTo($modNames));

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }
}
