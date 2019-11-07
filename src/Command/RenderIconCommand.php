<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Exception\ExportException;
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
     * @param IconRenderer $iconRenderer
     * @param SerializerInterface $exportDataSerializer
     */
    public function __construct(IconRenderer $iconRenderer, SerializerInterface $exportDataSerializer)
    {
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
        $icon = $this->getIconFromRoute($route);
        try {
            $renderedIcon = $this->iconRenderer->render($icon);
            $consoleAdapter->write($renderedIcon);
            return 0;
        } catch (ExportException $e) {
            $consoleAdapter->write($e->getMessage());
            return 1;
        }
    }

    /**
     * Extracts the icon from the route.
     * @param Route $route
     * @return Icon
     */
    protected function getIconFromRoute(Route $route): Icon
    {
        $serializedIcon = $route->getMatchedParam('icon');
        $icon = $this->serializer->deserialize($serializedIcon, Icon::class, 'json');
        // @todo Error handling
        return $icon;
    }
}
