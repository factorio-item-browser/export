<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for rendering all icons of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderModIconsCommand extends AbstractCommand
{
    use SubCommandTrait;

    /**
     * The registry of the combinations.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The process manager.
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * RenderModIconsCommand constructor.
     * @param EntityRegistry $combinationRegistry
     * @param ModRegistry $modRegistry
     * @param ProcessManager $processManager
     */
    public function __construct(
        EntityRegistry $combinationRegistry,
        ModRegistry $modRegistry,
        ProcessManager $processManager
    ) {
        $this->combinationRegistry = $combinationRegistry;
        $this->modRegistry = $modRegistry;
        $this->processManager = $processManager;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route): void
    {
        $modName = $route->getMatchedParam('modName', '');
        $iconHashes = $this->fetchIconHashesOfMod($modName);

        $this->console->writeLine('Rendering ' . count($iconHashes) . ' icons...');
        $this->renderIconsWithHashes($iconHashes);
    }

    /**
     * Fetches the icon hashes of the mod with the specified name.
     * @param string $modName
     * @return array|string[]
     * @throws CommandException
     */
    protected function fetchIconHashesOfMod(string $modName): array
    {
        $mod = $this->modRegistry->get($modName);
        if (!$mod instanceof Mod) {
            throw new CommandException('Mod not found: ' . $modName, 404);
        }

        $iconHashes = [];
        foreach ($mod->getCombinationHashes() as $combinationHash) {
            $iconHashes = array_merge($iconHashes, $this->fetchIconHashesOfCombination($combinationHash));
        }
        return array_unique($iconHashes);
    }

    /**
     * Fetches the icon hashes of the combination with the specified hash.
     * @param string $combinationHash
     * @return array|string[]
     * @throws CommandException
     */
    protected function fetchIconHashesOfCombination(string $combinationHash): array
    {
        $combination = $this->combinationRegistry->get($combinationHash);
        if (!$combination instanceof Combination) {
            throw new CommandException('Combination not found: #' . $combinationHash);
        }

        return $combination->getIconHashes();
    }

    /**
     * Renders the icons with the specified hashes.
     * @param array|string[] $iconHashes
     */
    protected function renderIconsWithHashes(array $iconHashes): void
    {
        foreach ($iconHashes as $iconHash) {
            $this->processManager->addProcess($this->createCommandProcess(
                CommandName::RENDER_ICON,
                ['iconHash' => $iconHash],
                $this->console
            ));
        }
        $this->processManager->waitForAllProcesses();
    }
}
