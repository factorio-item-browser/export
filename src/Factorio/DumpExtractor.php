<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Exception\DumpException;

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
    protected const PLACEHOLDER_BEGIN = '%name%>>>---';

    /**
     * The placeholder marking the end of a dump.
     */
    protected const PLACEHOLDER_END = '---<<<%name%';

    /**
     * Extracts all dumps from the specified output.
     * @param string $output
     * @return DataContainer
     * @throws DumpException
     */
    public function extract(string $output): DataContainer
    {
        return new DataContainer([
            'items' => $this->extractDumpData($output, 'ITEMS'),
            'fluids' => $this->extractDumpData($output, 'FLUIDS'),
            'recipes' => [
                'normal' => $this->extractDumpData($output, 'RECIPES_NORMAL'),
                'expensive' => $this->extractDumpData($output, 'RECIPES_EXPENSIVE')
            ],
            'machines' => $this->extractDumpData($output, 'MACHINES'),
            'icons' => $this->extractDumpData($output, 'ICONS'),
            'fluidBoxes' => $this->extractDumpData($output, 'FLUID_BOXES')
        ]);
    }

    /**
     * Extracts the actual dump from the specified output.
     * @param string $output
     * @param string $name
     * @return array
     * @throws DumpException
     */
    protected function extractDumpData(string $output, string $name): array
    {
        $dump = $this->extractRawDump($output, $name);
        return $this->parseDump($name, $dump);
    }

    /**
     * Extracts the raw dump string from the output.
     * @param string $output
     * @param string $name
     * @return string
     * @throws DumpException
     */
    protected function extractRawDump(string $output, string $name): string
    {
        $startPosition = $this->getStartPosition($output, $name);
        $endPosition = $this->getEndPosition($output, $name);
        if ($startPosition === null || $endPosition === null || $endPosition < $startPosition) {
            throw new DumpException($name, 'Cannot locate placeholders.', $output);
        }
        return substr($output, $startPosition, $endPosition - $startPosition);
    }

    /**
     * Returns the start position of the dump with the specified name.
     * @param string $output
     * @param string $name
     * @return int|null
     */
    protected function getStartPosition(string $output, string $name): ?int
    {
        $placeholder = str_replace('%name%', $name, self::PLACEHOLDER_BEGIN);
        $position = strpos($output, $placeholder);
        return ($position === false) ? null : ($position + strlen($placeholder));
    }

    /**
     * Returns the end position of the dump with the specified name.
     * @param string $output
     * @param string $name
     * @return int|null
     */
    protected function getEndPosition(string $output, string $name): ?int
    {
        $placeholder = str_replace('%name%', $name, self::PLACEHOLDER_END);
        $position = strpos($output, $placeholder);
        return ($position === false) ? null : $position;
    }

    /**
     * Parses the dump to an array of data.
     * @param string $name
     * @param string $dump
     * @return array
     * @throws DumpException
     */
    protected function parseDump(string $name, string $dump): array
    {
        $result = @json_decode($dump, true);
        if (!is_array($result)) {
            throw new DumpException($name, 'Invalid JSON string.');
        }
        return $result;
    }
}
