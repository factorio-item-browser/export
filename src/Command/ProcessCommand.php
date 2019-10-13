<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Mod\ModDownloader;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command for processing the next job in the import queue.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessCommand implements CommandInterface
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The icon renderer.
     * @var IconRenderer
     */
    protected $iconRenderer;

    /**
     * The instance.
     * @var Instance
     */
    protected $instance;

    /**
     * The mod downloader.
     * @var ModDownloader
     */
    protected $modDownloader;

    /**
     * The parser manager.
     * @var ParserManager
     */
    protected $parserManager;

    /**
     * ProcessCommand constructor.
     * @param ExportDataService $exportDataService
     * @param IconRenderer $iconRenderer
     * @param Instance $instance
     * @param ModDownloader $modDownloader
     * @param ParserManager $parserManager
     */
    public function __construct(
        ExportDataService $exportDataService,
        IconRenderer $iconRenderer,
        Instance $instance,
        ModDownloader $modDownloader,
        ParserManager $parserManager
    ) {
        $this->exportDataService = $exportDataService;
        $this->iconRenderer = $iconRenderer;
        $this->instance = $instance;
        $this->modDownloader = $modDownloader;
        $this->parserManager = $parserManager;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $consoleAdapter
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $consoleAdapter): int
    {
        $this->console = new Console($consoleAdapter);
        try {
            $this->execute();
            return 0;
        } catch (Exception $e) {
            $this->console->writeException($e);
            return 1;
        }
    }

    /**
     * @throws ExportException
     */
    protected function execute(): void
    {
        $combinationHash = 'bar';
        $export = $this->exportDataService->createExport($combinationHash);

        $this->console->writeHeadline('Processing combination %s', $combinationHash);

        $modNames = explode(',', 'base,IndustrialRevolution');
        $this->downloadMods($modNames);
        $dump = $this->runFactorio($export, $modNames);
        $this->parseDump($export, $dump);
        $this->renderIcons($export);

        $fileName = $export->persist();
        echo 'Exported combination to: ' . $fileName . PHP_EOL;
    }

    /**
     * Downloads all the mods if they are not already present in their latest version.
     * @param array|string[] $modNames
     * @throws ExportException
     */
    protected function downloadMods(array $modNames): void
    {
        $this->console->writeStep('Downloading %d mods', count($modNames));
        $this->modDownloader->download($modNames);
    }

    /**
     * Runs the Factorio game to dump all the data.
     * @param ExportData $export
     * @param array|string[] $modNames
     * @return Dump
     * @throws ExportException
     */
    protected function runFactorio(ExportData $export, array $modNames): Dump
    {
        $this->console->writeStep('Running Factorio');
        return $this->instance->run($export->getCombination()->getHash(), $modNames);
    }

    /**
     * Parses the dumped data into the export.
     * @param ExportData $export
     * @param Dump $dump
     * @throws ExportException
     */
    protected function parseDump(ExportData $export, Dump $dump): void
    {
        $this->console->writeStep('Parsing dumped data');
        $this->parserManager->parse($dump, $export->getCombination());
    }

    /**
     * Renders all the icons of the export.
     * @param ExportData $export
     * @throws ExportException
     */
    protected function renderIcons(ExportData $export): void
    {
        $this->console->writeStep('Rendering %d icons', count($export->getCombination()->getIcons()));

        foreach ($export->getCombination()->getIcons() as $icon) {
            $this->console->writeAction('Rendering icon %s', $icon->getHash());
            $export->addRenderedIcon($icon, $this->iconRenderer->render($icon));
        }
    }
}
