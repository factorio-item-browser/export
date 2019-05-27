<?php

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;

/**
 * The generator for the info.json file of the dump mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpInfoGenerator
{
    /**
     * The filename of the info.json file of the dump mod.
     */
    protected const DUMP_INFO_JSON = 'Dump_1.0.0/info.json';

    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The directory of the mods.
     * @var string
     */
    protected $modsDirectory;

    /**
     * Initializes the generator.
     * @param ModRegistry $modRegistry
     * @param string $modsDirectory
     */
    public function __construct(ModRegistry $modRegistry, string $modsDirectory)
    {
        $this->modRegistry = $modRegistry;
        $this->modsDirectory = $modsDirectory;
    }

    /**
     * Generates the info.json file of the dump mod.
     * @throws ExportException
     */
    public function generate(): void
    {
        $json = $this->generateInfoJson($this->fetchBaseMod());
        $this->writeInfoJson($json);
    }

    /**
     * Fetches the base mod.
     * @return Mod
     * @throws ExportException
     */
    protected function fetchBaseMod(): Mod
    {
        $result = $this->modRegistry->get(Constant::MOD_NAME_BASE);
        if ($result === null) {
            throw new ExportException('Base mod not known.');
        }
        return $result;
    }

    /**
     * Generates the content of the info.json file.
     * @param Mod $baseMod
     * @return array
     */
    protected function generateInfoJson(Mod $baseMod): array
    {
        return [
            'name' => 'Dump',
            'version' => '1.0.0',
            'factorio_version' => $baseMod->getVersion(),
            'title' => 'BluePsyduck\'s Dump',
            'author' => 'BluePsyduck',
            'dependencies' => $this->createDependencies(),
        ];
    }

    /**
     * Returns the dependencies of the dumper mod.
     * @return array
     */
    protected function createDependencies(): array
    {
        $result = [];
        foreach ($this->modRegistry->getAllNames() as $modName) {
            $result[] = '?' . $modName;
        }
        return $result;
    }

    /**
     * Writes the info.json file with the specified content.
     * @param array $json
     * @throws ExportException
     */
    protected function writeInfoJson(array $json): void
    {
        $fileName = $this->modsDirectory . '/' . self::DUMP_INFO_JSON;
        $result = @file_put_contents($fileName, json_encode($json));
        if ($result === false) {
            throw new ExportException('Unable to write info.json file of the dump mod.');
        }
    }
}
