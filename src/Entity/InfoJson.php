<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use JMS\Serializer\Annotation\Type;

/**
 * The entity representing the info.json file of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InfoJson
{
    public string $name = '';
    public string $title = '';
    public string $description = '';

    #[Type('constructor<' . Version::class . '>')]
    public Version $version;

    #[Type('constructor<' . Version::class . '>')]
    public Version $factorioVersion;

    public string $author = '';
    public string $contact = '';
    public string $homepage = '';

    /** @var array<Dependency> */
    #[Type('array<constructor<' . Dependency::class . '>>')]
    public array $dependencies = [];

    public function __construct()
    {
        $this->version = new Version();
        $this->factorioVersion = new Version();
    }
}
