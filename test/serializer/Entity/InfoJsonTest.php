<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of the InfoJson class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Entity\InfoJson
 */
class InfoJsonTest extends SerializerTestCase
{
    protected function getObject(): object
    {
        $object = new InfoJson();
        $object->name = 'abc';
        $object->title = 'def';
        $object->description = 'ghi';
        $object->version = new Version('1.2.3');
        $object->factorioVersion = new Version('4.5.6');
        $object->author = 'jkl';
        $object->contact = 'mno';
        $object->homepage = 'pqr';
        $object->dependencies = [
            new Dependency('stu > 7.8.9'),
            new Dependency('vwx'),
        ];
        return $object;
    }

    protected function getData(): array
    {
        return [
            'name' => 'abc',
            'title' => 'def',
            'description' => 'ghi',
            'version' => '1.2.3',
            'factorio_version' => '4.5.6',
            'author' => 'jkl',
            'contact' => 'mno',
            'homepage' => 'pqr',
            'dependencies' => ['stu > 7.8.9', 'vwx'],
        ];
    }
}
