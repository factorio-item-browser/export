<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
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
     * The dump extractor.
     * @var DumpExtractor
     */
    protected $dumpExtractor;

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
     * @param DumpExtractor $dumpExtractor
     * @param ExportDataService $exportDataService
     * @param IconRenderer $iconRenderer
     * @param ModDownloader $modDownloader
     * @param ParserManager $parserManager
     */
    public function __construct(
        DumpExtractor $dumpExtractor,
        ExportDataService $exportDataService,
        IconRenderer $iconRenderer,
        ModDownloader $modDownloader,
        ParserManager $parserManager
    ) {
        $this->dumpExtractor = $dumpExtractor;
        $this->exportDataService = $exportDataService;
        $this->iconRenderer = $iconRenderer;
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

    protected function execute(): void
    {
            $combinationHash = 'foo';
            $export = $this->exportDataService->createExport($combinationHash);

            $this->console->writeHeadline('Processing combination %s', $combinationHash);

            $modNames = explode(',', 'base,bobenemies,boblibrary,clock,FNEI,YARM,boblocale,bobores,bobtech,bobplates,bobassembly,bobclasses,bobelectronics,bobgreenhouse,boblogistics,bobmining,bobpower,bobmodules');
            $this->downloadMods($modNames);
            $dump = $this->dumpExtractor->extract(file_get_contents(__DIR__ . '/../../data/log.txt'));
            $this->parserManager->parse($dump, $export->getCombination());

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
