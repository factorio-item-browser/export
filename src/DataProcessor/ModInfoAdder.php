<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data processor adding additional data from the info.json files of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModInfoAdder implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly ModFileService $modFileService,
    ) {
    }

    /**
     * @throws ExportException
     */
    public function process(ExportData $exportData): void
    {
        foreach ($this->console->iterateWithProgressbar('Add mod info', $exportData->getMods()) as $mod) {
            /* @var Mod $mod */
            $modName = $mod->name;
            $info = $this->modFileService->getInfo($modName);

            $mod->version = (string) $info->version;
            $mod->author = $info->author;
            $mod->localisedName = ["mod-name.${modName}"];
            $mod->localisedDescription = ["mod-description.${modName}"];
            $mod->labels->set(Defaults::LOCALE, $info->title);
            $mod->descriptions->set(Defaults::LOCALE, $info->description);
        }
    }
}
