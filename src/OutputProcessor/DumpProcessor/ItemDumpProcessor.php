<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Item;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemDumpProcessor implements DumpProcessorInterface
{
    private SerializerInterface $exportSerializer;

    public function __construct(SerializerInterface $exportSerializer)
    {
        $this->exportSerializer = $exportSerializer;
    }

    public function getType(): string
    {
        return 'item';
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        $dump->items[] = $this->exportSerializer->deserialize($serializedDump, Item::class, 'json');
    }
}
