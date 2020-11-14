<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconDumpProcessor implements DumpProcessorInterface
{
    private SerializerInterface $exportSerializer;

    public function __construct(SerializerInterface $exportSerializer)
    {
        $this->exportSerializer = $exportSerializer;
    }

    public function getType(): string
    {
        return 'icon';
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        $dump->icons[] = $this->exportSerializer->deserialize($serializedDump, Icon::class, 'json');
    }
}
