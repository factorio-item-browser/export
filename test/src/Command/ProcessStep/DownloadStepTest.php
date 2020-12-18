<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\DownloadStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Service\ModDownloadService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DownloadStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\DownloadStep
 */
class DownloadStepTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod downloader.
     * @var ModDownloadService&MockObject
     */
    protected $modDownloader;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modDownloader = $this->createMock(ModDownloadService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $step = new DownloadStep($this->modDownloader);

        $this->assertSame($this->modDownloader, $this->extractProperty($step, 'modDownloader'));
    }

    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Downloading mods from the Mod Portal';
        $step = new DownloadStep($this->modDownloader);

        $result = $step->getLabel();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getExportJobStatus method.
     * @covers ::getExportJobStatus
     */
    public function testGetExportJobStatus(): void
    {
        $expectedResult = JobStatus::DOWNLOADING;
        $step = new DownloadStep($this->modDownloader);

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
        $modNames = ['abc', 'def'];

        $exportJob = new Job();
        $exportJob->setModNames($modNames);

        $data = new ProcessStepData();
        $data->setExportJob($exportJob);

        $this->modDownloader->expects($this->once())
                            ->method('download')
                            ->with($this->identicalTo($modNames));

        $step = new DownloadStep($this->modDownloader);
        $step->run($data);
    }
}
