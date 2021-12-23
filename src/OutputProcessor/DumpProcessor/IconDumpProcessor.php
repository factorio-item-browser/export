<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @extends AbstractSerializerDumpProcessor<Icon>
 */
class IconDumpProcessor extends AbstractSerializerDumpProcessor
{
    public function getType(): string
    {
        return 'icon';
    }

    protected function getEntityClass(): string
    {
        return Icon::class;
    }

    protected function addEntityToDump(object $entity, Dump $dump): void
    {
        $dump->icons[] = $entity;
    }
}
