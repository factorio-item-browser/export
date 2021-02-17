<?php

/**
 * The configuration of the mapper manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use BluePsyduck\MapperManager\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::MAPPERS => [
            Mapper\FluidMapper::class,
            Mapper\IconLayerMapper::class,
            Mapper\IconMapper::class,
            Mapper\ItemMapper::class,
            Mapper\MachineMapper::class,
            Mapper\RecipeIngredientMapper::class,
            Mapper\RecipeMapper::class,
            Mapper\RecipeProductMapper::class,
        ],
    ],
];
