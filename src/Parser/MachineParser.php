<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The parser of the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineParser implements ParserInterface
{
    protected Console $console;
    protected IconParser $iconParser;
    protected MapperManagerInterface $mapperManager;
    protected TranslationParser $translationParser;

    public function __construct(
        Console $console,
        IconParser $iconParser,
        MapperManagerInterface $mapperManager,
        TranslationParser $translationParser
    ) {
        $this->console = $console;
        $this->iconParser = $iconParser;
        $this->mapperManager = $mapperManager;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
        foreach ($this->console->iterateWithProgressbar('Parsing machines', $dump->machines) as $dumpMachine) {
            $exportData->getMachines()->add($this->createMachine($dumpMachine));
        }
    }

    protected function createMachine(DumpMachine $dumpMachine): ExportMachine
    {
        $exportMachine = $this->mapperManager->map($dumpMachine, new ExportMachine());
        $exportMachine->iconId = $this->iconParser->getIconId(EntityType::MACHINE, $dumpMachine->name);

        $this->translationParser->translate($exportMachine->labels, $dumpMachine->localisedName);
        $this->translationParser->translate($exportMachine->descriptions, $dumpMachine->localisedDescription);

        return $exportMachine;
    }

    public function validate(ExportData $exportData): void
    {
    }
}
