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
 *
 * @extends AbstractSerializerDumpProcessor<Machine>
 */
class MachineDumpProcessor extends AbstractSerializerDumpProcessor
{
    public function getType(): string
    {
        return 'machine';
    }

    protected function getEntityClass(): string
    {
        return Machine::class;
    }

    protected function addEntityToDump(object $entity, Dump $dump): void
    {
        $dump->machines[] = $entity;
    }
}
