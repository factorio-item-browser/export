<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
     * The dump extractor.
     * @var DumpExtractor
     */
    protected $dumpExtractor;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The directory containing the actual Factorio game.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The directory of the instance.
     * @var string|null
     */
    protected $instanceDirectory;

    /**
     * Initializes the instance.
     * @param DumpExtractor $dumpExtractor
     * @param ModRegistry $modRegistry
     * @param string $factorioDirectory
     */
    public function __construct(DumpExtractor $dumpExtractor, ModRegistry $modRegistry, string $factorioDirectory)
    {
        $this->dumpExtractor = $dumpExtractor;
        $this->modRegistry = $modRegistry;
        $this->factorioDirectory = $factorioDirectory;
    }

    /**
     * Runs the combination in a Factorio instance.
     * @param Combination $combination
     * @return DataContainer
     * @throws ExportException
     */
    public function run(Combination $combination): DataContainer
    {
        try {
            $this->setUp($combination->calculateHash());
            $this->setUpMods($combination);
            $output = $this->execute();
        } finally {
            $this->removeInstanceDirectory();
        }
        return $this->dumpExtractor->extract($output);
    }

    /**
     * Sets up the instance.
     * @param string $combinationHash
     * @throws ExportException
     */
    protected function setUp(string $combinationHash): void
    {
        $this->instanceDirectory = $this->factorioDirectory . '/instances/' . $combinationHash;
        $this->removeInstanceDirectory();

        $this->createDirectory('bin/x64');
        $this->createDirectory('mods');

        $this->copy('bin/x64/factorio');
        $this->copy('config-path.cfg');

        $this->createSymlink('data');
    }

    /**
     * Sets up the mods to use for the combination.
     * @param Combination $combination
     * @throws ExportException
     */
    protected function setUpMods(Combination $combination): void
    {
        foreach ($combination->getLoadedModNames() as $modName) {
            $mod = $this->modRegistry->get($modName);
            if (!$mod instanceof Mod) {
                throw new ExportException('Mod not known: ' . $modName);
            }

            $this->createSymlink('mods/' . $mod->getFileName());
        }
        $this->createSymlink('mods/Dump_1.0.0');
    }

    /**
     * Executes the Factorio instance.
     * @return string
     */
    protected function execute(): string
    {
        $process = $this->createProcess();
        $process->run();
        return $process->getOutput();
    }

    protected function createProcess(): Process
    {
        $command = [
            $this->getInstancePath('bin/x64/factorio'),
            '--no-log-rotation',
            '--create=dump',
            '--mod-directory=' . $this->getInstancePath('mods')
        ];

        return new Process($command);
    }

    /**
     * Removes the specified directory if it exists.
     */
    protected function removeInstanceDirectory(): void
    {
        if ($this->instanceDirectory !== null && is_dir($this->instanceDirectory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->instanceDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                /* @var \SplFileInfo $file */
                if ($file->isDir() && !$file->isLink()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($this->instanceDirectory);
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
     * Creates a symlink to the specified directory.
     * @param string $directoryOrFile
     * @codeCoverageIgnore Unable to test symlink with vfsStream.
     */
    protected function createSymlink(string $directoryOrFile): void
    {
        symlink($this->getFactorioPath($directoryOrFile), $this->getInstancePath($directoryOrFile));
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
        return $this->instanceDirectory . '/' . $directoryOrFile;
    }
}
