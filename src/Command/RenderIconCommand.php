<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\IconRenderException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command for rendering one icon.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconCommand implements CommandInterface
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The icon renderer.
     * @var IconRenderer
     */
    protected $iconRenderer;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * RenderIconCommand constructor.
     * @param Console $console
     * @param IconRenderer $iconRenderer
     * @param SerializerInterface $exportDataSerializer
     */
    public function __construct(Console $console, IconRenderer $iconRenderer, SerializerInterface $exportDataSerializer)
    {
        $this->console = $console;
        $this->iconRenderer = $iconRenderer;
        $this->serializer = $exportDataSerializer;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $consoleAdapter
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $consoleAdapter): int
    {
        try {
            $icon = $this->getIconFromRoute($route);
            $renderedIcon = $this->renderIcon($icon);
            $this->console->writeData($renderedIcon);
            return 0;
        } catch (Exception $e) {
            $this->console->writeException($e);
            return 1;
        }
    }

    /**
     * Extracts the icon from the route.
     * @param Route $route
     * @return Icon
     * @throws ExportException
     */
    protected function getIconFromRoute(Route $route): Icon
    {
        try {
            $serializedIcon = $route->getMatchedParam('icon');
            return $this->serializer->deserialize($serializedIcon, Icon::class, 'json');
        } catch (Exception $e) {
            throw new InternalException(sprintf('Invalid serialized icon: %s', $e->getMessage()), $e);
        }
    }

    /**
     * Renders the icon.
     * @param Icon $icon
     * @return string
     * @throws ExportException
     */
    protected function renderIcon(Icon $icon): string
    {
        try {
            return $this->iconRenderer->render($icon);
        } catch (Exception $e) {
            throw new IconRenderException($icon, $e->getMessage(), $e);
        }
    }
}
