<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\I18n;

/**
 * The class able to read a locale file from the mods to an array.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleFileReader
{
    /**
     * The regular expression used to detect a section.
     */
    private const REGEXP_SECTION = '#^\[(.*)\]$#';

    /**
     * The regular expression used to detect actual translations.
     */
    private const REGEXP_LOCALE = '#^(.*)=(.*)$#';

    /**
     * The currently active section.
     * @var string
     */
    protected $currentSection;

    /**
     * The read translations.
     * @var array|string[]
     */
    protected $translations;

    /**
     * Reads the specified file as locale file.
     * @param string $fileName
     * @return array
     */
    public function read(string $fileName): array
    {
        $this->currentSection = '';
        $this->translations = [];

        $handle = fopen($fileName, 'r');
        while (($line = fgets($handle)) !== false) {
            $this->readLine(trim($line));
        }
        fclose($handle);
        return $this->translations;
    }

    /**
     * Reads the specified line of the locale file.
     * @param string $line
     * @return $this
     */
    protected function readLine(string $line)
    {
        if (!empty($line) && substr($line, 0, 1) !== ';') {
            if (preg_match(self::REGEXP_LOCALE, $line, $match) > 0) {
                $key = trim($match[1]);
                $value = str_replace('\n', PHP_EOL, trim($match[2]));
                if (strlen($this->currentSection) === 0) {
                    $this->translations[$key] = $value;
                } else {
                    $this->translations[$this->currentSection . '.' . $key] = $value;
                }
            } elseif (preg_match(self::REGEXP_SECTION, $line, $match) > 0) {
                $this->currentSection = trim($match[1]);
            }
        }
        return $this;
    }
}