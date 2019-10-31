<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Console;

use Exception;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

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
     * Writes a headline with the specified message.
     * @param string $message
     * @param array|string[] $parameters
     * @return $this
     */
    public function writeHeadline(string $message, ...$parameters): self
    {
        $this->consoleAdapter->writeLine();
        $this->consoleAdapter->writeLine($this->createHorizontalLine('-'), ColorInterface::LIGHT_YELLOW);
        $this->consoleAdapter->writeLine(' ' . sprintf($message, ...$parameters), ColorInterface::LIGHT_YELLOW);
        $this->consoleAdapter->writeLine($this->createHorizontalLine('-'), ColorInterface::LIGHT_YELLOW);
        return $this;
    }

    /**
     * Writes a step to the console.
     * @param string $step
     * @param mixed ...$parameters
     * @return $this
     */
    public function writeStep(string $step, ...$parameters): self
    {
        $this->consoleAdapter->writeLine();
        $this->consoleAdapter->writeLine(sprintf($step, ...$parameters), ColorInterface::LIGHT_BLUE);
        $this->consoleAdapter->writeLine($this->createHorizontalLine('-'), ColorInterface::LIGHT_BLUE);
        return $this;
    }

    /**
     * Writes an action to the console.
     * @param string $action
     * @param array $parameters
     * @return $this
     */
    public function writeAction(string $action, ...$parameters): self
    {
        $this->consoleAdapter->writeLine('> ' . sprintf($action, ...$parameters) . '...');
        return $this;
    }

    /**
     * Writes a simple message, like a comment, to the console.
     * @param string $message
     * @param mixed ...$parameters
     * @return $this
     */
    public function writeMessage(string $message, ...$parameters): self
    {
        $this->consoleAdapter->writeLine('# ' . sprintf($message, ...$parameters));
        return $this;
    }

    /**
     * Writes an exception to the console.
     * @param Exception $e
     * @return $this
     */
    public function writeException(Exception $e): self
    {
        $this->consoleAdapter->writeLine();
        $this->consoleAdapter->writeLine($this->createHorizontalLine('-'), ColorInterface::LIGHT_RED);
        $this->consoleAdapter->writeLine(
            sprintf(' %s: %s', substr(strrchr(get_class($e), '\\'), 1), $e->getMessage()),
            ColorInterface::LIGHT_RED
        );
        $this->consoleAdapter->writeLine($this->createHorizontalLine('-'), ColorInterface::LIGHT_RED);
        $this->consoleAdapter->writeLine($e->getTraceAsString(), ColorInterface::RED);
        return $this;
    }

    /**
     * Creates a horizontal line of the specified character.
     * @param string $character
     * @return string
     */
    protected function createHorizontalLine(string $character): string
    {
        return str_pad('', $this->consoleAdapter->getWidth(), $character);
    }
}
