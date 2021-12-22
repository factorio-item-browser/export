<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

use JMS\Serializer\Annotation\Type;

/**
 * The item written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Item
{
    public string $name = '';

    #[Type('raw')]
    public mixed $localisedName;

    #[Type('raw')]
    public mixed $localisedDescription;

    #[Type('raw')]
    public mixed $localisedEntityName;

    #[Type('raw')]
    public mixed $localisedEntityDescription;
}
