<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The item written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Item
{
    public string $name = '';
    /** @var mixed */
    public $localisedName;
    /** @var mixed */
    public $localisedDescription;
    /** @var mixed */
    public $localisedEntityName;
    /** @var mixed */
    public $localisedEntityDescription;
}
