<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when the execution of Factorio failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioExecutionException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Factorio exited with code %d: %s';

    /**
     * The regular expression to detect the line starting the error.
     */
    protected const REGEXP_ERROR_LINE = '(^[ ]*\d+\.\d{3} Error |^Error: )';

    /**
     * Initializes the exception.
     * @param int $exitCode
     * @param string $output
     * @param Throwable|null $previous
     */
    public function __construct(int $exitCode, string $output, ?Throwable $previous = null)
    {
        $message = $this->extractErrorMessageFromOutput($output);
        parent::__construct(sprintf(self::MESSAGE, $exitCode, $message), $exitCode, $previous);
    }

    /**
     * Extracts the actual error message from the output.
     * @param string $output
     * @return string
     */
    protected function extractErrorMessageFromOutput(string $output): string
    {
        $errorLines = [];
        $errorFound = false;

        $lines = array_reverse(explode(PHP_EOL, $output));
        foreach ($lines as $line) {
            $errorLines[] = $line;
            if (preg_match(self::REGEXP_ERROR_LINE, $line) > 0) {
                $errorFound = true;
                break;
            }
        }

        if (!$errorFound) {
            // We were unable to detect the start of the error. So take the last 10 lines instead.
            $errorLines = [];
            foreach (array_slice($lines, 0, 10) as $line) {
                if (strpos($line, '>>>') !== false) {
                    // We have our dump placeholder, so break now before we add the dump to the message.
                    break;
                }
                $errorLines[] = $line;
            }
        }

        return implode(PHP_EOL, array_reverse($errorLines));
    }
}
