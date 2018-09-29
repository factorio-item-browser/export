<?php

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
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
class ExportModMetaCommand extends AbstractCommand
{
    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

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
        $this->modRegistry = $modRegistry;
        $this->translator = $translator;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route): void
    {
        $mod = $this->fetchMod($route->getMatchedParam('modName'));
        $this->translate($mod);

        $this->modRegistry->set($mod);
        $this->modRegistry->saveMods();
    }

    /**
     * Fetches the mod to the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function fetchMod(string $modName): Mod
    {
        $mod = $this->modRegistry->get($modName);
        if (!$mod instanceof Mod) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }
        return $mod;
    }

    /**
     * Translates the specified mod.
     * @param Mod $mod
     * @throws ExportException
     */
    protected function translate(Mod $mod): void
    {
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
