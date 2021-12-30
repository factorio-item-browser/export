<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity;

use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of the ModListJson class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Entity\ModListJson
 */
class ModListJsonTest extends SerializerTestCase
{
    protected function getObject(): object
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod1->isEnabled = true;
        $mod2 = new Mod();
        $mod2->name = 'def';
        $mod2->isEnabled = false;

        $object = new ModListJson();
        $object->mods = [$mod1, $mod2];
        return $object;
    }

    protected function getData(): array
    {
        return [
            'mods' => [
                [
                    'name' => 'abc',
                    'enabled' => true,
                ],
                [
                    'name' => 'def',
                    'enabled' => false,
                ],
            ],
        ];
    }
}
