<?php

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command exporting the meta data of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModMetaCommand extends AbstractModCommand
{
    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the command.
     * @param ModRegistry $modRegistry
     * @param Translator $translator
     */
    public function __construct(ModRegistry $modRegistry, Translator $translator)
    {
        parent::__construct($modRegistry);

        $this->translator = $translator;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     * @throws ExportException
     */
    protected function processMod(Route $route, Mod $mod): void
    {
        $this->translate($mod);

        $this->modRegistry->set($mod);
        $this->modRegistry->saveMods();
    }

    /**
     * Translates the specified mod.
     * @param Mod $mod
     * @throws ExportException
     */
    protected function translate(Mod $mod): void
    {
        $this->console->writeAction('Exporting meta data of mod ' . $mod->getName());

        $this->translator->loadFromModNames([$mod->getName()]);
        $this->translator->addTranslationsToEntity(
            $mod->getTitles(),
            'mod-name',
            ['mod-name.' . $mod->getName()]
        );
        $this->translator->addTranslationsToEntity(
            $mod->getDescriptions(),
            'mod-descriptions',
            ['mod-descriptions.' . $mod->getName()]
        );
    }
}
