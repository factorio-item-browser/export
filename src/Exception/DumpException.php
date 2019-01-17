<?php

namespace FactorioItemBrowser\Export\Exception;

use Exception;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpException extends ExportException
{
    /**
     * The number of output lines to print in the exception message.
     */
    protected const NUMBER_OF_OUTPUT_LINES = 5;

    /**
     * Initializes the exception.
     * @param string $name
     * @param string $message
     * @param string $output
     * @param Exception|null $previous
     */
    public function __construct(string $name, string $message, string $output = '', Exception $previous = null)
    {
        parent::__construct($this->buildMessage($name, $message, $output), 0, $previous);
    }

    /**
     * Builds the full message of the exception.
     * @param string $name
     * @param string $message
     * @param string $output
     * @return string
     */
    protected function buildMessage(string $name, string $message, string $output): string
    {
        $result = 'Failed to extract dump ' . $name . ': ' . $message;
        if ($output !== '') {
            $result .= PHP_EOL . 'Last lines of output: ' . PHP_EOL
                . implode(PHP_EOL, $this->extractLastOutputLines($output));
        }
        return $result;
    }

    /**
     * Extracts the last lines of the specified output.
     * @param string $output
     * @return array
     */
    protected function extractLastOutputLines(string $output): array
    {
        $lines = explode(PHP_EOL, $output);
        return array_slice($lines, -self::NUMBER_OF_OUTPUT_LINES);
    }
}
