<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Console;

use Exception;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use Zend\ProgressBar\Adapter\AbstractAdapter;
use Zend\ProgressBar\Adapter\Console as ProgressBarConsole;
use Zend\ProgressBar\ProgressBar;

/**
 * The wrapper class for the actual console.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Console
{
    /**
     * The actual console instance.
     * @var AdapterInterface
     */
    protected $consoleAdapter;

    /**
     * Initializes the console wrapper.
     * @param AdapterInterface $consoleAdapter
     */
    public function __construct(AdapterInterface $consoleAdapter)
    {
        $this->consoleAdapter = $consoleAdapter;
    }

    /**
     * Writes a message to the console.
     * @param string $message
     * @param int|null $color
     * @return $this
     */
    public function write(string $message, ?int $color = null)
    {
        $this->consoleAdapter->write($message, $color);
        return $this;
    }

    /**
     * Writes a line to the console.
     * @param string $message
     * @param int|null $color
     * @return $this
     */
    public function writeLine(string $message = '', ?int $color = null)
    {
        $this->consoleAdapter->writeLine($message, $color);
        return $this;
    }

    /**
     * Writes a command being executed to the console.
     * @param string $command
     * @return $this
     */
    public function writeCommand(string $command)
    {
        $this->writeLine('$ ' . $command, ColorInterface::GRAY);
        return $this;
    }

    /**
     * Writes a headline with the specified message.
     * @param string $message
     * @param array|string[] $parameters
     * @return $this
     */
    public function writeHeadline(string $message, ...$parameters): self
    {
        $this->writeLine()
             ->writeHorizontalLine('-', ColorInterface::LIGHT_YELLOW)
             ->writeLine(' ' . sprintf($message, ...$parameters), ColorInterface::LIGHT_YELLOW)
             ->writeHorizontalLine('-', ColorInterface::LIGHT_YELLOW);
        return $this;
    }

    /**
     * Writes a step to the console.
     * @param string $step
     * @param mixed ...$parameters
     */
    public function writeStep(string $step, ...$parameters)
    {
        $this->writeLine();
        $this->writeLine(' ' . sprintf($step, ...$parameters), ColorInterface::LIGHT_BLUE);
        $this->writeHorizontalLine('-', ColorInterface::LIGHT_BLUE);
    }

    /**
     * Writes an action to the console.
     * @param string $action
     * @param array $parameters
     * @return $this
     */
    public function writeAction(string $action, ...$parameters)
    {
        $this->writeLine(sprintf('> ' . $action . '...', ...$parameters));
        return $this;
    }

    /**
     * Writes a simple message, like a comment, to the console.
     * @param string $message
     * @param mixed ...$parameters
     */
    public function writeMessage(string $message, ...$parameters)
    {
        $this->writeLine('# ' . sprintf($message, ...$parameters));
    }

    /**
     * Writes a horizontal line to the console.
     * @param string $character
     * @param int|null $color
     * @return $this
     */
    protected function writeHorizontalLine(string $character, ?int $color = null)
    {
        $this->writeLine(str_pad('', $this->consoleAdapter->getWidth(), $character), $color);
        return $this;
    }

    public function writeException(Exception $e): void
    {
        $this->writeLine()
             ->writeHorizontalLine('-', ColorInterface::LIGHT_RED)
             ->writeLine(
                 sprintf(' %s: %s', substr(strrchr(get_class($e), '\\'), 1), $e->getMessage()),
                 ColorInterface::LIGHT_RED
             )
             ->writeHorizontalLine('-', ColorInterface::LIGHT_RED)
             ->writeLine($e->getTraceAsString(), ColorInterface::RED);
    }
}
