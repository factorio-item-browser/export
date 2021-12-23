<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the normal recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @extends AbstractSerializerDumpProcessor<Recipe>
 */
class NormalRecipeDumpProcessor extends AbstractSerializerDumpProcessor
{
    public function getType(): string
    {
        return 'normal-recipe';
    }

    protected function getEntityClass(): string
    {
        return Recipe::class;
    }

    protected function addEntityToDump(object $entity, Dump $dump): void
    {
        $dump->normalRecipes[] = $entity;
    }
}
