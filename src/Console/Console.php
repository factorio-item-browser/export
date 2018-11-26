<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Console;

use FactorioItemBrowser\Export\Utils\VersionUtils;
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
     * Writes a banner with the specified message.
     * @param string $message
     * @param int|null $color
     * @return $this
     */
    public function writeBanner(string $message, ?int $color = null)
    {
        $this->writeHorizontalLine('-', $color)
             ->writeLine(' ' . $message, $color)
             ->writeHorizontalLine('-', $color);
        return $this;
    }

    /**
     * Writes a horizontal line to the console.
     * @param string $character
     * @param int|null $color
     * @return $this
     */
    public function writeHorizontalLine(string $character, ?int $color = null)
    {
        $this->writeLine(str_pad('', $this->consoleAdapter->getWidth(), $character), $color);
        return $this;
    }

    /**
     * Formats the mod name for the console.
     * @param string $modName
     * @param string $suffix
     * @return string
     */
    public function formatModName(string $modName, string $suffix = ''): string
    {
        return str_pad($modName, 64, ' ', STR_PAD_LEFT) . $suffix;
    }

    /**
     * Formats the version for the console.
     * @param string $version
     * @param bool $padLeft
     * @return string
     */
    public function formatVersion(string $version, bool $padLeft = false): string
    {
        $version = $version === '' ? '' : VersionUtils::normalize($version);
        return str_pad($version, 10, ' ', $padLeft ? STR_PAD_LEFT : STR_PAD_RIGHT);
    }
}
