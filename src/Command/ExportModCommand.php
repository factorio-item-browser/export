<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\FactorioManager;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Mod\CombinationCreator;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for export a certain mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The combination creator.
     * @var CombinationCreator
     */
    protected $combinationCreator;

    /**
     * The factorio manager.
     * @var FactorioManager
     */
    protected $factorioManager;

    /**
     * The icon renderer.
     * @var IconRenderer
     */
    protected $iconRenderer;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the command.
     * @param ExportDataService $exportDataService
     * @param CombinationCreator $combinationCreator
     * @param FactorioManager $factorioManager
     * @param IconRenderer $iconRenderer
     * @param Translator $translator
     */
    public function __construct(
        ExportDataService $exportDataService,
        CombinationCreator $combinationCreator,
        FactorioManager $factorioManager,
        IconRenderer $iconRenderer,
        Translator $translator
    )
    {
        $this->exportDataService = $exportDataService;
        $this->combinationCreator = $combinationCreator;
        $this->factorioManager = $factorioManager;
        $this->iconRenderer = $iconRenderer;
        $this->translator = $translator;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $modName = $route->getMatchedParam('modName');

        $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);
        $console->writeLine(' Exporting mod: ' . $modName, ColorInterface::YELLOW);
        $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);

        $mod = $this->exportDataService->getMod($modName);
        if (!$mod instanceof Mod) {
            throw new ExportException('Mod not known: ' . $modName);
        }

        $combinations = $this->combinationCreator->createCombinations($mod);
        $console->writeLine(' > Processing ' . count($combinations) . ' combination(s)...');
        foreach ($combinations as $combination) {
            $this->factorioManager->addCombination($combination);
        }
        $this->factorioManager->waitForAllCombinations();

        $console->writeLine(' > Rendering icons and persisting data...');
        foreach ($combinations as $combination) {
            if (count($combination->getLoadedOptionalModNames()) === 0) {
                $this->renderMeta($combination, $mod);
            }
            if (!$this->isCombinationEmpty($combination)) {
                $this->exportDataService->saveCombinationData($combination);
                $mod->addCombination($combination);

                $this->renderIcons($combination);
            }
        }
        $this->exportDataService->saveMods();
    }

    /**
     * Returns whether the specified combination is empty.
     * @param Combination $combination
     * @return bool
     */
    protected function isCombinationEmpty(Combination $combination): bool
    {
        return count($combination->getData()->getItems()) === 0
            && count($combination->getData()->getRecipes()) === 0
            && count($combination->getData()->getIcons()) === 0;
    }

    /**
     * Renders all the icons of the specified combination.
     * @param Combination $combination
     * @return $this
     */
    protected function renderIcons(Combination $combination)
    {
        foreach ($combination->getData()->getIcons() as $icon) {
            $content = $this->iconRenderer->render($icon, 32, 32);
            $this->exportDataService->saveIcon($icon->getIconHash(), $content);
        }
        return $this;
    }

    /**
     * Renders meta data of the mod.
     * @param Combination $combination
     * @param Mod $mod
     * @return $this
     */
    protected function renderMeta(Combination $combination, Mod $mod)
    {
        $this->translator->setEnabledModNames($combination->getLoadedModNames());
        $this->translator->addTranslations(
            $mod->getTitles(),
            'mod-name',
            ['mod-name.' . $mod->getName()],
            ''
        );
        $this->translator->addTranslations(
            $mod->getDescriptions(),
            'mod-description',
            ['mod-description.' . $mod->getName()],
            ''
        );
        return $this;
    }
}