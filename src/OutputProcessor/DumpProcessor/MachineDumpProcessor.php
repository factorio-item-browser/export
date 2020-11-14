<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineDumpProcessor implements DumpProcessorInterface
{
    private SerializerInterface $exportSerializer;

    public function __construct(SerializerInterface $exportSerializer)
    {
        $this->exportSerializer = $exportSerializer;
    }

    public function getType(): string
    {
        return 'machine';
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        $dump->machines[] = $this->exportSerializer->deserialize($serializedDump, Machine::class, 'json');
    }
}
