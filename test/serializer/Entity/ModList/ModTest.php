<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\ModList;

use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of the Mod class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Entity\ModList\Mod
 */
class ModTest extends SerializerTestCase
{
    protected function getObject(): object
    {
        $object = new Mod();
        $object->name = 'abc';
        $object->isEnabled = true;
        return $object;
    }

    protected function getData(): array
    {
        return [
            'name' => 'abc',
            'enabled' => true,
        ];
    }
}
