<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;

/**
 * The class reading the dependencies of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DependencyReader
{
    /**
     * The regular expression used for the dependencies.
     */
    protected const REGEXP_DEPENDENCY = '#^(\?)?\s*([a-zA-Z0-9\-_ ]+)\s*([<=>]*)\s*([0-9.]*)$#';

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the reader.
     * @param ModFileManager $modFileManager
     */
    public function __construct(ModFileManager $modFileManager)
    {
        $this->modFileManager = $modFileManager;
    }

    /**
     * Reads the dependencies of the specified mod.
     * @param Mod $mod
     * @return array|Dependency[]
     * @throws ExportException
     */
    public function read(Mod $mod): array
    {
        $infoJson = $this->modFileManager->getInfoJson($mod);

        $result = [];
        if ($mod->getName() !== 'base') {
            $result['base'] = $this->createDependency('base', '', true);
        }
        foreach ($infoJson->getArray('dependencies') as $dependencyString) {
            $dependency = $this->parseDependency($dependencyString);
            if ($dependency instanceof Dependency) {
                $result[$dependency->getRequiredModName()] = $dependency;
            }
        }
        return $result;
    }

    /**
     * Parses the specified dependency string.
     * @param string $dependencyString
     * @return Dependency|null
     * @throws ExportException
     */
    protected function parseDependency(string $dependencyString): ?Dependency
    {
        $result = null;
        if (preg_match(self::REGEXP_DEPENDENCY, trim($dependencyString), $match) === 0) {
            throw new ExportException(
                'Unable to parse dependency: ' . $dependencyString
            );
        }

        $dependency = null;
        if ($match[3] !== '<') {
            $dependency = $this->createDependency(trim($match[2]), $match[4], $match[1] !== '?');
        }
        return $dependency;
    }

    /**
     * Creates a dependency entity.
     * @param string $modName
     * @param string $version
     * @param bool $isMandatory
     * @return Dependency
     */
    protected function createDependency(string $modName, string $version, bool $isMandatory): Dependency
    {
        $result = new Dependency();
        $result->setRequiredModName($modName)
               ->setRequiredVersion(VersionUtils::normalize($version))
               ->setIsMandatory($isMandatory);
        return $result;
    }
}
