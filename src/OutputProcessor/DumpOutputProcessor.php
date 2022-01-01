<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Constant\ServiceName;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Technology;
use FactorioItemBrowser\ExportData\ExportData;
use JMS\Serializer\SerializerInterface;

/**
 * The class processing the dumps from the output.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpOutputProcessor implements OutputProcessorInterface
{
    private const REGEX_DUMP = '#^>DUMP>(.*)>(.*)<$#';

    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function processLine(string $outputLine, ExportData $exportData): void
    {
        if (preg_match(self::REGEX_DUMP, $outputLine, $match) > 0) {
            $type = $match[1];
            $data = $match[2];

            switch ($type) {
                case 'icon':
                    $exportData->getIcons()->add($this->createObject($data, Icon::class));
                    break;

                case 'item':
                    $exportData->getItems()->add($this->createObject($data, Item::class));
                    break;

                case 'machine':
                    $exportData->getMachines()->add($this->createObject($data, Machine::class));
                    break;

                case 'recipe':
                    $exportData->getRecipes()->add($this->createObject($data, Recipe::class));
                    break;

                case 'technology':
                    $exportData->getTechnologies()->add($this->createObject($data, Technology::class));
                    break;
            }
        }
    }

    /**
     * @template T of object
     * @param string $data
     * @param class-string<T> $class
     * @return T
     */
    protected function createObject(string $data, string $class): object
    {
        return $this->serializer->deserialize($data, $class, 'json'); // @phpstan-ignore-line
    }

    public function processExitCode(int $exitCode, ExportData $exportData): void
    {
    }
}
