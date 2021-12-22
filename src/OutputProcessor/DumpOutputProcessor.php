<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\DumpProcessorInterface;

/**
 * The class processing the dumps from the output.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpOutputProcessor implements OutputProcessorInterface
{
    private const REGEX_DUMP = '#^>DUMP>(.*)>(.*)<$#';

    /** @var array<string,DumpProcessorInterface> */
    private array $dumpProcessors = [];

    /**
     * @param array<DumpProcessorInterface> $dumpProcessors
     */
    public function __construct(
        #[InjectAliasArray(ConfigKey::MAIN, ConfigKey::OUTPUT_DUMP_PROCESSORS)]
        array $dumpProcessors,
    ) {
        foreach ($dumpProcessors as $dumpProcessor) {
            $this->dumpProcessors[$dumpProcessor->getType()] = $dumpProcessor;
        }
    }

    public function processLine(string $outputLine, Dump $dump): void
    {
        if (preg_match(self::REGEX_DUMP, $outputLine, $match) > 0) {
            $type = $match[1];
            $serializedString = $match[2];

            if (isset($this->dumpProcessors[$type])) {
                $this->dumpProcessors[$type]->process($serializedString, $dump);
            }
        }
    }

    public function processExitCode(int $exitCode, Dump $dump): void
    {
    }
}
