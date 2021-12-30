<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data processor loading the translations of all the mods into the translator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationLoader implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly ModFileService $modFileService,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @throws NoSupportedLoaderException
     */
    public function process(ExportData $exportData): void
    {
        $this->translator->loadMod($this->modFileService->getLocalDirectory('core'));
        foreach ($this->console->iterateWithProgressbar('Load translations', $exportData->getMods()) as $mod) {
            /* @var Mod $mod */
            $this->translator->loadMod($this->modFileService->getLocalDirectory($mod->name));
        }
    }
}
