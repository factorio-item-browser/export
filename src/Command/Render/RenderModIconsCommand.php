<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
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
class RenderModIconsCommand extends AbstractModCommand
{
    use SubCommandTrait;

    /**
     * The registry of the combinations.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

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
        parent::__construct($modRegistry);

        $this->combinationRegistry = $combinationRegistry;
        $this->processManager = $processManager;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     * @throws ExportException
     */
    protected function processMod(Route $route, Mod $mod): void
    {
        $iconHashes = $this->fetchIconHashesOfMod($mod);
        $this->console->writeAction('Rendering ' . count($iconHashes) . ' icons');
        $this->renderIconsWithHashes($iconHashes);
    }

    /**
     * Fetches the icon hashes of the mod with the specified name.
     * @param Mod $mod
     * @return array|string[]
     * @throws CommandException
     */
    protected function fetchIconHashesOfMod(Mod $mod): array
    {
        $iconHashes = [];
        foreach ($mod->getCombinationHashes() as $combinationHash) {
            $combination = $this->fetchCombination($combinationHash);
            $iconHashes = array_merge($iconHashes, $combination->getIconHashes());
        }
        return array_values(array_unique($iconHashes));
    }

    /**
     * Fetches the combination to the specified hash.
     * @param string $combinationHash
     * @return Combination
     * @throws CommandException
     */
    protected function fetchCombination(string $combinationHash): Combination
    {
        $combination = $this->combinationRegistry->get($combinationHash);
        if (!$combination instanceof Combination) {
            throw new CommandException('Combination hash #' . $combinationHash . ' not known.', 404);
        }
        return $combination;
    }

    /**
     * Renders the icons with the specified hashes.
     * @param array|string[] $iconHashes
     */
    protected function renderIconsWithHashes(array $iconHashes): void
    {
        foreach ($iconHashes as $iconHash) {
            $process = $this->createCommandProcess(
                CommandName::RENDER_ICON,
                [ParameterName::ICON_HASH => $iconHash],
                $this->console
            );
            $this->processManager->addProcess($process);
        }
        $this->processManager->waitForAllProcesses();
    }
}
