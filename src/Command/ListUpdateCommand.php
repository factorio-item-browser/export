<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command to update the list of mods from the files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListUpdateCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the command.
     * @param ExportDataService $exportDataService
     * @param ModFileManager $modFileManager
     * @param Translator $translator
     */
    public function __construct(
        ExportDataService $exportDataService,
        ModFileManager $modFileManager,
        Translator $translator
    )
    {
        $this->exportDataService = $exportDataService;
        $this->modFileManager = $modFileManager;
        $this->translator = $translator;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $oldVersions = [];
        foreach ($this->exportDataService->getMods() as $mod) {
            $oldVersions[$mod->getName()] = $mod->getVersion();
        }

        $this->modFileManager->updateModsFromFiles();

        foreach ($this->exportDataService->getMods() as $mod) {
            $newVersion = $mod->getVersion();
            $oldVersion = $oldVersions[$mod->getName()] ?? '';

            if ($newVersion !== $oldVersion) {
                $console->write(str_pad($mod->getName() . ': ', 64, ' ', STR_PAD_LEFT));
                $console->write(str_pad($oldVersion, 10, ' ', STR_PAD_LEFT));
                $console->write(' -> ');
                $console->write(str_pad($newVersion, 10, ' ', STR_PAD_RIGHT), ColorInterface::YELLOW);
                $console->writeLine();
            }
        }

        $this->translator->clearLocaleDataCache();
    }
}