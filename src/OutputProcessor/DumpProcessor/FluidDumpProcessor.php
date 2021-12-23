<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid;

/**
 * The dump processor for the fluids.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @extends AbstractSerializerDumpProcessor<Fluid>
 */
class FluidDumpProcessor extends AbstractSerializerDumpProcessor
{
    public function getType(): string
    {
        return 'fluid';
    }

    protected function getEntityClass(): string
    {
        return Fluid::class;
    }

    protected function addEntityToDump(object $entity, Dump $dump): void
    {
        $dump->fluids[] = $entity;
    }
}
