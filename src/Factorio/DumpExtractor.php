<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Exception\ExportException;

/**
 * The class for extracting all dumps from the game output.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpExtractor
{
    /**
     * The placeholder marking the begin of a dump.
     */
    private const PLACEHOLDER_BEGIN = '%name%>>>---';

    /**
     * The placeholder marking the end of a dump.
     */
    private const PLACEHOLDER_END = '---<<<%name%';

    /**
     * Extracts all dumps from the specified output.
     * @param string $output
     * @return DataContainer
     * @throws ExportException
     */
    public function extract(string $output): DataContainer
    {
        return new DataContainer([
            'items' => $this->extractDump($output, 'ITEMS'),
            'fluids' => $this->extractDump($output, 'FLUIDS'),
            'recipes' => [
                'normal' => $this->extractDump($output, 'RECIPES_NORMAL'),
                'expensive' => $this->extractDump($output, 'RECIPES_EXPENSIVE')
            ],
            'machines' => $this->extractDump($output, 'MACHINES'),
            'icons' => $this->extractDump($output, 'ICONS'),
            'fluidBoxes' => $this->extractDump($output, 'FLUID_BOXES')
        ]);
    }

    /**
     * Extracts the actual dump from the specified output.
     * @param string $output
     * @param string $dumpName
     * @return array
     * @throws ExportException
     */
    protected function extractDump(string $output, string $dumpName): array
    {
        $placeHolderBegin = str_replace('%name%', $dumpName, self::PLACEHOLDER_BEGIN);
        $placeHolderEnd = str_replace('%name%', $dumpName, self::PLACEHOLDER_END);

        $posBegin = strpos($output, $placeHolderBegin) + strlen($placeHolderBegin);
        $posEnd = strpos($output, $placeHolderEnd, $posBegin);
        if ($posBegin === false || $posEnd === false) {
            $lines = explode(PHP_EOL, $output);
            $lastLines = implode(PHP_EOL, array_slice($lines, -5));

            throw new ExportException(
                'Unable to locate dump ' . $dumpName . ' in the output. Last lines were: ' . PHP_EOL . $lastLines
            );
        }

        $dump = substr($output, $posBegin, $posEnd - $posBegin);
        $result = json_decode($dump, true);
        if (!is_array($result)) {
            throw new ExportException('The dump ' . $dumpName . ' seems to be not valid JSON.');
        }

        return $result;
    }
}