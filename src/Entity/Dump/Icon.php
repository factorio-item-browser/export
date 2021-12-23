<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

use JMS\Serializer\Annotation\Type;

/**
 * The icon written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Icon
{
    public string $type = '';
    public string $name = '';
    /** @var array<Layer> */
    #[Type('array<' . Layer::class . '>')]
    public array $layers = [];
}
