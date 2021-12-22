<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\Export\Constant\ServiceName;
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
    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer
    ) {
    }

    public function getType(): string
    {
        return 'machine';
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        $dump->machines[] = $this->serializer->deserialize($serializedDump, Machine::class, 'json');
    }
}
