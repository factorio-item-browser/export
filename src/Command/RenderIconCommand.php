<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\IconRenderException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for rendering one icon.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconCommand extends Command
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
        parent::__construct();

        $this->console = $console;
        $this->iconRenderer = $iconRenderer;
        $this->serializer = $exportDataSerializer;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::RENDER_ICON);
        $this->setDescription('Renders the specified icon.');

        $this->addArgument('icon', InputArgument::REQUIRED, 'The serialized icon to render.');
    }

    /**
     * Executes the command.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $icon = $this->getIconFromInput($input);
            $renderedIcon = $this->renderIcon($icon);
            $this->console->writeData($renderedIcon);
            return 0;
        } catch (Exception $e) {
            $this->console->writeException($e);
            return 1;
        }
    }

    /**
     * Extracts the icon from the input.
     * @param InputInterface $input
     * @return Icon
     * @throws ExportException
     */
    protected function getIconFromInput(InputInterface $input): Icon
    {
        try {
            $serializedIcon = strval($input->getArgument('icon'));
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
