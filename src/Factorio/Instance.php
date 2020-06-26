<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FactorioExecutionException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Process\Process;

/**
 * The instance of Factorio being run to get the dump data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Instance
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The dump extractor.
     * @var DumpExtractor
     */
    protected $dumpExtractor;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The directory containing the actual Factorio game.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The directory of the instances.
     * @var string
     */
    protected $instancesDirectory;

    /**
     * The version of the export project.
     * @var string
     */
    protected $version;

    /**
     * The directory for the combination instance.
     * @var string
     */
    protected $combinationInstanceDirectory = '';

    /**
     * Initializes the instance.
     * @param Console $console
     * @param DumpExtractor $dumpExtractor
     * @param ModFileManager $modFileManager
     * @param SerializerInterface $exportSerializer
     * @param string $factorioDirectory
     * @param string $instancesDirectory
     * @param string $version
     */
    public function __construct(
        Console $console,
        DumpExtractor $dumpExtractor,
        ModFileManager $modFileManager,
        SerializerInterface $exportSerializer,
        string $factorioDirectory,
        string $instancesDirectory,
        string $version
    ) {
        $this->console = $console;
        $this->dumpExtractor = $dumpExtractor;
        $this->modFileManager = $modFileManager;
        $this->serializer = $exportSerializer;
        $this->factorioDirectory = $factorioDirectory;
        $this->instancesDirectory = $instancesDirectory;
        $this->version = $version;
    }

    /**
     * Runs the Factorio instance.
     * @param string $combinationId
     * @param array|string[] $modNames
     * @return Dump
     * @throws ExportException
     */
    public function run(string $combinationId, array $modNames): Dump
    {
        try {
            $this->console->writeAction('Preparing Factorio instance');
            $this->combinationInstanceDirectory = $this->instancesDirectory . '/' . $combinationId;

            $this->setUpInstance();
            $this->setUpMods($modNames);
            $this->setupDumpMod($modNames);

            $this->console->writeAction('Launching Factorio');
            $output = $this->execute();
        } finally {
            $this->removeInstanceDirectory();
        }

        $this->console->writeAction('Extracting dumped data');
        return $this->dumpExtractor->extract($output);
    }

    /**
     * Sets up the instance.
     */
    protected function setUpInstance(): void
    {
        $this->removeInstanceDirectory();

        $this->createDirectory('bin/x64');
        $this->createDirectory('mods');

        $this->copy('bin/x64/factorio');
        $this->copy('config-path.cfg');

        $this->createFactorioSymlink('data');
    }

    /**
     * Sets up the mods to use for the combination.
     * @param array|string[] $modNames
     */
    protected function setUpMods(array $modNames): void
    {
        foreach ($modNames as $modName) {
            if ($modName !== Constant::MOD_NAME_BASE) {
                $this->createModSymlink($modName);
            }
        }
    }

    /**
     * Sets up the dump mod to be used.
     * @param array|string[] $modNames
     * @throws ExportException
     * @codeCoverageIgnore Unable to mock cp -r with virtual file system.
     */
    protected function setupDumpMod(array $modNames): void
    {
        exec(sprintf(
            'cp -r "%s" "%s"',
            __DIR__ . '/../../lua/dump',
            $this->getInstancePath('mods/Dump')
        ));

        file_put_contents(
            $this->getInstancePath('mods/Dump/info.json'),
            $this->serializer->serialize($this->createDumpInfoJson($modNames), 'json')
        );
        file_put_contents(
            $this->getInstancePath('mods/mod-list.json'),
            $this->serializer->serialize($this->createModListJson($modNames), 'json')
        );
        file_put_contents(
            $this->getInstancePath('mods/mod-list-foo.json'),
            $this->serializer->serialize($this->createModListJson($modNames), 'json')
        );
    }

    /**
     * Creates the info.json instance used for the dump mod.
     * @param array|string[] $modNames
     * @return InfoJson
     * @throws ExportException
     */
    protected function createDumpInfoJson(array $modNames): InfoJson
    {
        $baseInfo = $this->modFileManager->getInfo(Constant::MOD_NAME_BASE);

        $info = new InfoJson();
        $info->setName('Dump')
             ->setTitle('Factorio Item Browser - Dump')
             ->setAuthor('factorio-item-browser')
             ->setVersion($this->version)
             ->setFactorioVersion($baseInfo->getVersion())
             ->setDependencies($modNames);

        return $info;
    }

    /**
     * Creates the mod-list.json instance.
     * @param array|string[] $modNames
     * @return ModListJson
     */
    protected function createModListJson(array $modNames): ModListJson
    {
        $modList = new ModListJson();

        // Base mod must always be present, especially if disabled.
        $baseMod = new Mod();
        $baseMod->setName(Constant::MOD_NAME_BASE)
                ->setEnabled(in_array(Constant::MOD_NAME_BASE, $modNames, true));
        $modList->addMod($baseMod);

        // Dump mod must always be enabled.
        $dumpMod = new Mod();
        $dumpMod->setName('Dump')
                ->setEnabled(true);
        $modList->addMod($dumpMod);

        // Add all the other mods as well.
        foreach ($modNames as $modName) {
            if ($modName === Constant::MOD_NAME_BASE) {
                continue;
            }

            $mod = new Mod();
            $mod->setName($modName)
                ->setEnabled(true);
            $modList->addMod($mod);
        }

        return $modList;
    }

    /**
     * Executes the Factorio instance.
     * @return string
     * @throws ExportException
     */
    protected function execute(): string
    {
        $process = $this->createProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new FactorioExecutionException((int) $process->getExitCode(), $process->getOutput());
        }

        return $process->getOutput();
    }

    /**
     * Creates the process which will actually run Factorio.
     * @return Process<string>
     */
    protected function createProcess(): Process
    {
        $command = [
            $this->getInstancePath('bin/x64/factorio'),
            '--no-log-rotation',
            '--create=' . $this->getInstancePath('dump'),
            '--mod-directory=' . $this->getInstancePath('mods')
        ];

        $process = new Process($command);
        $process->setTimeout(null);
        return $process;
    }

    /**
     * Removes the specified directory if it exists.
     * @codeCoverageIgnore Unable to rm -rf in virtual file system.
     */
    protected function removeInstanceDirectory(): void
    {
        if (is_dir($this->combinationInstanceDirectory)) {
            exec(sprintf('rm -rf "%s"', $this->combinationInstanceDirectory));
        }
    }

    /**
     * Creates the specified directory.
     * @param string $directory
     */
    protected function createDirectory(string $directory): void
    {
        mkdir($this->getInstancePath($directory), 0777, true);
    }

    /**
     * Copies a file or directory to the instance.
     * @param string $directoryOrFile
     */
    protected function copy(string $directoryOrFile): void
    {
        $destination = $this->getInstancePath($directoryOrFile);

        copy($this->getFactorioPath($directoryOrFile), $destination);
        chmod($destination, 0755);
    }

    /**
     * Creates a symlink to the specified directory or file of the Factorio game.
     * @param string $directoryOrFile
     * @codeCoverageIgnore Unable to test symlink with vfsStream.
     */
    protected function createFactorioSymlink(string $directoryOrFile): void
    {
        symlink((string) realpath($this->getFactorioPath($directoryOrFile)), $this->getInstancePath($directoryOrFile));
    }

    /**
     * Creates a symlink to the specified mod name.
     * @param string $modName
     * @codeCoverageIgnore Unable to test symlink with vfsStream.
     */
    protected function createModSymlink(string $modName): void
    {
        $source = $this->modFileManager->getLocalDirectory($modName);
        $destination = $this->getInstancePath(sprintf('mods/%s', $modName));
        symlink((string) realpath($source), $destination);
    }

    /**
     * Returns the specified directory or file in context of the Factorio game.
     * @param string $directoryOrFile
     * @return string
     */
    protected function getFactorioPath(string $directoryOrFile): string
    {
        return $this->factorioDirectory . '/' . $directoryOrFile;
    }

    /**
     * Returns the specified directory or file in context of the instance directory.
     * @param string $directoryOrFile
     * @return string
     */
    protected function getInstancePath(string $directoryOrFile): string
    {
        return $this->combinationInstanceDirectory . '/' . $directoryOrFile;
    }
}
