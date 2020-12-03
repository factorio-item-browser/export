<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The parser of the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconParser implements ParserInterface
{
    protected const BLACKLISTED_TYPES = [
        'technology',
        'tutorial',
    ];

    protected Console $console;
    protected HashCalculator $hashCalculator;
    protected MapperManagerInterface $mapperManager;

    /** @var array<string,array<string,ExportIcon>> */
    protected array $parsedIcons = [];
    /** @var array<string,ExportIcon> */
    protected array $usedIcons = [];

    public function __construct(Console $console, HashCalculator $hashCalculator, MapperManagerInterface $mapperManager)
    {
        $this->console = $console;
        $this->hashCalculator = $hashCalculator;
        $this->mapperManager = $mapperManager;
    }

    public function prepare(Dump $dump): void
    {
        $this->parsedIcons = [];
        $this->usedIcons = [];

        foreach ($this->console->iterateWithProgressbar('Preparing icons', $dump->icons) as $dumpIcon) {
            if ($this->isIconValid($dumpIcon)) {
                $this->addParsedIcon($dumpIcon->type, $dumpIcon->name, $this->createIcon($dumpIcon));
            }
        }
    }

    protected function isIconValid(DumpIcon $dumpIcon): bool
    {
        return !in_array($dumpIcon->type, self::BLACKLISTED_TYPES, true);
    }

    protected function createIcon(DumpIcon $dumpIcon): ExportIcon
    {
        $exportIcon = $this->mapperManager->map($dumpIcon, new ExportIcon());
        $exportIcon->id = $this->hashCalculator->hashIcon($exportIcon);
        return $exportIcon;
    }

    protected function addParsedIcon(string $type, string $name, ExportIcon $icon): void
    {
        switch ($type) {
            case EntityType::FLUID:
            case EntityType::ITEM:
            case EntityType::RECIPE:
                $this->parsedIcons[$type][$name] = $icon;
                break;

            default:
                if (!isset($this->parsedIcons[EntityType::ITEM][$name])) {
                    $this->parsedIcons[EntityType::ITEM][$name] = $icon;
                }
                if (!isset($this->parsedIcons[EntityType::MACHINE][$name])) {
                    $this->parsedIcons[EntityType::MACHINE][$name] = $icon;
                }
                break;
        }
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
    }

    public function validate(ExportData $exportData): void
    {
        foreach ($this->usedIcons as $icon) {
            $exportData->getIcons()->add($icon);
        }
    }

    public function getIconId(string $type, string $name): string
    {
        $result = '';
        $icon = $this->parsedIcons[$type][$name] ?? null;
        if ($icon !== null) {
            $this->usedIcons[$icon->id] = $icon;
            $result = $icon->id;
        }
        return $result;
    }
}
