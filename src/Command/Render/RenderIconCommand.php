<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Registry\ContentRegistry;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use ZF\Console\Route;

/**
 * The command for rendering an icon.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconCommand extends AbstractCommand
{
    /**
     * The size to render the icons in.
     */
    protected const ICON_SIZE = 32;

    /**
     * The registry of the icons.
     * @var EntityRegistry
     */
    protected $iconRegistry;

    /**
     * The registry for the rendered icons.
     * @var ContentRegistry
     */
    protected $renderedIconRegistry;

    /**
     * The icon renderer.
     * @var IconRenderer
     */
    protected $iconRenderer;

    /**
     * Initializes the command.
     * @param EntityRegistry $iconRegistry
     * @param ContentRegistry $renderedIconRegistry
     * @param IconRenderer $iconRenderer
     */
    public function __construct(
        EntityRegistry $iconRegistry,
        ContentRegistry $renderedIconRegistry,
        IconRenderer $iconRenderer
    ) {
        $this->iconRegistry = $iconRegistry;
        $this->renderedIconRegistry = $renderedIconRegistry;
        $this->iconRenderer = $iconRenderer;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $hash = $route->getMatchedParam(ParameterName::ICON_HASH, '');
        $icon = $this->iconRegistry->get($hash);

        if (!$icon instanceof Icon) {
            throw new CommandException('Icon with hash #' . $hash . ' not found.', 404);
        }

        $this->console->writeAction('Rendering icon #' . $hash);
        $renderedIcon = $this->iconRenderer->render($icon, self::ICON_SIZE);
        $this->renderedIconRegistry->set($hash, $renderedIcon);
    }
}
