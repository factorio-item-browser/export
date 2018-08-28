<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Render;

use FactorioItemBrowser\Export\Command\CommandInterface;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Registry\ContentRegistry;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for rendering an icon.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconCommand implements CommandInterface
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
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $hash = $route->getMatchedParam('hash', '');
        $icon = $this->iconRegistry->get($hash);
        if ($icon instanceof Icon) {
            $console->writeLine('Rendering icon #' . $hash . '...');
            try {
                $renderedIcon = $this->iconRenderer->render($icon, self::ICON_SIZE);
                $this->renderedIconRegistry->set($hash, $renderedIcon);
                $result = 0;
            } catch (ExportException $e) {
                $console->writeLine('Failed to render icon #' . $hash . ': ' . $e->getMessage(), ColorInterface::RED);
                $result = 500;
            }
        } else {
            $console->writeLine('Cannot find icon #' . $hash . '.', ColorInterface::RED);
            $result = 404;
        }
        return $result;
    }
}
