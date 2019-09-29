<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use Exception;
use FactorioItemBrowser\Export\Entity\Dump\ControlStage;
use FactorioItemBrowser\Export\Entity\Dump\DataStage;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\InvalidDumpException;
use JMS\Serializer\SerializerInterface;

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
    protected const PLACEHOLDER_BEGIN = '>>>%s>>>';

    /**
     * The placeholder marking the end of a dump.
     */
    protected const PLACEHOLDER_END = '<<<%s<<<';

    /**
     * The regular expression used to detect the checksums for the mod loading order.
     */
    protected const REGEX_CHECKSUM = '#^\s+[0-9.]+ Checksum of (.*): \d+$#m';

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * DumpExtractor constructor.
     * @param SerializerInterface $exportSerializer
     */
    public function __construct(SerializerInterface $exportSerializer)
    {
        $this->serializer = $exportSerializer;
    }
    /**
     * Extracts all dumps from the specified output.
     * @param string $output
     * @return Dump
     * @throws ExportException
     */
    public function extract(string $output): Dump
    {
        $dataStageData = $this->extractRawDumpData($output, 'data');
        $controlStageData = $this->extractRawDumpData($output, 'control');

        $result = new Dump();
        $result->setModNames($this->detectModOrder($output))
               ->setDataStage($this->parseDump('data', $dataStageData, DataStage::class))
               ->setControlStage($this->parseDump('control', $controlStageData, ControlStage::class));
        return $result;
    }

    /**
     * Extracts the raw dump data from the output using predefined placeholders.
     * @param string $output
     * @param string $stage
     * @return string
     * @throws ExportException
     */
    protected function extractRawDumpData(string $output, string $stage): string
    {
        $placeholderStart = sprintf(self::PLACEHOLDER_BEGIN, strtoupper($stage));
        $placeholderEnd = sprintf(self::PLACEHOLDER_END, strtoupper($stage));

        $startPosition = strpos($output, $placeholderStart);
        $endPosition = strpos($output, $placeholderEnd);

        if ($startPosition === false || $endPosition === false || $endPosition < $startPosition) {
            throw new InvalidDumpException($stage, 'Unable to locate data in log file.');
        }

        $startPosition += strlen($placeholderStart);
        return substr($output, $startPosition, $endPosition - $startPosition);
    }

    /**
     * Parses the dump data into an object.
     * @param string $stage
     * @param string $dumpData
     * @param string $className
     * @return mixed
     * @throws InvalidDumpException
     */
    protected function parseDump(string $stage, string $dumpData, string $className)
    {
        try {
            $result = $this->serializer->deserialize($dumpData, $className, 'json');
        } catch (Exception $e) {
            throw new InvalidDumpException($stage, $e->getMessage(), $e);
        }
        return $result;
    }

    /**
     * Detects the mod order from the output.
     * @param string $output
     * @return array|string[]
     * @throws ExportException
     */
    protected function detectModOrder(string $output): array
    {
        $success = preg_match_all(self::REGEX_CHECKSUM, $output, $matches);
        if ($success === false || $success === 0) {
            throw new InternalException('Unable to detect mod order.');
        }

        return $matches[1];
    }
}
